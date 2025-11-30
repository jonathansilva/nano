<?php

namespace Nano\Core;

class Env
{
    private static array $env = [];

    private static bool $loaded = false;

    public static function fetch(string $key): ?string
    {
        $value = getenv($key);

        if ($value !== false) {
            return $value;
        }

        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        if (!self::$loaded) {
            self::loadEnvFile();
        }

        return self::$env[$key] ?? null;
    }

    private static function loadEnvFile(): void
    {
        self::$loaded = true;

        $file = dirname(__DIR__, 5) . '/.env';

        if (file_exists($file)) {
            self::$env = parse_ini_file($file, true, INI_SCANNER_RAW);
        }
    }
}
