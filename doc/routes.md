# Routes

Verbos: GET, POST, PUT, PATCH e DELETE

```php
$app = Nano\Core\Router\Instance::create();

// Rota não encontrada (opcional)
$app->notFound('App\Actions\NotFoundAction');

$app->get(
    '/me', // Path
    'App\Actions\Profile\ShowProfileAction', // Action
    ['App\Middlewares\Token\EnsureMiddleware'] // Middlewares (opcional)
);

$app->run();
```

---

O padrão Action não permite chamada de métodos específicos

```php
$app->post('/login', 'App\Actions\Auth\LoginAction@authenticate'); ❌
```

```php
$app->post('/login', 'App\Actions\Auth\LoginAction'); ✔️
```

> Toda Action deve implementar o método `handle`

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

As dependências declaradas no `__construct()` são resolvidas automaticamente pelo [Container](container.md)

## Routes file

Para carregar um arquivo de rotas, utilize o método `load`

```php
$app->load(__DIR__ . '/../src/routes.xml');
```

routes.xml

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

# Middleware

Middlewares devem ser informados no terceiro parâmetro da rota ( [Routes](#routes) )

Para configurar um middleware **global**, utilize o método `use`

```php
$app->use('App\Middlewares\A');
$app->use('App\Middlewares\B');
```

Veja abaixo alguns **exemplos** de middlewares

## Assert Middleware

```php
use Nano\Core\Security\{ CSRF, JWT };
```

Middleware global que faz proteção contra CSRF e decodifica o payload do JWT

```php
final readonly class AssertMiddleware
{
    public function handle($request, $response): void
    {
        CSRF::assert($request, $response);
        JWT::assert($request, $response, '/login');
    }
}
```

Veja também: [CSRF](csrf.md) e [JWT](jwt.md)

## Ensure Middleware

```php
use Nano\Core\Security\JWT;
```

Será chamado em rotas onde a autenticação é obrigatória

```php
final readonly class EnsureMiddleware
{
    public function handle($request, $response): void
    {
        JWT::ensure($request, $response, '/login');
    }
}
```

A criação dos middlewares Assert e Ensure, obriga que as **rotas de api**, tenham o prefixo `/api/` para evitar redirecionamento

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
