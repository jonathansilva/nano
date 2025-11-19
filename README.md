# Nano

*Nano framework PHP* para desenvolvimento de API's e aplicações Web

> public/index.php

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = Nano\Core\Router\Instance::create();

$app->use('App\Middleware\Token\Assert');

$app->notFound('App\Callback\Page\NotFound');

$app->get('/about', fn ($req, $res) => $res->view('about'));

$app->get('/hello/{name}', function ($req, $res): void {
    echo $req->params()->name;
});

$app->post('/api/register', function ($req, $res): void {
    $res->json(201, array('message' => 'Cadastrado com sucesso'));
});

$app->start();
```

---

*Nano docs*

**[Installation](doc/installation.md)**
[Routes](doc/routes.md)
[Request](doc/request.md)
[Response](doc/response.md)
[Template engine](doc/template.md)
[Database](doc/database.md)
[Validator](doc/validator.md)
[CORS](doc/cors.md)
[CSRF](doc/csrf.md)
[cURL](doc/curl.md)
[Form](doc/form.md)
[JWT](doc/jwt.md)
[Error handler](doc/error.md)
