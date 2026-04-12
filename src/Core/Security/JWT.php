<?php

namespace Nano\Core\Security;

use Nano\Core\Env;
use Exception;

final class JWT
{
    private function __construct() {}

    public static function encode(array $data): object
    {
        // Header
        $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
        $headersEncoded = self::base64url_encode(json_encode($headers));

        // Payload
        $exp = self::getExpTime();

        $payloadAccess = array_merge($data, ['exp' => $exp['access']]);
        $payloadAccessEncoded = self::base64url_encode(json_encode($payloadAccess));

        $payloadRefresh = ['exp' => $exp['refresh']];
        $payloadRefreshEncoded = self::base64url_encode(json_encode($payloadRefresh));

        // Signature
        $key = self::getKey();

        $signatureAccess = hash_hmac('SHA256', "{$headersEncoded}.{$payloadAccessEncoded}", $key, true);
        $signatureAccessEncoded = self::base64url_encode($signatureAccess);

        $signatureRefresh = hash_hmac('SHA256', "{$headersEncoded}.{$payloadRefreshEncoded}", $key, true);
        $signatureRefreshEncoded = self::base64url_encode($signatureRefresh);

        return (object) [
            'access' => "{$headersEncoded}.{$payloadAccessEncoded}.{$signatureAccessEncoded}",
            'refresh' => "{$headersEncoded}.{$payloadRefreshEncoded}.{$signatureRefreshEncoded}"
        ];
    }

    public static function decode(string $token): false|object
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        $header = self::base64url_decode($parts[0]);
        $header = json_decode($header, true);

        if (!$header || ($header['alg'] ?? '') !== 'HS256' || ($header['typ'] ?? '') !== 'JWT') {
            return false;
        }

        $payload = self::base64url_decode($parts[1]);

        $obj = json_decode($payload);

        if (!$obj || !isset($obj->exp)) {
            return false;
        }

        if ($obj->exp < time()) {
            return false;
        }

        $key = self::getKey();

        $signature = hash_hmac('SHA256', "{$parts[0]}.{$parts[1]}", $key, true);
        $base64UrlSignature = self::base64url_encode($signature);

        if (!hash_equals($base64UrlSignature, $parts[2])) {
            return false;
        }

        return $obj;
    }

    private static function getExpTime(): array
    {
        $envAccessType = strtolower(Env::fetch('JWT_ACCESS_EXP_TYPE'));
        $envRefreshType = strtolower(Env::fetch('JWT_REFRESH_EXP_TYPE'));

        $envAccessTime = (int) Env::fetch('JWT_ACCESS_EXP_TIME');
        $envRefreshTime = (int) Env::fetch('JWT_REFRESH_EXP_TIME');

        $accessTime = ($envAccessTime > 0) ? $envAccessTime : null;
        $refreshTime = ($envRefreshTime > 0) ? $envRefreshTime : null;

        return [
            'access' => match ($envAccessType) {
                'minutes' => time() + 60 * ($accessTime ?? 5),
                'hours' => time() + 3600 * ($accessTime ?? 1),
                'days' => time() + (3600 * 24) * ($accessTime ?? 1),
                default => time() + 3600 * ($accessTime ?? 1) // hours
            },
            'refresh' => match ($envRefreshType) {
                'hours' => time() + 3600 * ($refreshTime ?? 8),
                'days' => time() + (3600 * 24) * ($refreshTime ?? 7),
                default => time() + (3600 * 24) * ($refreshTime ?? 7) // days
            }
        ];
    }

    private static function getKey(): string
    {
        $envKey = Env::fetch('JWT_KEY') ?? '';

        $key = (strlen($envKey) > 0) ? $envKey : null;

        return $key ?? throw new Exception('[JWT] Erro de autenticação no servidor');
    }

    private static function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64url_decode(string $data): string|false
    {
        $base64 = strtr($data, '-_', '+/');

        $remainder = strlen($base64) % 4;

        if ($remainder) {
            $padlen = 4 - $remainder;
            $base64 .= str_repeat('=', $padlen);
        }

        return base64_decode($base64, true);
    }
}
