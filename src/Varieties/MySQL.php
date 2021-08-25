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
     * @return array
     */
    public static function get($source): array
    {
        return $source->fetchAll(PDO::FETCH_CLASS, self::class);
    }

    /**
     * @param PDOStatement $source
     * @param array $entry
     */
    public static function set($source, array $entry): Headache
    {
        fore
        $source->bindValue()
    }
}
