# JWT

### `encode(array $data): object`

Gera um *access token* com parâmetros definidos por [variáveis de ambiente](installation.md). Os dados serão salvos no **payload** do token

<details>
<summary>Exemplo</summary>

```php
final readonly class AuthService
{
    public function generateToken(UserEntity $user): string
    {
        $payload = [
            'sub' => $user->id
        ];

        $token = JWT::encode($payload);

        return $token->access;
    }
}
```
</details>

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

### `decode(string $token): false|object`

Retorna os dados do payload, caso o token for válido ( [Exemplo](routes.md#assert-middleware) )
