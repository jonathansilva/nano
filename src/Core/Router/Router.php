<?php

namespace Nano\Core\Router;

use Nano\Core\Router\{ Request, Response };
use Nano\Core\Error;
use Exception;
use Closure;

class Router
{
    private Request $request;

    private array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
    private array $globalsMiddlewares = [];
    private array $placeholdersValues = [];
    private array $routes = [];

    private string $path;
    private string $method;
    private string $notFoundCallback;

    public function __construct()
    {
        $this->request = new Request();
        $this->path = $this->request->path();
        $this->method = $this->getMethod();
    }

    private function getMethod(): string
    {
        $method = $this->request->method();

        if (!in_array($method, $this->allowedMethods)) {
            Error::throwJsonException(405, 'Method not allowed');
        }

        return $method;
    }

    public function __call(string $method, array $args): Router
    {
        $this->addRoute(strtoupper($method), ...$args);

        return $this;
    }

    public function use(string $callback): void
    {
        $this->globalsMiddlewares[] = $callback;
    }

    public function notFound(string $callback): void
    {
        $this->notFoundCallback = $callback;
    }

    private function addRoute(string $method, string $path, string|Closure $callback, array $middleware = []): void
    {
        $newRoute = [
            'path' => $path,
            'callback' => $callback
        ];

        if (!empty($middleware)) {
            $newRoute['middleware'] = $middleware;
        }

        $this->routes[$method][] = $newRoute;
    }

    public function load(string $path): void
	{
		$content = simplexml_load_file($path);

		foreach ($content as $routes) {
			$this->handleSimpleXMLRoutes($routes);
		}
    }

	private function handleSimpleXMLRoutes(object $route): void
	{
        $path = $route->path->__toString();
	    $method = $route->method->__toString();
	    $callback = $route->callback->__toString();

        $middlewares = [];

        if (!empty($route->middlewares)) {
            foreach ($route->middlewares->middleware as $middleware) {
                $middlewares[] = $middleware->__toString();
            }
        }

        $this->addRoute($method, $path, $callback, $middlewares);
	}

    public function start(): mixed
    {
        $route = $this->findRoute();

        if ($route) {
            $this->extractPlaceholdersValues($route['path']);

            if (!empty($this->globalsMiddlewares)) {
                $this->callMiddleware($this->globalsMiddlewares);
            }

            if (!empty($route['middleware'])) {
                $this->callMiddleware($route['middleware']);
            }

            if (is_callable($route['callback'])) {
                return $route['callback'](new $this->request($this->placeholdersValues), new Response);
            }

            return $this->handlerCallback($route['callback']);
        }

        return $this->routeNotFound();
    }

    private function findRoute(): array|null
    {
        $routes = $this->routes[$this->method] ?? [];

        $route = array_filter($routes, function ($checkRoute): bool|int {
            $pattern = preg_replace('#\{.*?\}#', '([^/]+)', $checkRoute['path']);
            $pattern = '#^' . $pattern . '/?$#';

            return preg_match($pattern, $this->path);
        });

        return $route ? reset($route) : null;
    }

    private function routeNotFound(): void
    {
        header('HTTP/1.1 404 Not Found');

        error_log('"notFound" function has not been defined');

        if (!isset($this->notFoundCallback)) {
            if (!str_starts_with($this->request->path(), '/api/')) {
                echo 'Page not found';

                return;
            }

            Error::throwJsonException(404, 'Route not found');
        }

        $this->handlerCallback($this->notFoundCallback);
    }

    private function extractPlaceholdersValues(string $path): void
    {
        //var_dump($path, $this->path); // "/hello/{name}" "/hello/jonathan"

        $pattern = preg_replace('/\{([a-zA-Z0-9_-]+)\}/', '(?<$1>[^/]+)', $path); // "/hello/(?[^/]+)"

        $pattern = '#^' . $pattern . '/?$#'; // "#^/hello/(?[^/]+)/?$#"

        preg_match($pattern, $this->path, $matches);

        //var_dump($matches); // [ 0 => "/hello/jonathan", "name" => "jonathan", 1 => "jonathan" ]

        $keys = array_keys($matches); // [ 0 => 0, 1 => "name", 2 => 1 ]
        $filter = array_filter($keys, 'is_string'); // [ 1 => "name" ]
        $flip = array_flip($filter); // [ "name" => 1 ]
        $intersect_key = array_intersect_key($matches, $flip); // [ "name" => "jonathan" ]

        $this->placeholdersValues = $intersect_key;
    }

    private function handlerCallback(string $class): void
    {
        $this->callClass($class)->handle(new $this->request($this->placeholdersValues), new Response);
    }

    private function callMiddleware(array $middlewares): void
    {
        foreach ($middlewares as $middleware) {
            $matchParsed = explode('::', $middleware);

            $middleware = $matchParsed[0];

            unset($matchParsed[0]);

            $args = array_values($matchParsed);

            $this->callClass($middleware)->handle(new $this->request($this->placeholdersValues), new Response, $args);
        }
    }

    private function callClass(string $class): object
    {
        if (!class_exists($class)) {
            throw new Exception("Class {$class} don't exists");
        }

        if (!method_exists($class, 'handle')) {
            throw new Exception("Method \"handle\" don't exists in {$class}");
        }

        return new $class;
    }
}
