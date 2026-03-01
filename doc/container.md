# Container

> Injeção de dependência

A classe `Container` utiliza **Reflection** para analisar o `__construct()` das classes e gerenciar a injeção de dependências automaticamente

Por padrão, as instâncias são tratadas como *Singletons*, sendo reutilizadas durante toda a requisição

```php
final readonly class StoreUserAction
{
    public function __construct(
        private AuthServiceInterface $authService,
        private UserServiceInterface $userService,
        private StoreUserMapperInterface $mapper
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

**Testes Unitários e Mocks**

Use o método `set()` para injetar instâncias específicas no Container. Isso permite substituir serviços reais por Mocks ( objetos simulados ), isolando a classe que está sendo testada de dependências externas como bancos de dados ou APIs

<details>
<summary>Exemplo</summary>

```php
final class LoginActionTest extends TestCase
{
    private Container $container;
    private $requestMock;
    private $responseMock;
    private $mapperMock;
    private $loginServiceMock;
    private $authServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();

        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->mapperMock = $this->createMock(LoginMapperInterface::class);
        $this->loginServiceMock = $this->createMock(LoginServiceInterface::class);
        $this->authServiceMock = $this->createMock(AuthServiceInterface::class);

        $this->container->set(LoginMapperInterface::class, $this->mapperMock);
        $this->container->set(LoginServiceInterface::class, $this->loginServiceMock);
        $this->container->set(AuthServiceInterface::class, $this->authServiceMock);
    }

    #[Test]
    public function deve_realizar_o_login_com_sucesso_e_redirecionar_para_o_perfil(): void
    {
        $fakeObj = (object) ['email' => 'admin@admin.com', 'password' => '12345678'];

        $fakeDto = new LoginDTO(email: 'admin@admin.com', password: '12345678');

        $fakeUser = new UserEntity(
            id: 1,
            name: 'Admin',
            email: 'admin@admin.com',
            password: 'hash'
        );

        $fakeToken = (object) ['access' => 'jwt_fake_token'];

        $this->requestMock->expects($this->once())
            ->method('validate');

        $this->requestMock->method('data')
            ->willReturn($fakeObj);

        $this->mapperMock->expects($this->once())
            ->method('mapFromRequest')
            ->with($fakeObj)
            ->willReturn($fakeDto);

        $this->loginServiceMock->expects($this->once())
            ->method('authenticate')
            ->with($fakeDto)
            ->willReturn($fakeUser);

        $this->authServiceMock->expects($this->once())
            ->method('generateToken')
            ->with($fakeUser)
            ->willReturn($fakeToken);

        $this->requestMock->expects($this->once())
            ->method('setCookie')
            ->with('token', 'jwt_fake_token');

        $this->responseMock->expects($this->once())
            ->method('redirect')
            ->with('/me');

        $action = $this->container->resolve(LoginAction::class);
        $action->handle($this->requestMock, $this->responseMock);
    }

    #[Test]
    public function deve_redirecionar_para_o_login_quando_a_autenticacao_falhar(): void
    {
        $fakeObj = (object) ['email' => 'admin@admin.com', 'password' => '12345678'];

        $fakeDto = new LoginDTO(email: 'admin@admin.com', password: '12345678');

        $this->requestMock->expects($this->once())
            ->method('data')
            ->willReturn($fakeObj);

        $this->mapperMock->expects($this->once())
            ->method('mapFromRequest')
            ->with($fakeObj)
            ->willReturn($fakeDto);

        $this->loginServiceMock->expects($this->once())
            ->method('authenticate')
            ->with($fakeDto)
            ->willThrowException(new DomainException('E-mail ou senha inválido'));

        $this->authServiceMock->expects($this->never())
            ->method('generateToken');

        $this->requestMock->expects($this->once())
            ->method('setSession')
            ->with('errors', $this->isArray());

        $this->responseMock->expects($this->once())
            ->method('redirect')
            ->with('/login');

        $action = $this->container->resolve(LoginAction::class);
        $action->handle($this->requestMock, $this->responseMock);
    }
}
```
</details>
