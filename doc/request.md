# Request

### `method(): string`

Retorna o método HTTP da requisição

### `path(): string`

Retorna o caminho da URI

Exemplo:

*http://localhost:8080/api/users*

> /api/users

### `data(?string $key = null): mixed`

Acessa os dados do corpo da requisição

* Se `$key` for fornecido, retorna o valor ou uma string vazia se não existir
* Se `$key` for null ( padrão ), retorna todos os dados

| Parâmetro | Tipo        | Descrição                |
| :--------- | :---------- | :------------------------- |
| `$key`   | `?string` | Nome da chave ( opcional ) |

> Os dados são preenchidos automaticamente pelo método privado `setData`

### `params(): ?object`

Retorna os parâmetros da rota

Exemplo:

*http://localhost:8080/hello/Jonathan*

```php
$app->get('/hello/{name}', function ($req, $res): void {
    echo $req->params()->name; // Jonathan
});
```

### `query(): ?object`

Retorna a query string da URI

Exemplo:

*http://localhost:8080/t-shirts?filter=price*

```php
$filter = $req->query()->filter ?? null; // price
```

### `setQuery(string $key, string|object $value): ?object`

Define uma chave e valor na query string

| Parâmetro | Tipo              | Descrição        |
| :--------- | :---------------- | :----------------- |
| `$key`   | `string`        | Nome para a chave  |
| `$value` | `string\|object` | Valor para a chave |

### `headers(): ?object`

Retorna os cabeçalhos HTTP da requisição

### `authorizationBearer(): ?string`

Retorna o token do cabeçalho `Authorization`

### `cookie(?string $key = null): mixed`

Retorna os cookies da requisição

* Se `$key` for fornecido, retorna o valor ou null se não existir
* Se `$key` for null ( padrão ), retorna todos os cookies

| Parâmetro | Tipo        | Descrição                |
| :--------- | :---------- | :------------------------- |
| `$key`   | `?string` | Nome da chave ( opcional ) |

### `setCookie(string $key, string $value): bool`

Cria um cookie com parâmetros definidos por variáveis de ambiente

| Parâmetro | Tipo        | Descrição                 |
| :--------- | :---------- | :-------------------------- |
| `$key`   | `string`  | Nome para a chave           |
| `$value` | `string`  | Valor para a chave          |

> O cookie será criado com **httponly** habilitado

### `hasCookie(string $key): bool`

Verifica se o cookie existe

| Parâmetro | Tipo       | Descrição   |
| :--------- | :--------- | :------------ |
| `$key`   | `string` | Nome da chave |

### `removeCookie(string $key): bool`

Invalida e remove o cookie

| Parâmetro | Tipo       | Descrição   |
| :--------- | :--------- | :------------ |
| `$key`   | `string` | Nome da chave |

### `session(?string $key = null): string|array`

Acessa os dados da sessão

* Se `$key` for fornecido:
  * Se `$key` for *errors*, retorna um array com os erros ou um array vazio
  * Caso contrário, retorna o valor da chave ou uma string vazia
* Se `$key` for null ( padrão ), retorna todos os dados da sessão

| Parâmetro | Tipo        | Descrição                |
| :--------- | :---------- | :------------------------- |
| `$key`   | `?string` | Nome da chave ( opcional ) |

### `setSession(string $key, mixed $value): void`

Define a chave e seu valor na sessão

| Parâmetro | Tipo       | Descrição        |
| :--------- | :--------- | :----------------- |
| `$key`   | `string` | Nome para a chave  |
| `$value` | `mixed`  | Valor para a chave |

> Coloque `session_start();` no index.php

### `hasSession(string $key): bool`

Verifica se a chave existe e o valor não está vazio

| Parâmetro | Tipo       | Descrição   |
| :--------- | :--------- | :------------ |
| `$key`   | `string` | Nome da chave |

### `removeSession(string $key): void`

Remove a chave e seu valor da sessão

| Parâmetro | Tipo       | Descrição   |
| :--------- | :--------- | :------------ |
| `$key`   | `string` | Nome da chave |

### `validate(array $rules, ?string $lang = 'pt-BR'): void`

Valida os dados recebidos na requisição

* Se `$lang` for fornecido, retorna os erros de validação no idioma definido
* Se `$lang` for null ( padrão ), retorna os erros em pt-BR

| Parâmetro | Tipo        | Descrição                    |
| :--------- | :---------- | :----------------------------- |
| `$rules` | `array`   | Dados da requisição          |
| `$lang`  | `?string` | Código do idioma ( opcional ) |

Mais detalhes em [Validator](validator.md)

### `http(): Curl`

Retorna uma nova instância da classe Curl, responsável por realizar requisições HTTP

Mais detalhes em [cURL](curl.md)
