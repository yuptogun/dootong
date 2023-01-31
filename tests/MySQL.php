<?php declare(strict_types=1);

namespace Tests;

use PDO;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

abstract class MySQL extends TestCase
{
    /**
     * @var PDO
     */
    protected static $pdo = null;

    public static function setUpBeforeClass(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__.'/../');
        $dotenv->load();
        $host = $_ENV['TEST_MYSQL_HOST'];
        $port = $_ENV['TEST_MYSQL_PORT'];
        $username = $_ENV['TEST_MYSQL_USERNAME'];
        $database = $_ENV['TEST_MYSQL_DATABASE'];
        $password = $_ENV['TEST_MYSQL_PASSWORD'] ?? null;
        $charset = $_ENV['TEST_MYSQL_CHARSET'] ?? 'utf8';
        $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=$charset";

        $pdo = new PDO($dsn, $username, $password, [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
        ]);
        $pdo->exec(file_get_contents(__DIR__.'/MySQL/test.sql'));
        self::$pdo = $pdo;
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
