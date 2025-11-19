# Validator

Regras: required, string, integer, float, bool, email, confirmed, min e max

> Caso não houver erros na validação, um novo objeto será retornado em `$req->data()` com os dados [sanitizados](https://github.com/jonathansilva/nano/blob/master/src/Core/Security/Sanitize.php)

```php
class Create
{
    public function handle($req, $res): void
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

            $res->json(201, ['message' => new Service()->register($data)]);
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

O uso do *confirmed* exige um novo input, onde o *name* precisa ter o sufixo `_confirmation`

```html
<div class="field">
    <label for="password_confirmation">Confirmar senha</label>

    <input type="password" id="password_confirmation" name="password_confirmation" spellcheck="false" autocomplete="off">
</div>
```

> Por padrão, as mensagens de erro estão em português. As opções aceitas são `pt-BR` e `en-US`

```php
$req->validate($rules, 'en-US');
```
