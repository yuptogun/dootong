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
     * @param string $source query string that eventually SELECT
     */
    public function get($source, ?array $params = null): array
    {
        $query = $this->executeQuery($source, $params);

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
     * @param string $source query string that eventually UPDATE, INSERT or DELETE
     */
    public function set($source, array $entry): int
    {
        $query = $this->executeQuery($source, $entry);

        $isInsert = (bool) preg_match('/INSERT\s+INTO\s+/', $source);
        $hasIncrementingKey = $this->hasIncrementingKey();
        return $isInsert && $hasIncrementingKey ? (int) $this->pdo->lastInsertId() : $query->rowCount();
    }

    protected function executeQuery(string $source, ?array $params = null): PDOStatement
    {
        $query = $this->prepareQuery($source, $params);
        $query->execute();
        return $query;
    }

    protected function prepareQuery(string $source, ?array $params = null): PDOStatement
    {
        $query = $this->pdo->prepare($source);
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $param = ":$key";
                if (strpos($source, $param) !== FALSE) {
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
