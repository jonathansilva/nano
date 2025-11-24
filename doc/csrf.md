# CSRF

```php
use Nano\Core\Security\CSRF;
```

### `assert(object $req, object $res): void`

Gera um token a cada requisição ( GET ) e salva na sessão junto com o *path* da rota

> Use como middleware global

* Se o token do formulário e da sessão forem diferentes, redireciona para a mesma página

```php
CSRF::assert($req, $res);
```

Os formulários deverão ter um campo *hidden* chamado **csrf**

```html
<input type="hidden" name="csrf" value="{{ $csrf }}">
```

> Coloque `session_start();` no index.php

Veja também: [Form](form.md)
