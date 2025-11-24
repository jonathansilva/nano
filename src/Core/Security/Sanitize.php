<?php

namespace Nano\Core\Security;

class Sanitize
{
    public static function data(array $data): object
    {
        return (object) array_filter(self::recursive($data), function ($key): bool {
            return $key !== 'csrf' && !str_ends_with($key, '_confirmation');
        }, ARRAY_FILTER_USE_KEY);
    }

    private static function recursive(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([self::class, 'recursive'], $value);
        }

        return self::sanitize($value);
    }

    private static function sanitize(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = mb_trim($value);

            if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return strtolower($value);
            }

            $value = stripslashes($value);
            $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        return $value;
    }
}
