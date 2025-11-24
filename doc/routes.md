# Routes

Verbos: GET, POST, PUT, PATCH e DELETE

```php
$app->get(
    '/me', // Path
    'App\Callback\Page\Me', // Callback
    ['App\Middleware\Token\Ensure'] // Middleware
);
```

O Callback ( ou Controller ) não permite chamada de método

```php
$app->get('/login', 'App\Callback\Page\Login@index'); ❌
```

```php
$app->get('/login', 'App\Callback\Page\Login'); ✔️
```

Crie o método `handle`

```php
class Login
{
    public function handle($req, $res): void
    {
        // ...
    }
}
```

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
        <callback>App\Callback\Page\Home</callback>
    </route>

    <route>
        <path>/me</path>
        <method>GET</method>
        <callback>App\Callback\Page\Me</callback>
        <middlewares>
            <middleware>App\Middleware\Token\Ensure</middleware>
        </middlewares>
    </route>

    <route>
        <path>/dashboard</path>
        <method>GET</method>
        <callback>App\Callback\Page\Dashboard</callback>
        <middlewares>
            <middleware>App\Middleware\Token\Ensure</middleware>
            <middleware>App\Middleware\Role::admin</middleware>
        </middlewares>
    </route>
</routes>
```

# Middleware

Middlewares devem ser informados no terceiro parâmetro da rota ( [Routes](#routes) )

Para configurar um middleware **global**, utilize o método `use`

```php
$app->use('App\Middleware\A');
$app->use('App\Middleware\B');
```

Veja abaixo alguns **exemplos** de middlewares

## Assert Middleware

```php
use Nano\Core\Security\{ CSRF, JWT };
```

Middleware global que faz proteção contra CSRF e decodifica o payload do JWT

```php
class Assert
{
    public function handle($req, $res): void
    {
        CSRF::assert($req, $res);
        JWT::assert($req, $res, '/login');
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
class Ensure
{
    public function handle($req, $res): void
    {
        JWT::ensure($req, $res, '/login');
    }
}
```

A criação dos middlewares Assert e Ensure, obriga que as **rotas de api**, tenham o prefixo `/api/` para evitar redirecionamento

## Role Middleware

Será chamado em rotas onde o usuário precisa ter níveis de acesso específicos

> Coloque após o `Ensure`

```php
$app->get(
    '/dashboard',
    'App\Callback\Page\Dashboard',
    ['App\Middleware\Token\Ensure', 'App\Middleware\Token\Role::admin']
);
```

```php
class Role
{
    public function handle($req, $res, $args): void
    {
        try {
            $id = $req->query()->data->sub;

            $role = new Service()->getRoleByUserId($id);

            if (!in_array($role, $args)) {
                $res->redirect('/me');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
```
