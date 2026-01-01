# Nano

*Nano framework PHP* para desenvolvimento de API's e aplicações Web

> public/index.php

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = Nano\Core\Router\Instance::create();

$app->use('App\Middlewares\Token\AssertMiddleware');

$app->get('/about', fn ($request, $response) => $response->view('about'));

$app->get('/hello/{name}', function ($request, $response): void {
    echo $request->params()->name;
});

$app->post('/api/register', function ($request, $response): void {
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
* [Form](doc/form.md)
* [JWT](doc/jwt.md)
* [Error handler](doc/error.md)
