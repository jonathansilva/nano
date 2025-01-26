<?php

namespace Nano\Core;

use Nano\Core\Env;
use PDO;
use PDOException;

class Database
{
    private static $conn = null;

    public static function instance(): ?PDO
    {
        try {
            if (self::$conn === null) {
                $dsn = 'mysql:host=' . Env::fetch('DB_HOST') . ';dbname=' . Env::fetch('DB_NAME');

                $options = [
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
                ];

                self::$conn = new PDO($dsn, Env::fetch('DB_USER'), Env::fetch('DB_PASS'), $options);
            }
        } catch (PDOException) {
            throw new PDOException('Error connecting to the database');
        }

        return self::$conn;
    }

    private function __construct() {}

    private function __clone(): void {}

    public function __wakeup(): void
    {
        static::instance();
    }
}
