<?php

namespace Nano\Core\Router;

use Nano\Core\Security\{ Sanitize, Validator };
use Nano\Core\{ Curl, Env };
use Exception;

class Request
{
    private array $cookie;
    private array $session;

    private ?object $data;
    private ?object $headers;
    private ?object $params;

    public function __construct(array $parameters = [])
    {
        if ($parameters) {
            $this->setParams($parameters);
        }

        $this->setData();
        $this->setHeaders();

        $this->cookie = $_COOKIE;
        $this->session = $_SESSION ?? [];
    }

    public function method(): string
	{
		return parse_url($_SERVER['REQUEST_METHOD'], PHP_URL_PATH);
	}

    public function path(): string
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (!empty($_SERVER['REDIRECT_BASE'])) {
			$path = str_replace($_SERVER['REDIRECT_BASE'], '', $path);
		}

		return $path;
    }

    public function data(?string $key = null): mixed
    {
        if ($key) {
            return $this->data->$key ?? '';
        }

        return $this->data;
    }

    private function setData(): void
    {
        if ($this->method() == 'OPTIONS') {
            header('HTTP/1.1 200 OK');

            die;
        }

        if ($this->method() == 'GET') {
            return;
        }

        $contentType = $_SERVER['CONTENT_TYPE'];

        if ($contentType == 'application/json') {
            $this->data = json_decode(file_get_contents('php://input'));
        }

        if ($contentType == 'application/x-www-form-urlencoded') {
            $this->data = (object) $_POST;
        }
    }

    public function params(): ?object
    {
        return $this->params;
    }

    private function setParams(array|object $parameters): void
    {
        $this->params = (object) $parameters;
    }

    public function query(): ?object
    {
        return (object) $_GET;
    }

    public function setQuery(string $key, string|object $value): ?object
    {
        return (object) $_GET[$key] = $value;
    }

    public function headers(): ?object
    {
        return $this->headers;
    }

    private function setHeaders(): void
    {
        $this->headers = (object) getallheaders();
    }

    public function authorizationBearer(): ?string
    {
        $authorization = $this->headers()->Authorization ?? null;

        if (!$authorization) {
            return null;
        }

        return explode('Bearer ', $authorization)[1] ?? null;
    }

    public function cookie(?string $key = null): mixed
    {
        if ($key) {
            return $this->cookie[$key] ?? null;
        }

        return $this->cookie;
    }

    public function setCookie(string $key, string $value): bool
    {
        $this->cookie[$key] = $value;

        return setcookie($key, $value, [
            'expires' => time() + (60 * 60 * 24 * (int) Env::fetch('COOKIE_EXP_IN_DAYS')),
            'path' => '/',
            'domain' => Env::fetch('COOKIE_DOMAIN'),
            'secure' => (bool) Env::fetch('COOKIE_HTTPS'),
            'httponly' => (bool) Env::fetch('COOKIE_HTTPONLY'),
            'samesite' => Env::fetch('COOKIE_SAMESITE')
        ]);
    }

    public function hasCookie(string $key): bool
	{
		return !empty($this->cookie[$key]);
	}

    public function removeCookie(string $key): bool
    {
        unset($this->cookie[$key]);
        unset($_COOKIE[$key]);

        return setcookie($key, '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => Env::fetch('COOKIE_DOMAIN')
        ]);
    }

    public function session(?string $key = null): string|array
    {
        if ($key) {
            if ($key == 'errors') {
                return $this->session[$key] ?? [];
            }

            return $this->session[$key] ?? '';
        }

        return $this->session;
    }

    public function setSession(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function hasSession(string $key): bool
	{
		return !empty($this->session[$key]);
	}

    public function removeSession(string $key): void
    {
        unset($this->session[$key]);
        unset($_SESSION[$key]);
    }

    public function validate(array $rules, ?string $lang = 'pt-BR'): void
    {
        $data = $this->data();

        if (!$data) {
            throw new Exception('No data sent in the request');
        }

        if (!in_array($lang, ['pt-BR', 'en-US'])) {
            throw new Exception("Only 'pt-BR' and 'en-US' are allowed on 'validate' method");
        }

        $errors = Validator::schema($data, $rules, $lang);

        if ($errors) {
            throw new Exception(json_encode($errors));
        }

        $this->data = Sanitize::data((array) $data);
    }

    public function http(): Curl
    {
        return new Curl();
    }
}
