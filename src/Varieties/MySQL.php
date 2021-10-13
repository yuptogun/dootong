<?php
namespace Yuptogun\Dootong\Varieties;

use PDO;
use PDOStatement;
use Yuptogun\Dootong\Dootong;

class MySQL extends Dootong
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $cause MySQL query string that eventually SELECT
     */
    public function get($cause, ?array $params = null): array
    {
        $query = $this->executeQuery($cause, $params);

        /** @var self[] $dootongs */
        $dootongs = $query->fetchAll(PDO::FETCH_CLASS, static::class, [$this->pdo]);

        return !$this->isSoftDeletable()
            ? $dootongs
            : array_filter($dootongs, function ($dootong) {
                /** @var self $dootong */
                return !$dootong->isSoftDeleted();
            });
    }

    /**
     * @param string $cause MySQL query string that eventually UPDATE, INSERT or DELETE
     */
    public function set($cause, array $params): int
    {
        $query = $this->executeQuery($cause, $params);

        $isInsert = (bool) preg_match('/INSERT\s+INTO\s+/i', $cause);
        $hasIncrementingKey = $this->hasIncrementingKey();
        return $isInsert && $hasIncrementingKey ? (int) $this->pdo->lastInsertId() : $query->rowCount();
    }

    protected function executeQuery(string $query, ?array $params = null): PDOStatement
    {
        $query = $this->prepareQuery($query, $params);
        $query->execute();
        return $query;
    }

    protected function prepareQuery(string $query, ?array $params = null): PDOStatement
    {
        $query = $this->pdo->prepare($query);
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $param = ":$key";
                if (strpos($query, $param) !== FALSE) {
                    $query->bindParam($param, $this->getCastedValue($value, $this->getCasting($key)), $this->getParamType($key));
                }
            }
        }
        return $query;
    }

    protected function getParamType(string $name): int
    {
        switch ($this->getCasting($name)) {
            case 'int': case 'integer': case 'increment':
                return PDO::PARAM_INT;
            case 'string':
            default:
                return PDO::PARAM_STR;
        }
    }
}
