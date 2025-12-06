# Database

```php
use Nano\Core\Database;
```

A classe `Database` retorna uma **única instância** de conexão *PDO* ( Singleton ), com parâmetros definidos por [variáveis de ambiente](installation.md)

> Deve ser chamada pelo método estático `instance`

```php
class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }

    // ...
}
```
