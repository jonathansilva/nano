<?php

namespace Nano\Core\Security;

use Nano\Core\{ Env, Error };
use Exception;

class JWT
{
    public static function assert(object $req, object $res, ?string $redirectTo): void
    {
        $info = self::getData($req, $req->path());

        if ($info['data'] === false) {
            if ($info['type'] == 'api') {
                Error::throwJsonException(401, 'Invalid or expired token');
            }

            if ($info['type'] == 'web') {
                $req->removeCookie('token');
                $res->redirect($redirectTo);
            }
        }

        $req->setQuery('data', $info['data']);
    }

    public static function ensure(object $req, object $res, ?string $redirectTo): void
    {
        if (str_starts_with($req->path(), '/api/')) {
            if (!$req->authorizationBearer()) {
                Error::throwJsonException(401, 'Authorization token not found in request');
            }

            return;
        }

        if (!$req->hasCookie('token')) {
            $res->redirect($redirectTo);
        }
    }

    public static function encode(array $data): string
    {
        // Header
        $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
        $headers_encoded = self::base64url_encode(json_encode($headers));

        $expirationInHours = (int) Env::fetch('JWT_EXP_IN_HOURS');

        // Payload
        $payload = array_merge($data, ['exp' => (time() + 3600) * $expirationInHours]);
        $payload_encoded = self::base64url_encode(json_encode($payload));

        $key = self::getKey();

        // Signature
        $signature = hash_hmac('SHA256', "{$headers_encoded}.{$payload_encoded}", $key, true);
        $signature_encoded = self::base64url_encode($signature);

        return "{$headers_encoded}.{$payload_encoded}.{$signature_encoded}";
    }

    public static function decode(string $token): false|object
    {
        $parts = explode('.', $token);
        $header = base64_decode($parts[0]);
        $payload = base64_decode($parts[1]);
        $signature_provided = $parts[2];

        $obj = json_decode($payload);

        $is_token_expired = ($obj->exp - time()) < 0;

        $key = self::getKey();

        $base64_url_header = self::base64url_encode($header);
        $base64_url_payload = self::base64url_encode($payload);
        $signature = hash_hmac('SHA256', "{$base64_url_header}.{$base64_url_payload}", $key, true);
        $base64_url_signature = self::base64url_encode($signature);

        $is_signature_valid = hash_equals($base64_url_signature, $signature_provided);

        if ($is_token_expired || !$is_signature_valid) {
            return false;
        }

        return $obj;
    }

    private static function getKey(): string
    {
        $envKey = trim(Env::fetch('JWT_KEY') ?? '');

        $key = (strlen($envKey) > 0) ? $envKey : null;

        return $key ?? throw new Exception('[JWT] Erro de autenticação no servidor');
    }

    private static function decodeTokenPayload(?string $token): mixed
    {
        return $token ? self::decode($token) : '';
    }

    private static function getData(object $req, string $path): array
    {
        if (str_starts_with($path, '/api/')) {
            return ['type' => 'api', 'data' => self::decodeTokenPayload($req->authorizationBearer())];
        }

        return ['type' => 'web', 'data' => self::decodeTokenPayload($req->cookie('token'))];
    }

    private static function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
