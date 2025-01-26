<?php

namespace Nano\Core\Router;

class Instance
{
    public static function create(): Router
    {
        return new Router();
    }
}
