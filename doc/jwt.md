# JWT

```php
use Nano\Core\Security\JWT;
```

### `encode(array $data): object`

Gera um *access token* com parâmetros definidos por [variáveis de ambiente](installation.md)

Os dados serão salvos no **payload** do token

```php
JWT::encode(["sub" => $user->id])->access;
```

| Chave                   | Valor                  | Descrição            |
| :---------------------- | :--------------------- | :--------------------- |
| `JWT_KEY`             | ( qualquer coisa )           | Frase secreta          |
| `JWT_ACCESS_EXP_TYPE` | `minutes\|hours\|days` | Minutos, horas ou dias |
| `JWT_ACCESS_EXP_TIME` | ( maior que 0 )        | Tempo de expiração   |

> `JWT_KEY` é obrigatório

* Se `JWT_ACCESS_EXP_TYPE` não for definido, recebe o valor `hours`
* Se `JWT_ACCESS_EXP_TIME` não for definido:
  * Caso `JWT_ACCESS_EXP_TYPE` seja `hours` ou `days`, recebe o valor `1`
  * Caso `JWT_ACCESS_EXP_TYPE` seja `minutes`, recebe o valor `5`

---

Caso for salvar no Cookie, nomeie de `token`. Isso é necessário pois há funções na classe JWT que busca, verifica e remove o cookie pela chave *token*

### `decode(string $token): false|object`

Retorna os dados do payload, caso for válido

```php
JWT::decode($token);
```

### `assert(object $req, object $res, ?string $redirectTo): void`

Verifica se existe um token válido no Cookie ( Web ) ou cabeçalho Authorization ( API )

> Use como middleware global

```php
JWT::assert($req, $res, '/login');
```

1 ) Se o token existir mas for inválido:

[ Web ] Redireciona para a página `/login`

[ API ] Retorna 'Invalid or expired token'

Caso for válido, o payload será enviado para o próximo middleware ou controller, podendo ser recuperado usando `$req->query()` ( [Role Middleware](routes.md#role-middleware) )

2 ) Se não existir, vai para o próximo middleware ou executa o controller

### `ensure(object $req, object $res, ?string $redirectTo): void`

> Use em rotas onde a autenticação é obrigatória ( [Ensure Middleware](routes.md#ensure-middleware) )

```php
JWT::ensure($req, $res, '/login');
```

Se não encontrar o token:

[ Web ] Redireciona para a página `/login`

[ API ] Retorna 'Authorization token not found in request'

---

O terceiro parâmetro em `JWT::assert` e `JWT::ensure` é opcional e ignorado em rotas de api
