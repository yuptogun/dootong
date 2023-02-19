<?php declare(strict_types=1);

namespace Yuptogun\Dootong\Headaches;

use PDO;
use Generator;
use PDOStatement;

use Yuptogun\Dootong\Dootong;
use Yuptogun\Dootong\Headache;

class MySQL extends Headache
{
    /**
     * @var Dootong
     */
    private $dto;

    private $pdo;

    // ---- Variety implementation ---- //

    /**
     * @param PDO $pdo
     */
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function get(Dootong $dootong, ?array $cause = null): Generator
    {
        $this->setDTO($dootong);

        $query = $this->executeQuery($this->getGetter(), $cause);
        $query->setFetchMode(PDO::FETCH_CLASS, get_class($dootong));

        while ($dto = $query->fetch(PDO::FETCH_CLASS)) {
            /** @var Dootong $dto */
            if (!$dto->isSoftDeleted()) {
                yield $dto;
            }
        }
    }

    /**
     * @return integer If DTO is incrementing, Last Inserted ID. Otherwise, queried rows count.
     */
    public function set(Dootong $dootong, array $cause): int
    {
        $this->setDTO($dootong);

        $query = $this->executeQuery($this->getSetter(), $cause);

        $causeHasInsert = stripos($this->getSetter(), 'INSERT INTO ') !== false;
        $hasIncrementingKey = $dootong->hasIncrementingKey();
        return $causeHasInsert && $hasIncrementingKey
            ? (int) $this->pdo->lastInsertId()
            : $query->rowCount();
    }

    public function getDTO(): Dootong
    {
        return $this->dto;
    }

    public function setDTO(Dootong $dootong): void
    {
        $this->dto = $dootong;
    }

    // ---- Variety implementation ---- //

    private function executeQuery(string $sql, ?array $params = null): PDOStatement
    {
        $query = $this->prepareQuery($sql, $params);
        $query->execute();
        return $query;
    }

    private function prepareQuery(string $sql, ?array $params = null): PDOStatement
    {
        $query = $this->pdo->prepare($sql);
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                $var = $this->getDTO()->getCastedValue($key, $value);
                $query->bindParam(":$key", $var, $this->getParamType($key));
                unset($var);
            }
        }
        return $query;
    }

    private function getParamType(string $key): int
    {
        switch ($this->getDTO()->getCasting($key)) {
            case 'int': case 'integer': case 'increment':
                return PDO::PARAM_INT;
            case 'string':
            default:
                return PDO::PARAM_STR;
        }
    }
}
