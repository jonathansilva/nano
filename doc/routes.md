# Routes

Verbos: GET, POST, PUT, PATCH e DELETE

> public/index.php

```php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = Nano\Core\Router\Instance::create();

// Middleware global
$app->use('App\Middlewares\Token\AssertMiddleware');

// Rota não encontrada ( opcional )
$app->notFound('App\Actions\Page\ShowNotFoundAction');

$app->get(
    '/me', // Path
    'App\Actions\Profile\ShowProfileAction', // Action
    ['App\Middlewares\Token\EnsureMiddleware'] // Middlewares ( opcional )
);

$app->run();
```

**Parâmetros e Restrições ( Regex )**

Permite aplicar restrições *inline* para validar o formato do parâmetro

**Obs:** Parâmetros devem ficar entre chaves

| Rota                                                          | Descrição                                                                                   |
| :------------------------------------------------------------ | :-------------------------------------------------------------------------------------------- |
| `GET /product/{slug}`                                       | Sem restrições ( não recomendado )                                                         |
| `GET /product/{slug:[a-z0-9](?:(?!--)[a-z0-9-])*[a-z0-9]}`  | Letras minúsculas, números e `-`. Pode iniciar e terminar com letra ou número |
| `DELETE /product/{id:\d+}`                                  | Apenas números                                                                               |
| `GET /profile/{username:[a-z](?:(?!__)[a-z0-9_])*[a-z0-9]}` | Letras minúsculas, números e `_`. Deve iniciar com letra                 |

---

Além de passar o `namespace` como uma string literal, o Nano suporta a resolução de nome de classe*

```php
// String literal
$app->get('/', 'App\Actions\Page\ShowHomeAction');

// Resolução de nome de classe
use App\Actions\Page\ShowHomeAction;

$app->get('/', ShowHomeAction::class);
```

*Não há suporte no arquivo de rotas ( [Routes file](#routes-file) )

---

O padrão Action não permite chamada de métodos específicos

```php
$app->post('/login', 'App\Actions\Auth\LoginAction@authenticate'); ❌
```

```php
$app->post('/login', 'App\Actions\Auth\LoginAction'); ✔️
```

Toda Action deve implementar o método `handle`

<details>
<summary>Exemplo</summary>

```php
final readonly class LoginAction
{
    public function __construct(
        private AuthService $authService,
        private LoginService $loginService
    ) {}

    public function handle($request, $response): void
    {
        // ...
    }
}
```

**Obs:** As dependências declaradas no `__construct()` são resolvidas automaticamente pelo [Container](container.md)
</details>

## Routes file

Para carregar um arquivo de rotas, utilize o método `load`

```php
$app->load(__DIR__ . '/../src/routes.xml');
```

<details>
<summary>XML</summary>

```xml
<?xml version="1.0" encoding="UTF-8"?>

<routes>
    <route>
        <path>/</path>
        <method>GET</method>
        <action>App\Actions\Page\ShowHomeAction</action>
    </route>

    <route>
        <path>/me</path>
        <method>GET</method>
        <action>App\Actions\Profile\ShowProfileAction</action>
        <middlewares>
            <middleware>App\Middlewares\Token\EnsureMiddleware</middleware>
        </middlewares>
    </route>

    <route>
        <path>/dashboard</path>
        <method>GET</method>
        <action>App\Actions\Dashboard\ShowDashboardAction</action>
        <middlewares>
            <middleware>App\Middlewares\Token\EnsureMiddleware</middleware>
            <middleware>App\Middlewares\RoleMiddleware::admin</middleware>
        </middlewares>
    </route>
</routes>
```
</details>

# Middleware

Middlewares devem ser informados no terceiro parâmetro da rota ( [Routes](#routes) )

Para configurar um middleware global, utilize o método `use`

```php
$app->use('App\Middlewares\A');
$app->use('App\Middlewares\B');
```

Veja abaixo alguns **exemplos** de middlewares

## Assert Middleware

Middleware **global** que decodifica o payload do JWT

<details>
<summary>Exemplo</summary>

```php
final readonly class AssertMiddleware
{
    public function handle($request, $response): void
    {
        if (str_ends_with($request->path(), '/refresh-token')) {
            return;
        }

        $request->setQuery('data', null);

        $token = $request->authorizationBearer() ?? $request->cookie('token');

        if (!$token) {
            return;
        }

        $payload = JWT::decode($token);

        if (!$payload) {
            if (str_starts_with($request->path(), '/api/')) {
                Error::throwJsonException(401, 'Invalid or expired token');
            }

            $request->removeCookie('token');
            $response->redirect('/login');
        }

        $request->setQuery('data', $payload);
    }
}
```
</details>

Veja também: [JWT](jwt.md)

## Ensure Middleware

Será chamado em rotas onde a autenticação é obrigatória

<details>
<summary>Exemplo</summary>

```php
final readonly class EnsureMiddleware
{
    public function handle($request, $response): void
    {
        if (str_starts_with($request->path(), '/api/')) {
            if (!$request->authorizationBearer()) {
                Error::throwJsonException(401, 'Authorization token not found in request');
            }

            return;
        }

        if (!$request->hasCookie('token')) {
            $response->redirect('/login');
        }
    }
}
```
</details>

## Role Middleware

Será chamado em rotas onde o usuário precisa ter níveis de acesso específicos

> Coloque após o `EnsureMiddleware`

```php
$app->get(
    '/dashboard',
    'App\Actions\Dashboard\ShowDashboardAction',
    [
        'App\Middlewares\Token\EnsureMiddleware',
        'App\Middlewares\Token\RoleMiddleware::admin'
    ]
);
```

<details>
<summary>Exemplo</summary>

```php
final readonly class RoleMiddleware
{
    public function __construct(private UserService $service) {}

    public function handle($request, $response, $args): void
    {
        $id = $request->query()->data->sub;

        $user = $this->service->find($id);

        // $args => ['admin']
        if (!in_array($user->role, $args)) {
            $response->redirect('/me');
        }
    }
}
```
</details>

---

Veja também: [CSRF](csrf.md)
