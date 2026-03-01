<?php

namespace Nano\Core\Router;

use Nano\Core\Router\RequestInterface;

interface InternalRequestInterface extends RequestInterface
{
    public function setParams(array $params): void;
}
