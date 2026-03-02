# Database

Gerencia as conexões com o banco de dados via **PDO**. A configuração é definida através de [variáveis de ambiente](installation.md)

**Injeção de dependência**

Para manter o código testável e desacoplado, a conexão não deve ser instanciada manualmente. O Container resolve e injeta a instância de PDO automaticamente nos construtores, utilizando internamente o método `Database::instance()`

```php
final readonly class UserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $db) {}

    public function findByEmail(string $email): ?UserEntity
    {
        $stmt = $this->db->prepare('...');

        // ...
    }
}
```

Veja também: [Container](container.md)
