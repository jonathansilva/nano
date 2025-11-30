# CSRF

```php
use Nano\Core\Security\CSRF;
```

### `assert(object $req, object $res): void`

Gera um token a cada requisição ( GET ) e salva na sessão junto com o *path* da rota

* Se o token da requisição ( formulário ou AJAX ) e da sessão forem diferentes, redireciona para a mesma página

```php
CSRF::assert($req, $res);
```

> Coloque `session_start();` no index.php

**Formulário**

Crie um campo *hidden* chamado **csrf**

```html
<input type="hidden" name="csrf" value="{{ $csrf }}">
```

**AJAX ( axios / fetch )**

> Por convenção, o token é exibido em uma *meta* tag chamada **csrf-token**

```html
<meta name="csrf-token" content="{{ $csrf }}">
```

O token será enviado pelo cabeçalho `X-CSRF-Token`

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

Veja também: [Form](form.md)
