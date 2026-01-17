<?php

namespace Nano\Core\Security;

use Nano\Core\Env;
use Exception;

class JWT
{
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
        $header = base64_decode($parts[0] ?? '');
        $payload = base64_decode($parts[1] ?? '');
        $signatureProvided = $parts[2] ?? '';

        $obj = json_decode($payload);

        if (!$obj) {
            return false;
        }

        $isTokenExpired = ($obj->exp ?? 0) - time() < 0;

        $key = self::getKey();

        $base64UrlHeader = self::base64url_encode($header);
        $base64UrlPayload = self::base64url_encode($payload);
        $signature = hash_hmac('SHA256', "{$base64UrlHeader}.{$base64UrlPayload}", $key, true);
        $base64UrlSignature = self::base64url_encode($signature);

        $isSignatureValid = hash_equals($base64UrlSignature, $signatureProvided);

        if ($isTokenExpired || !$isSignatureValid) {
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
}
