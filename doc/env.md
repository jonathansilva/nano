# Env

```php
use Nano\Core\Env;
```

A classe `Env` lê o arquivo **.env** uma única vez ( Singleton ) e retorna o valor da variável ambiente

> Deve ser chamada pelo método estático `fetch`

```php
Env::fetch('JWT_KEY');
```
