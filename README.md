# Nano

*Nano framework PHP* para desenvolvimento de API's e aplicações Web

```php
$app = Nano\Core\Router\Instance::create();

$app->get('/', 'App\Actions\Page\ShowHomeAction');

$app->get('/{name:[a-zA-Z][a-zA-Z]+}', function ($request, $response): void {
    echo "Olá, {$request->params()->name}";
});

$app->post('/api/users', function ($request, $response): void {
    $response->json(201, ['message' => 'Cadastrado com sucesso']);
});

$app->run();
```

---

*Nano docs*

* **[Installation](doc/installation.md)**
* [Routes](doc/routes.md)
* [Request](doc/request.md)
* [Response](doc/response.md)
* [Container](doc/container.md)
* [Database](doc/database.md)
* [Template engine](doc/template.md)
* [Validator](doc/validator.md)
* [CLI](doc/cli.md)
* [CORS](doc/cors.md)
* [CSRF](doc/csrf.md)
* [cURL](doc/curl.md)
* [Env](doc/env.md)
* [JWT](doc/jwt.md)
* [Error handler](doc/error.md)
