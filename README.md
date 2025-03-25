# Nano

*Nano framework PHP* para desenvolvimento de API's e aplicações Web

**Requisitos**

* Composer
* PHP >= 8.4.0
* MySQL

Certifique-se de que as extensões abaixo, estejam habilitadas no *php.ini*

* extension=pdo_mysql
* extension=mbstring
* extension=curl

**Instalação**

`composer require jonathansilva/nano`

**Configuração**

*Apache*

```apache
RewriteEngine On
Options All -Indexes

RewriteCond %{SCRIPT_FILENAME} !-f
RewriteCond %{SCRIPT_FILENAME} !-d
RewriteRule ^(.*)$ index.php?uri=/$1 [L,QSA]
```

*Nginx*

```nginx
location / {
    if ($script_filename !~ "-f") {
        rewrite ^(.*)$ /index.php?uri=/$1 break;
    }
}
```

.env.example

```
DB_HOST=localhost
DB_USER=root
DB_PASS=password
DB_NAME=database

JWT_KEY=anything
JWT_EXP_IN_HOURS=8

COOKIE_EXP_IN_DAYS=1
COOKIE_DOMAIN=localhost
COOKIE_HTTPS=false
COOKIE_HTTPONLY=true
COOKIE_SAMESITE=Strict

CURL_SSL_VERIFYPEER=false

TEMPLATE_ENGINE_CACHE=false
```

Duplique o arquivo, renomeie para **.env** e altere os valores

.gitignore

```
.idea
.env
.vscode/
vendor/
cache/
composer.phar
composer.lock
```

index.php

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = Nano\Core\Router\Instance::create();

$app->use('App\Middleware\Token\Assert');

$app->notFound('App\Callback\Page\NotFound');

$app->get('/about', fn ($req, $res) => $res->view('about'));

$app->get('/hello/{name}', function ($req, $res) {
    echo $req->params()->name;
});

$app->post('/api/test', function ($req, $res) {
    $res->json(200, array('message' => 'Hello World!'));
});

$app->start();
```

# Routes

Verbos: GET, POST, PUT, PATCH e DELETE

```php
$app->get(
    '/me', // Path
    'App\Callback\Page\Me', // Callback
    ['App\Middleware\Token\Ensure'] // Middleware
);
```

O Callback/Controller não permite chamada de método

❌

```php
$app->get('/login', 'App\Callback\Page\Login@index');
```

✔️

```php
$app->get('/login', 'App\Callback\Page\Login');
```

Crie o método 'handle'

```php
class Login
{
    public function handle($req, $res)
    {
        // TODO
    }
}
```

## Routes file

Para carregar um arquivo de rotas, utilize o método 'load'

```php
$app->load(__DIR__ . '/src/routes.xml');
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

Para configurar um middleware **global**, utilize o método 'use'

```php
$app->use('App\Middleware\A');
$app->use('App\Middleware\B');
```

Veja abaixo alguns **exemplos** de middlewares

## Assert Middleware

Middleware global que faz proteção contra CSRF e decodifica o payload do JWT

*CSRF*

Os formulários deverão ter um campo 'hidden' chamado 'csrf'

```html
<input type="hidden" name="csrf" value="{{ $csrf }}">
```

> O uso do CSRF necessita do `session_start();` no index.php

*JWT*

1 ) Se o token existir mas for inválido:

[ Web ] Redireciona para a página 'login'

[ API ] Retorna 'Invalid or expired token'

Caso for válido, o payload será enviado para o próximo middleware ou controller, podendo ser recuperado usando `$req->query()` ( veja um exemplo em [Role Middleware](#role-middleware) )

2 ) Se não existir, vai para o próximo middleware ou executa o controller

```php
<?php

namespace App\Middleware\Token;

use Nano\Core\Security\{ CSRF, JWT };

class Assert
{
    public function handle($req, $res)
    {
        CSRF::assert($req, $res);
        JWT::assert($req, $res, '/login');
    }
}
```

> O terceiro parâmetro em `JWT::assert` é ignorado em rotas de api

## Ensure Middleware

Será chamado em rotas onde a autenticação é obrigatória

Se não encontrar o token:

[ Web ] Redireciona para a página 'login'

[ API ] Retorna 'Authorization token not found in request'

```php
<?php

namespace App\Middleware\Token;

use Nano\Core\Security\JWT;

class Ensure
{
    public function handle($req, $res)
    {
        JWT::ensure($req, $res, '/login');
    }
}
```

A criação dos middlewares Assert e Ensure, obriga que as **rotas de api**, tenham o prefixo '/api/' para evitar redirecionamento

> O terceiro parâmetro em `JWT::ensure` é ignorado em rotas de api

## Role Middleware

Será chamado em rotas onde o usuário precisa ter níveis de acesso específicos

> Coloque após o 'Ensure'

```php
$app->get(
    '/dashboard',
    'App\Callback\Page\Dashboard',
    ['App\Middleware\Token\Ensure', 'App\Middleware\Token\Role::admin']
);
```

```php
<?php

namespace App\Middleware;

use App\Service\Auth\Role as Service;
use Exception;

class Role
{
    public function handle($req, $res, $args)
    {
        try {
            $id = $req->query()->data->id;

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

# Query params

> localhost:8080/books?filter=price

```php
<?php

namespace App\Callback\Page;

use App\Service\Book\Read as Service;
use Exception;

class Book
{
    public function handle($req, $res)
    {
        try {
            $filter = $req->query()->filter ?? null;

            $res->view('books', array('books' => new Service()->all($filter)));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
```

# cURL

Verbos: GET, POST, PUT, PATCH e DELETE

```php
<?php

namespace App\Callback\Payment;

use Nano\Core\Error;
use Exception;

class Create
{
    public function handle($req, $res)
    {
        try {
            $url = 'https://...';

            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer TOKEN'
            ];

            $body = json_encode(array(...));

            $data = $req->http()->post($url, $headers, $body);

            if (!$data) {
                throw new Exception('Erro ao realizar requisição');
            }

            $info = json_decode($data);

            $res->json($info->status, array('message' => $info->message));
        } catch (Exception $e) {
            Error::throwJsonException(500, $e->getMessage());
        }
    }
}
```

# Validator

Regras: required, string, integer, float, bool, email, confirmed, min e max

> Caso não houver erros na validação, um novo objeto será retornado em `$req->data()` com os dados [sanitizados](https://github.com/jonathansilva/nano/blob/master/src/Core/Security/Sanitize.php)

```php
<?php

namespace App\Callback\Book;

use App\Service\Book\Create as Service;
use Nano\Core\Error;
use Exception;

class Create
{
    public function handle($req, $res)
    {
        try {
            $rules = [
                'title' => 'required|string',
                'description' => 'required|string|max:255',
                'authors' => [
                    'name' => 'required|string',
                    'website' => 'string'
                ]
            ];

            $req->validate($rules);

            $data = $req->data();

            $res->json(201, array('message' => new Service()->register($data)));
        } catch (Exception $e) {
            Error::throwJsonException(500, $e->getMessage());
        }
    }
}
```

```php
$rules = [
    'password' => 'required|string|min:8|confirmed'
];
```

O uso do `confirmed` exige um novo input, onde o 'name' precisa ter o sufixo '_confirmation'

```html
<div class="field">
    <label for="password_confirmation">Confirmar senha</label>

    <input type="password" id="password_confirmation" name="password_confirmation" spellcheck="false" autocomplete="off">
</div>
```

> Por padrão, as mensagens de erro estão em português. As opções aceitas são 'pt-BR' e 'en-US'

```php
$req->validate($rules, 'en-US');
```

# JSON Exception

Use `throwJsonException` para exibir erros no formato json

```php
<?php

namespace App\Callback\Book;

use Nano\Core\Error;
use Exception;

class Read
{
    public function handle($req, $res)
    {
        try {
            // TODO
        } catch (Exception $e) {
            Error::throwJsonException(500, $e->getMessage());
        }
    }
}
```

# Template engine

O template utilizado foi desenvolvido por David Adams ( https://codeshack.io )

> Foram feitas pequenas alterações no código original

base.html

```html
<!DOCTYPE html>

<html lang="pt-BR">
    <head>
        <title>{% yield title %}</title>

        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="/assets/style.css">
    </head>

    <body>
        {# comentário de teste #}
        <main>
            {% yield content %}
        </main>
    </body>
</html>
```

home.html

```html
{% extends base %}

{% block title %}Nano Framework{% endblock %}

{% block content %}
<h1>{{ $welcome }}</h1>
{% endblock %}
```

```php
<?php

namespace App\Callback\Page;

class Home
{
    public function handle($req, $res)
    {
        $res->view('home', array('welcome' => 'Welcome to Nano!'));
    }
}
```

> Crie o diretório 'views'

Exibindo os erros de validação ( [Validator](#validator) )

```html
{% foreach ($errors as $value): %}
    <div>{{ $value }}</div>
{% endforeach; %}
```

Para saber mais sobre este template engine, [clique aqui](https://codeshack.io/lightweight-template-engine-php)

# CORS

Coloque no index.php de sua API e faça as modificações necessárias

```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE');
header('Access-Control-Allow-Headers: Origin, Accept, Content-Type, Authorization, X-Requested-With');
```

# Login / Register

> Exemplo de login e cadastro de usuário

GET /cadastro

```php
<?php

namespace App\Callback\Page;

use Nano\Core\View\Form;

class Register
{
    public function handle($req, $res)
    {
        if ($req->hasCookie('token')) {
            $res->redirect('/me');
        }

        $form = Form::session($req);

        $res->view('register', [
            'csrf' => $form->csrf,
            'errors' => $form->errors
        ]);
    }
}
```

POST /cadastro

> Ao usar Cookie para salvar o JWT, nomeie-o de 'token'. Isso é necessário pois há funções na classe JWT, que *busca*, *verifica* e *remove* o cookie pelo nome 'token'

```php
<?php

namespace App\Callback\User;

use App\Service\User\Create as Service;
use Nano\Core\Error;
use Exception;

class Create
{
    public function handle($req, $res)
    {
        try {
            $rules = [
                'name' => 'required|string',
                'email' => 'required|email|confirmed',
                'password' => 'required|string|min:8|confirmed'
            ];

            $req->validate($rules);

            $data = $req->data();

            $req->setCookie('token', new Service()->register($data));
            $res->redirect('/me');
        } catch (Exception $e) {
            $req->setSession('errors', Error::parse($e->getMessage()));
            $res->redirect('/cadastro');
        }
    }
}
```

Service

```php
<?php

namespace App\Service\User;

use Nano\Core\Database;
use Nano\Core\Security\JWT;
use Exception;
use PDO;

class Create
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    public function register(object $data): string
    {
        if ($this->emailExists($data->email)) {
            throw new Exception('O e-mail informado já existe');
        }

        $hash = password_hash($data->password, PASSWORD_ARGON2ID);

        try {
            $query = "INSERT INTO users (name, email, password) VALUES (:name, :email, :password)";

            $stmt = $this->db->prepare($query);
            $stmt->bindValue(':name', $data->name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $data->email, PDO::PARAM_STR);
            $stmt->bindValue(':password', $hash, PDO::PARAM_STR);
            $stmt->execute();

            return JWT::encode(array('id' => $this->db->lastInsertId()));
        } catch (PDOException $e) {
            throw new Exception('Erro ao cadastrar, tente novamente');
        }
    }

    private function emailExists(string $email): bool
    {
        $query = "SELECT id FROM users WHERE email = :email LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->execute(array(':email' => $email));

        return (bool) $stmt->fetchColumn();
    }
}
```

GET /login

```php
<?php

namespace App\Callback\Page;

use Nano\Core\View\Form;

class Login
{
    public function handle($req, $res)
    {
        if ($req->hasCookie('token')) {
            $res->redirect('/me');
        }

        $form = Form::session($req);

        $res->view('login', [
            'csrf' => $form->csrf,
            'errors' => $form->errors
        ]);
    }
}
```

POST /login

```php
<?php

namespace App\Callback\Auth;

use App\Service\Auth\Login as Service;
use Nano\Core\Error;
use Exception;

class Login
{
    public function handle($req, $res)
    {
        try {
            $rules = [
                'email' => 'required|email',
                'password' => 'required|string'
            ];

            $req->validate($rules);

            $data = $req->data();

            $req->setCookie('token', new Service()->authenticate($data));
            $res->redirect('/me');
        } catch (Exception $e) {
            $req->setSession('errors', Error::parse($e->getMessage()));
            $res->redirect('/login');
        }
    }
}
```

Service

```php
<?php

namespace App\Service\Auth;

use Nano\Core\Database;
use Nano\Core\Security\JWT;
use Exception;
use PDO;

class Login
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    public function authenticate(object $data): string
    {
        $query = "SELECT id, password FROM users WHERE email = :email";

        $stmt = $this->db->prepare($query);
        $stmt->execute(array(':email' => $data->email));

        $result = $stmt->fetchObject();

        if (!password_verify($data->password, $result->password)) {
            throw new Exception('E-mail ou senha inválido');
        }

        return JWT::encode(array('id' => $result->id));
    }
}
```

GET /logout

```php
<?php

namespace App\Callback\Auth;

class Logout
{
    public function handle($req, $res)
    {
        if ($req->hasCookie('token')) {
            $req->removeCookie('token');
            $res->redirect('/login');
        }

        $res->redirect('/');
    }
}
```

GET /me

```php
<?php

namespace App\Callback\Page;

use App\Service\User\Read as Service;
use Exception;

class Me
{
    public function handle($req, $res)
    {
        try {
            $id = $req->query()->data->id;

            $res->view('me', array('data' => new Service()->getUserInfoById($id)));
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}
```
