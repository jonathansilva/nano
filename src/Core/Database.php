<?php

namespace Nano\Core;

use Nano\Core\Env;
use PDO;
use PDOException;
use Exception;

class Database
{
    public static function instance(): PDO
    {
        try {
            $host = Env::fetch('DATABASE_HOST');
            $name = Env::fetch('DATABASE_NAME');
            $user = Env::fetch('DATABASE_USER');
            $pass = Env::fetch('DATABASE_PASS');

            $dsn = "mysql:host={$host};dbname={$name}";

            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            ];

            return new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log('Error connecting to the database: ' . $e->getMessage());

            throw new PDOException('Erro ao conectar no banco de dados');
        }
    }

    private function __construct() {}

    private function __clone(): void {}

    public function __wakeup(): void
    {
        throw new Exception('Unserializing is not allowed');
    }
}
