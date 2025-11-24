<?php

namespace Nano\Core\Router;

use Nano\Core\View\Template;

class Response
{
    public function view(string $file, ?array $data = []): ?Template
    {
        return Template::render($file, $data);
    }

    public function redirect(string $path, ?int $code = 302): never
    {
        http_response_code($code);

        header("Location: {$path}");

        die;
    }

    public function json(int $code, mixed $data): void
    {
        http_response_code($code);

        header('Content-Type: application/json');

        echo json_encode(['data' => $data], JSON_PRETTY_PRINT);

        die;
    }
}
