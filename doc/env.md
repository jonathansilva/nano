# Env

```php
use Nano\Core\Env;
```

A classe `Env` segue a seguinte ordem ao procurar pela chave

1. `getenv($key)` ( sistema )
2. `$_SERVER[$key]` ( servidor web )
3. `.env` ( arquivo .env )

O processo de busca é interrompido no momento em que a chave é encontrada

> Deve ser chamada pelo método estático `fetch`

```php
Env::fetch('JWT_KEY');
```
