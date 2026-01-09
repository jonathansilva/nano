# CSRF

Gera um token a cada requisição GET e salva na sessão junto com o *path* da rota

Se o token da requisição POST, PUT, PATCH ou DELETE for diferente da sessão, redireciona para a mesma página

<details>
<summary>Exemplo</summary>

```php
final readonly class CSRF
{
    public function handle($request, $response): void
	{
        if ($request->method() == 'GET') {
            $request->setSession('path', $request->path());
            $request->setSession('csrf', bin2hex(random_bytes(32)));

            return;
        }

        $token = trim($request->data('csrf') ?? '');

        if (empty($token)) {
            if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = trim($_SERVER['HTTP_X_CSRF_TOKEN']);
            }
        }

        if (empty($token)) {
            error_log('[CSRF] The token was not found or empty in the form or header');

            $response->redirect($request->session('path'));
        }

        if (!hash_equals($request->session('csrf'), $token)) {
            error_log('[CSRF] Invalid token in the form or header');

            $response->redirect($request->session('path'));
        }
    }
}
```

**Obs:** Por usar `$_SESSION` internamente na classe `Request`, é necessário que coloque `session_start();` no index.php

</details>

> Use como middleware

**Formulário**

```html
<input type="hidden" name="csrf" value="{{ $csrf }}">
```

<details>
<summary>Exemplo</summary>

```php
final readonly class ShowLoginAction
{
    public function handle($request, $response): void
    {
        $response->view('login', [
            'csrf' => $request->session('csrf')
        ]);
    }
}
```

</details>

**AJAX ( axios / fetch )**

Por convenção, o token é exibido em uma *meta tag* chamada **csrf-token** e enviado pelo cabeçalho `X-CSRF-Token`

<details>
<summary>Exemplo</summary>

```html
<meta name="csrf-token" content="{{ $csrf }}">
```

```javascript
const form = document.querySelector('form');
const button = form.querySelector('button');

button.addEventListener('click', async event => {
    event.preventDefault();

    const body = new FormData(form);

    const { status, url } = await fetch('/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(Object.fromEntries(body))
    });

    if (status === 200) {
        window.location = url;
    }
});
```

</details>
