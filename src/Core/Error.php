<?php

namespace Nano\Core;

class Error
{
    public static function parse(string|object $message): array|object
    {
        return (json_validate($message)) ? json_decode($message) : [$message];
    }

	public static function throwJsonException(int $statusCode, string|object $message): never
	{
        header("HTTP/1.1 {$statusCode}");
        header('Content-type: application/json');

		echo json_encode(array('data' => array('errors' => self::parse($message))));

        die;
	}
}
