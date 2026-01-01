# Database

A classe `Database` gerencia a conexão com o banco de dados utilizando *PDO*. A configuração é feita via [variáveis de ambiente](installation.md)

> A conexão é injetada automaticamente pelo [Container](container.md)

```php
final readonly class UserRepository
{
    public function __construct(private PDO $db) {}

    public function findByEmail(string $email): ?UserEntity
    {
        $stmt = $this->db->prepare('...');

        // ...
    }
}
```
