# Template engine

O template utilizado foi desenvolvido por David Adams ( https://codeshack.io )

> Foram feitas pequenas alterações no código original

base.html

```html
<!DOCTYPE html>

<html lang="pt-BR">
    <head>
        <title>{% yield title %}</title>

        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="stylesheet" href="/assets/style.css">
    </head>

    <body>
        {# comentário de teste #}
        <main>
            {% yield content %}
        </main>
    </body>
</html>
```

home.html

```html
{% extends base %}

{% block title %}Nano Framework{% endblock %}

{% block content %}
<h1>{{ $message }}</h1>
{% endblock %}
```

```php
<?php

namespace App\Callback\Page;

class Home
{
    public function handle($req, $res): void
    {
        $res->view('home', ['message' => 'Welcome to Nano!']);
    }
}
```

> Crie o diretório `views`

Exibindo os erros de validação ( [Validator](validator.md) )

```html
{% foreach ($errors as $value): %}
    <div>{{ $value }}</div>
{% endforeach; %}
```

Para saber mais sobre este template engine, [clique aqui](https://codeshack.io/lightweight-template-engine-php)
