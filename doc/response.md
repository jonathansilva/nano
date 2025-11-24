# Response

### `view(string $file, ?array $data = []): ?Template`

Carrega e renderiza o template

* Se `$data` for fornecido, envia os dados para a view
* Se `$data` for null ( padrão ), envia um array vazio para a view

| Parâmetro | Tipo       | Descrição                            |
| :--------- | :--------- | :------------------------------------- |
| `$file`  | `string` | Nome da view                           |
| `$data`  | `?array` | Dados para exibir na view ( opcional ) |

Veja também: [Template engine](template.md)

### `redirect(string $path, ?int $code = 302): never`

Redireciona para outro *path / URI*

* Se `$code` for fornecido, redireciona com o código definido
* Se `$code` for null ( padrão ), redireciona com o código 302 ( redirecionamento temporário )

| Parâmetro | Tipo       | Descrição                         |
| :--------- | :--------- | :---------------------------------- |
| `$path`  | `string` | Caminho da URI                      |
| `$code`  | `?int`   | Código de status HTTP ( opcional ) |

### `json(int $code, mixed $data): void`

Imprime os dados em formato JSON

| Parâmetro | Tipo      | Descrição         |
| :--------- | :-------- | :------------------ |
| `$code`  | `int`   | Código da resposta |
| `$data`  | `mixed` | Dados da resposta   |
