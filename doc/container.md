# Container

> Injeção de dependência

A classe `Container` utiliza **Reflection** para analisar o `__construct()` das classes e gerenciar a injeção de dependências automaticamente

Por padrão, as instâncias são tratadas como *Singletons*, sendo reutilizadas durante toda a requisição

```php
final readonly class StoreUserAction
{
    public function __construct(
        private AuthService $authService,
        private UserService $userService
    ) {}
}
```

**Obs:** Para tipos primitivos como `string` ou `int`, forneça um valor padrão no construtor ou realize o mapeamento manual ( set )

```php
final readonly class EmailService
{
    public function __construct(
        private string $apiKey
    ) {}
}
```

> public/index.php

```php
$app = Nano\Core\Router\Instance::create();

$app->set('apiKey', Env::fetch('API_KEY'));
```

**Binding**

Como interfaces não podem ser instanciadas, é necessário usar o método `bind()` para informar ao Container qual classe concreta será utilizada

```php
$app->bind(
    App\Repositories\UserRepositoryInterface::class,
    App\Repositories\UserRepository::class
);
```

**Testes unitários e Mocks**

O método `set()` permite forçar uma instância específica dentro do Container. Ideal para substituir serviços reais por *mocks* durante os testes

<details>
<summary>Exemplo</summary>

```php
final class LoginActionTest extends TestCase
{
    // ...

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Nano\Core\Container();

        $this->authServiceMock = $this->createMock(AuthService::class);
        $this->loginServiceMock = $this->createMock(LoginService::class);
        $this->mapperMock = $this->createMock(LoginMapper::class);

        $this->requestMock = $this->getMockBuilder(stdClass::class)
            ->addMethods(['validate', 'data', 'setCookie', 'setSession'])
            ->getMock();

        $this->responseMock = $this->getMockBuilder(stdClass::class)
            ->addMethods(['redirect'])
            ->getMock();

        $this->container->set(AuthService::class, $this->authServiceMock);
        $this->container->set(LoginService::class, $this->loginServiceMock);
        $this->container->set(LoginMapper::class, $this->mapperMock);
    }

    // ...
}
```
</details>
