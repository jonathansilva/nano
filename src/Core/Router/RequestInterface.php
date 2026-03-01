<?php

namespace Nano\Core\Router;

use Nano\Core\Curl;

interface RequestInterface
{
    public function getMethod(): string;
    public function path(): string;
    public function data(?string $key = null): mixed;
    public function params(): ?object;
    public function query(): ?object;
    public function setQuery(string $key, string|object|null $value): ?object;
    public function headers(): ?object;
    public function authorizationBearer(): ?string;
    public function cookie(?string $key = null): mixed;
    public function setCookie(string $key, string $value, ?string $path = '/'): bool;
    public function hasCookie(string $key): bool;
    public function removeCookie(string $key): bool;
    public function session(?string $key = null): string|array;
    public function setSession(string $key, mixed $value): void;
    public function hasSession(string $key): bool;
    public function removeSession(string $key): void;
    public function validate(array $rules, ?array $attrs = [], ?string $lang = 'pt-BR'): void;
    public function http(): Curl;
}
