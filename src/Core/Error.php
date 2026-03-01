<?php

namespace Nano\Core;

final class Error
{
    private function __construct() {}

    public static function parse(string|object $message): array|object
    {
        return (json_validate($message)) ? json_decode($message) : [$message];
    }

	public static function throwJsonException(int $code, string|object $message): never
	{
        header("HTTP/1.1 {$code}");
        header('Content-Type: application/json');

		echo json_encode(['data' => ['errors' => self::parse($message)]]);

        die;
	}
}
