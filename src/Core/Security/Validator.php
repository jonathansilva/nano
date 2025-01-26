<?php

namespace Nano\Core\Security;

use Exception;

class Validator
{
    private static array $errors = [];

    private static object $data;

    private static string $lang;

    public static function schema(object $data, array $rules, string $lang): false|array
    {
        self::$data = $data;
        self::$lang = $lang;

        self::recursive(self::$data, $rules);

        if (count(self::$errors) === 0) {
            return false;
        }

        /* Separa os erros em grupos ( ["email"] => [...], ["password"] => [...] )
         * e move a mensagem de "tipo desconhecido" ( caso existir ), para o final de cada grupo
         *
         * [
         *     "O campo email é obrigatório",
         *     "O campo email deve ser do tipo e-mail",
         *     "O campo password é obrigatório",
         *     "O campo password deve ter no mínimo 8 caracteres",
         *     "O campo password tem um tipo desconhecido => strings"
         * ]
         */
        return self::orderedArray();
    }

    private static function recursive(object $obj, array $rules): void
    {
        foreach ($rules as $field => $rule) {
            $value = $obj->$field ?? null;

            if (is_array($rule)) {
                if (is_null($value)) {
                    break;
                }

                // Array de objetos
                if (is_array($value)) {
                    foreach ($value as $obj) {
                        self::recursive($obj, $rules[$field]);
                    }

                    continue;
                }

                // Objetos
                self::recursive($value, $rules[$field]);

                continue;
            }

            self::handleValidate($rules[$field], $field, $value);
        }
    }

    private static function handleValidate(string $rules, string $field, mixed $value): void
    {
        foreach (array_unique(explode('|', $rules)) as $rule) {
            if (empty($rule)) {
                throw new Exception("Check the rules for the '{$field}' field");
            }

            if ((is_null($value) && $rule != 'required')) {
                continue;
            }

            if (str_contains($rule, ':')) {
                $_rule = explode(':', $rule);

                $ruleName = $_rule[0];
                $ruleNumber = $_rule[1];

                if (empty($ruleName) || empty($ruleNumber)) {
                    throw new Exception("Check the ':' in the '{$field}' field");
                }

                if (!self::validate($field, $value, $ruleName, $ruleNumber)) {
                    self::addMessage($field, $ruleName, $ruleNumber);
                }

                continue;
            }

            if (!self::validate($field, $value, $rule)) {
                self::addMessage($field, $rule);
            }
        }
    }

    private static function validate(string $field, mixed $value, string $rule, ?string $num = null): bool
    {
        return match ($rule) {
            'required' => $value !== null && $value !== '',
            'string' => is_string($value),
            'integer' => is_integer($value),
            'float' => is_float($value),
            'bool' => is_bool($value),
            'email' => filter_var($value, FILTER_VALIDATE_EMAIL),
            'min' => is_numeric($num) && mb_strlen($value ?? '') >= $num,
            'max' => is_numeric($num) && mb_strlen($value ?? '') <= $num,
            'confirmed' => $value === self::$data->{$field . '_confirmation'},
            default => false
        };
    }

    private static function addMessage(string $field, string $rule, ?string $num = null): void
    {
        $message = [
            'pt-BR' => [
                'required' => "é obrigatório",
                'string' => "deve ser do tipo string",
                'integer' => "deve ser do tipo inteiro",
                'float' => "deve ser do tipo float",
                'bool' => "deve ser do tipo boolean",
                'email' => "deve ser do tipo e-mail",
                'min' => call_user_func(function ($num) {
                    $message = "deve ter no mínimo {$num} caracteres";

                    if (!is_numeric($num)) {
                        $message = "deve ter um valor inteiro na regra 'min'";
                    }

                    return $message;
                }, $num),
                'max' => call_user_func(function ($num) {
                    $message = "deve ter no máximo {$num} caracteres";

                    if (!is_numeric($num)) {
                        $message = "deve ter um valor inteiro na regra 'max'";
                    }

                    return $message;
                }, $num),
                'confirmed' => "deve ser confirmado"
            ][$rule] ?? "tem um tipo desconhecido => {$rule}",

            'en-US' => [
                'required' => "is required",
                'string' => "must be of type string",
                'integer' => "must be of type integer",
                'float' => "must be of type float",
                'bool' => "must be of type boolean",
                'email' => "must be of type e-mail",
                'min' => call_user_func(function ($num) {
                    $message = "must be a minimum of {$num} characters";

                    if (!is_numeric($num)) {
                        $message = "must have an integer value in the 'min' rule";
                    }

                    return $message;
                }, $num),
                'max' => call_user_func(function ($num) {
                    $message = "must be a maximum of {$num} characters";

                    if (!is_numeric($num)) {
                        $message = "must have an integer value in the 'max' rule";
                    }

                    return $message;
                }, $num),
                'confirmed' => "must be confirmed"
            ][$rule] ?? "has an unknown type => {$rule}"
        ][self::$lang];

        self::$errors[] = (self::$lang == 'pt-BR') ? "O campo '{$field}' {$message}" : "The '{$field}' field {$message}";
    }

    private static function orderedArray(): array
    {
        $errorGroups = [];
        $unknownRules = [];

        $unknownType = (self::$lang == 'pt-BR') ? 'desconhecido' : 'unknown';

        foreach (array_unique(self::$errors) as $error) {
            preg_match('/\'([\w]+)\'/', $error, $matches);

            $field = $matches[1];

            if (strpos($error, $unknownType) !== false) {
                $unknownRules[$field][] = str_replace("'", '', $error);

                continue;
            }

            if (!isset($errorGroups[$field])) {
                $errorGroups[$field] = [];
            }

            $errorGroups[$field][] = str_replace("'", '', $error);
        }

        foreach ($unknownRules as $field => $errors) {
            $errorGroups[$field] = (isset($errorGroups[$field])) ? array_merge($errorGroups[$field], $errors) : $errors;
        }

        return array_merge(...array_values($errorGroups));
    }
}
