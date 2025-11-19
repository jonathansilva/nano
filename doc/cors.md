# CORS

Coloque no index.php de sua API e faça as modificações necessárias

```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE');
header('Access-Control-Allow-Headers: Origin, Accept, Content-Type, Authorization, X-Requested-With');
```
