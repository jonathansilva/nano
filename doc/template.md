# Template engine

Desenvolvido por David Adams ( https://codeshack.io )

> Foram feitas pequenas alterações no código

```php
final readonly class ShowHomeAction
{
    public function handle($request, $response): void
    {
        $response->view('home', ['message' => 'Welcome to Nano!']);
    }
}
```

<details>
<summary>base.html</summary>

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
</details>

<details>
<summary>home.html</summary>

```html
{% extends base %}

{% block title %}Nano Framework{% endblock %}

{% block content %}
<h1>{{ $message }}</h1>
{% endblock %}
```
</details>

**Obs:** Salve os arquivos *.html* no diretório `views`

---

Exibindo os erros de validação ( [Validator](validator.md) )

```html
{% foreach ($errors as $value): %}
    <div>{{ $value }}</div>
{% endforeach; %}
```

Para saber mais sobre este template engine, [clique aqui](https://codeshack.io/lightweight-template-engine-php)

Veja também: [CLI](cli.md)
