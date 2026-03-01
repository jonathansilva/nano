<?php

namespace Nano\Core\Router;

use Nano\Core\Container;
use Nano\Core\Router\{ Request, Response, RequestInterface, ResponseInterface };

final class Instance
{
    private function __construct() {}

    public static function create(): Router
    {
        $di = new Container();

        $di->bind(RequestInterface::class, Request::class);
        $di->bind(ResponseInterface::class, Response::class);

        return new Router($di);
    }
}
