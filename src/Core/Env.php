<?php

namespace Nano\Core;

use Exception;

class Env
{
    private static array $env;

    public static function fetch(string $key): ?string
    {
        if (empty(self::$env)) {
            $file = dirname(__DIR__, 5) . '/.env';

            if (!file_exists($file)) {
                throw new Exception("The {$file} file was not found");
            }

            self::$env = parse_ini_file($file, true);
        }

        return self::$env[$key] ?? null;
    }
}
