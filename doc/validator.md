# Validator

Regras: required, string, integer, float, bool, email, confirmed, min e max

Caso não houver erros na validação, um novo objeto será retornado em `$request->data()` com os dados [sanitizados](https://github.com/jonathansilva/nano/blob/main/src/Core/Security/Sanitize.php)

```php
final readonly class StoreBookAction
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

**Field confirmation**

Verifica se o valor de um campo é idêntico ao de um segundo campo de confirmação

```php
$rules = [
    'password' => 'required|string|min:8|confirmed'
];
```

O uso do *confirmed* exige um novo input, onde o *name* precisa ter o sufixo `_confirmation`. O validador buscará automaticamente pelo input de confirmação no corpo da requisição

<details>
<summary>Exemplo</summary>

```html
<div class="field">
    <label for="password">Senha</label>

    <input type="password" id="password" name="password" spellcheck="false" autocomplete="off">
</div>

<div class="field">
    <label for="password_confirmation">Confirmar senha</label>

    <input type="password" id="password_confirmation" name="password_confirmation" spellcheck="false" autocomplete="off">
</div>
```
</details>

**Custom attributes**

Substitui os nomes técnicos das chaves ( exemplo: fullname ) por nomes amigáveis ( exemplo: Nome completo ) nas mensagens de erro

<details>
<summary>Exemplo</summary>

```php
$rules = [
    'fullname' => 'required|string',
    'email' => 'required|email',
    'password' => 'required|string|min:8|confirmed'
];

$customAttributes = [
    'fullname' => 'Nome completo',
    'email' => 'E-mail',
    'password' => 'Senha'
];

$request->validate($rules, $customAttributes);
```

"O campo **fullname** é obrigatório" ❌

"O campo **Nome completo** é obrigatório" ✔️
</details>

**Obs:** Por padrão, as mensagens de erro estão em português. As opções aceitas são `pt-BR` e `en-US`

```php
$request->validate($rules, [], 'en-US');
```
