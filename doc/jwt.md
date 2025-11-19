# JWT

```php
use Nano\Core\Security\JWT;
```

### `encode(array $data): string`

Retorna um *access token* com os dados salvos no **payload**

```php
JWT::encode(["id" => $user->id]);
```

> Caso for salvar no Cookie, nomeie de `token`. Isso é necessário pois há funções na classe JWT que busca, verifica e remove o cookie pela chave *token*

### `decode(string $token): false|object`

Retorna os dados do payload, caso for válido

```php
JWT::decode($token);
```

### `assert(object $req, object $res, ?string $path): void`

Verifica se o token existe no Cookie ( Web ) ou cabeçalho Authorization ( API ) e é válido

> Use como middleware global

```php
JWT::assert($req, $res, '/login');
```

1 ) Se o token existir mas for inválido:

[ Web ] Redireciona para a página `/login`

[ API ] Retorna 'Invalid or expired token'

Caso for válido, o payload será enviado para o próximo middleware ou controller, podendo ser recuperado usando `$req->query()` ( [Role Middleware](routes.md#role-middleware) )

2 ) Se não existir, vai para o próximo middleware ou executa o controller

### `ensure(object $req, object $res, ?string $path): void`

> Use em rotas onde a autenticação é obrigatória ( [Ensure Middleware](routes.md#ensure-middleware) )

```php
JWT::ensure($req, $res, '/login');
```

Se não encontrar o token:

[ Web ] Redireciona para a página `/login`

[ API ] Retorna 'Authorization token not found in request'

---

O terceiro parâmetro em `JWT::assert` e `JWT::ensure` é ignorado em rotas de api
