# Database

```php
use Nano\Core\Database;
```

A classe `Database` retorna uma única instância de conexão *PDO* ( Singleton ) e deve ser chamada pelo método estático `instance`

```php
class Create
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::instance();
    }
}
```

*Executando query*

```php
public function findUserById(int $id): array|bool
{
    $stmt = $this->db->prepare("SELECT id, name FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);

    return $stmt->fetch();
}
```
