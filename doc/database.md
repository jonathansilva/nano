# Database

```php
use Nano\Core\Database;
```

A classe `Database` retorna uma única instância de conexão *PDO* ( Singleton )

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
