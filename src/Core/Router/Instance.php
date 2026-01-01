<?php

namespace Nano\Core\Router;

use Nano\Core\Container;

class Instance
{
    public static function create(): Router
    {
        return new Router(new Container());
    }
}
