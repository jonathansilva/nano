<?php

namespace Nano\Core;

use Nano\Core\Env;
use PDO;
use PDOException;
use Exception;

final class Database
{
    private function __construct() {}

    private function __clone(): void {}

    public function __wakeup(): void
    {
        throw new Exception('Unserializing is not allowed');
    }

    public static function instance(): PDO
    {
        try {
            $type = Env::fetch('DATABASE_TYPE') ?? 'mysql';

            if (!in_array($type, ['mysql', 'pgsql', 'sqlite'])) {
                throw new Exception("Banco de dados '{$type}' não suportado");
            }

            $host = Env::fetch('DATABASE_HOST');
            $port = Env::fetch('DATABASE_PORT') ?? (match ($type) {
                'pgsql' => '5432',
                default => '3306'
            });
            $name = Env::fetch('DATABASE_NAME');
            $user = Env::fetch('DATABASE_USER');
            $pass = Env::fetch('DATABASE_PASS');

            $extension = "pdo_{$type}";

            if (!extension_loaded($extension)) {
                throw new PDOException("A extensão '{$extension}' não está habilitada no php.ini");
            }

            $dsn = match ($type) {
                'pgsql' => "pgsql:host={$host};port={$port};dbname={$name};options='--client_encoding=UTF8'",
                'sqlite' => "sqlite:{$name}",
                default => "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4"
            };

            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            return new PDO($dsn, $user ?? null, $pass ?? null, $options);
        } catch (PDOException | Exception $e) {
            error_log($e->getMessage());

            throw new PDOException('Erro ao conectar no banco de dados');
        }
    }
}
