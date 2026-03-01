<?php

namespace Nano\Core\Router;

use Nano\Core\View\Template;

interface ResponseInterface
{
    public function view(string $file, ?array $data = []): ?Template;
    public function redirect(string $path, ?int $code = 302): void;
    public function json(int $code, mixed $data): void;
}
