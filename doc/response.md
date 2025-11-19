# Response

### `view(string $file, ?array $arr = []): ?Template`

Carrega e renderiza o template

* Se `$arr` for fornecido, envia os dados para a view
* Se `$arr` for null ( padrão ), envia um array vazio para a view

| Parâmetro | Tipo        | Descrição                                               |
| :--------- | :---------- | :-------------------------------------------------------- |
| `$file`   | `string` | Nome da view |
| `$arr`   | `?array` | Dados para exibir na view ( opcional ) |

Veja também: [Template engine](template.md)

### `redirect(string $path, ?int $code = 302): never`

Redireciona para outro *path / URI*

* Se `$code` for fornecido, redireciona com o código definido
* Se `$code` for null ( padrão ), redireciona com o código 302 ( redirecionamento temporário )

| Parâmetro | Tipo        | Descrição                                               |
| :--------- | :---------- | :-------------------------------------------------------- |
| `$path`   | `string` | Caminho da URI |
| `$code`   | `?int` | Código de status HTTP ( opcional ) |

### `json(int $status, mixed $data): void`

Imprime os dados em formato JSON

| Parâmetro | Tipo        | Descrição                                               |
| :--------- | :---------- | :-------------------------------------------------------- |
| `$status`   | `int` | Código da resposta |
| `$data`   | `mixed` | Dados da resposta |
