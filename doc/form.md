# Form

```php
use Nano\Core\View\Form;
```

Retorna os erros e o token CSRF salvos na **sessão**

```php
$form = Form::session($req);

$res->view('register', [
    'csrf' => $form->csrf,
    'errors' => $form->errors
]);
```

Veja também: [CSRF](csrf.md)
