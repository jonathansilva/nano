# Error handler

```php
use Nano\Core\Error;
```

### `parse(string|object $message): array|object`

* Se `$message` for uma string JSON válida, faz o *parsing* e retorna o objeto correspondente
* Caso contrário, retorna um array contendo `$message`

```php
try {
    // ...
} catch (Exception $e) {
    $req->setSession('errors', Error::parse($e->getMessage()));
}
```

### `throwJsonException(int $statusCode, string|object $message): never`

Imprime o erro em formato JSON

```php
try {
    // ...
} catch (Exception $e) {
    Error::throwJsonException(500, $e->getMessage());
}
```
