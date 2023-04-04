<?php declare(strict_types=1);

namespace Tests;

use PDO;
use PHPUnit\Framework\TestCase;

abstract class MySQL extends TestCase
{
    /**
     * @var PDO
     */
    protected static $pdo = null;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new PDO('mysql:host=db;port=3306;dbname=test;charset=utf8', 'test', 'test', [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
        ]);
    }

    public static function tearDownAfterClass(): void
    {
        self::$pdo = null;
    }

    public function getPDO(): ?PDO
    {
        return self::$pdo;
    }
}
