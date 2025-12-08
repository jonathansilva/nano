# CLI

> composer.json

```json
"scripts": {
    "nano:clear": "./vendor/bin/nano clear",
    "nano:help": "./vendor/bin/nano help"
}
```

Execute o comando abaixo, caso esteja configurando após a instalação do framework

```bash
composer install
```

### `nano:cache`

Limpa o cache do Template engine

### `nano:help`

Lista os comandos disponíveis

---

**Composer**

> composer `<command>`

```bash
composer nano:clear
```

**Docker**

> docker compose exec `<service>` composer `<command>`

```bash
docker compose exec php composer nano:clear
```

ou

```bash
docker compose exec php sh
```

```bash
composer nano:clear
```
