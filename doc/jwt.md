# JWT

### `encode(array $data): object`

Retorna dois tokens ( access e refresh ) com parâmetros definidos por [variáveis de ambiente](installation.md)

**Obs:** Os dados são salvos no *payload* do access

<details>
<summary>Exemplo</summary>

```php
final readonly class AuthService implements AuthServiceInterface
{
    public function generateToken(UserEntity $user): object
    {
        $payload = [
            'sub' => $user->id
        ];

        return JWT::encode($payload);
    }
}
```
</details>

| Chave                   | Valor                  | Descrição            |
| :---------------------- | :--------------------- | :--------------------- |
| `JWT_KEY`             | ( qualquer coisa )           | Frase secreta          |
| `JWT_ACCESS_EXP_TYPE` | `minutes\|hours\|days` | Minutos, horas ou dias |
| `JWT_ACCESS_EXP_TIME` | ( maior que 0 )        | Tempo de expiração   |
| `JWT_REFRESH_EXP_TYPE` | `hours\|days`         | Horas ou dias          |
| `JWT_REFRESH_EXP_TIME` | ( maior que 0 )        | Tempo de expiração   |

> `JWT_KEY` é obrigatório

*Access token*

* Se `JWT_ACCESS_EXP_TYPE` não for definido, recebe o valor `hours`
* Se `JWT_ACCESS_EXP_TIME` não for definido:
  * Caso `JWT_ACCESS_EXP_TYPE` seja `hours` ou `days`, recebe o valor `1`
  * Caso `JWT_ACCESS_EXP_TYPE` seja `minutes`, recebe o valor `5`

---

*Refresh token*

* Se `JWT_REFRESH_EXP_TYPE` não for definido, recebe o valor `days`
* Se `JWT_REFRESH_EXP_TIME` não for definido:
  * Caso `JWT_REFRESH_EXP_TYPE` seja `hours`, recebe o valor `8`
  * Caso `JWT_REFRESH_EXP_TYPE` seja `days`, recebe o valor `7`

### `decode(string $token): false|object`

Retorna os dados do payload, caso o token for válido ( exemplo: [Assert Middleware](routes.md#assert-middleware) )
