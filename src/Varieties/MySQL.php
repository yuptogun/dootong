<?php
namespace Yuptogun\Dootong\Varieties;

use PDO;
use PDOStatement;
use Yuptogun\Dootong\Dootong;
use Yuptogun\Dootong\Interfaces\Headache;

class MySQL extends Dootong
{
    /**
     * @param PDOStatement $source
     * @return self[]
     */
    public function get($source): array
    {
        return $source->fetchAll(PDO::FETCH_CLASS, static::class);
    }

    /**
     * @param PDOStatement $source
     * @param self $entry
     */
    public function set($source, array $entry): Headache
    {
        foreach ($entry as $param => $value) {
            $source->bindValue($param, $value);
        }
        $source->execute();
        $dootong = clone $this;
        foreach ($entry as $name => $value) {
            $dootong->setAttribute($name, $value);
        }
        return $dootong;
    }
}
