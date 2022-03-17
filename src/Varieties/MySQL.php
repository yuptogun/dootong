<?php
namespace Yuptogun\Dootong\Varieties;

use PDO;
use PDOStatement;
use Yuptogun\Dootong\Dootong;
use Yuptogun\Dootong\Interfaces\Variety as DootongVariety;

class MySQL implements DootongVariety
{
    /**
     * @var Dootong
     */
    private $dootong;

    private $pdo;

    // ---- Variety implementation ---- //

    /**
     * @param PDO $pdo
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param string $cause MySQL query string that eventually SELECT
     */
    public function get(Dootong $dootong, ?array $params = null): array
    {
        $this->setDootong($dootong);

        $cause = $this->getDootong()->getHeadacheGettingCause();
        $query = $this->executeQuery($cause, $params);

        /** @var Headache[] $dootongs */
        $dootongs = $query->fetchAll(PDO::FETCH_CLASS, get_class($this->getDootong()), [$this->getDootong()->getVariety()]);

        return !$this->getDootong()->isSoftDeletable()
            ? $dootongs
            : array_filter($dootongs, function ($dootong) {
                /** @var Headache $dootong */
                return !$dootong->isSoftDeleted();
            });
    }

    /**
     * @param string $cause MySQL query string that eventually UPDATE, INSERT or DELETE
     */
    public function set(Dootong $dootong, array $params): int
    {
        $this->setDootong($dootong);

        $cause = $this->getDootong()->getHeadacheSettingCause();
        $query = $this->executeQuery($cause, $params);

        $causeHasInsert = stripos($cause, 'INSERT INTO ') !== false;
        $hasIncrementingKey = $this->getDootong()->hasIncrementingKey();
        return $causeHasInsert && $hasIncrementingKey ? (int) $this->pdo->lastInsertId() : $query->rowCount();
    }

    public function getDootong(): Dootong
    {
        return clone $this->dootong;
    }

    public function setDootong(Dootong $dootong): void
    {
        $this->dootong = $dootong;
    }

    // ---- Variety implementation ---- //

    private function executeQuery(string $query, ?array $params = null): PDOStatement
    {
        $query = $this->prepareQuery($query, $params);
        $query->execute();
        return $query;
    }

    private function prepareQuery(string $query, ?array $params = null): PDOStatement
    {
        $query = $this->pdo->prepare($query);
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $param = ":$key";
                if (strpos($query, $param) !== FALSE) {
                    $query->bindParam($param, $this->getDootong()->getCastedValue($key, $value), $this->getParamType($key));
                }
            }
        }
        return $query;
    }

    private function getParamType(string $key): int
    {
        switch ($this->getDootong()->getCasting($key)) {
            case 'int': case 'integer': case 'increment':
                return PDO::PARAM_INT;
            case 'string':
            default:
                return PDO::PARAM_STR;
        }
    }
}
