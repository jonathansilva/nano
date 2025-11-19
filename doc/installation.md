**Requisitos**

* Composer
* PHP >= 8.4.0
* MySQL
* Nginx

Certifique-se de que as extensões abaixo, estejam habilitadas no *php.ini*

* extension=pdo_mysql
* extension=mbstring
* extension=curl

**Instalação**

`composer require jonathansilva/nano`

*Nginx*

```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/projeto/public;

    # Em 'root' coloque o caminho para o diretório /public do seu projeto

    index index.html index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;

        # Se estiver usando Docker, mantenha como 'php:9000' se 'php' for o nome do serviço
        # Se for uma instalação local, use '127.0.0.1:9000' ou o caminho do socket

        fastcgi_pass php:9000;
        # fastcgi_pass 127.0.0.1:9000;
        # fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
```

Crie os arquivos `.env.example` e `.gitignore` na raiz do seu projeto

.env.example

```
DB_HOST=localhost
DB_NAME=database
DB_USER=root
DB_PASS=password

JWT_KEY=textoSecretoAqui
JWT_EXP_IN_HOURS=8

COOKIE_EXP_IN_DAYS=1
COOKIE_DOMAIN=localhost
COOKIE_HTTPS=false
COOKIE_HTTPONLY=true
COOKIE_SAMESITE=Strict

CURL_SSL_VERIFYPEER=false

TEMPLATE_ENGINE_CACHE=false
```

Duplique o arquivo, renomeie para **.env** e altere os valores

> Em ambiente de produção, configure as variáveis de ambiente diretamente no servidor ou plataforma de hospedagem

.gitignore

```
.idea
.env
.vscode/
vendor/
cache/
composer.phar
```
