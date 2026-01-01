# Validator

Regras: required, string, integer, float, bool, email, confirmed, min e max

> Caso não houver erros na validação, um novo objeto será retornado em `$request->data()` com os dados [sanitizados](https://github.com/jonathansilva/nano/blob/main/src/Core/Security/Sanitize.php)

```php
final readonly class BookCreateAction
{
    public function handle($request, $response): void
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

            $request->validate($rules);

            // ...

            $response->json(201, ['message' => 'Cadastrado com sucesso']);
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
$request->validate($rules, 'en-US');
```
