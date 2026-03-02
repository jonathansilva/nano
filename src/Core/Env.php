<?php

namespace Nano\Core;

final class Env
{
    private static array $env = [];
    private static bool $loaded = false;

    private function __construct() {}

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

        $value = self::$env[$key] ?? null;

        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        return null;
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
