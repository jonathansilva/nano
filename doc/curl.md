# cURL

Verbos: GET, POST, PUT, PATCH e DELETE

```php
try {
    $url = 'https://...';

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer TOKEN'
    ];

    $body = json_encode(array(...));

    $data = $req->http()->post($url, $headers, $body);

    if (!$data) {
        throw new Exception('Erro ao realizar requisição');
    }

    $info = json_decode($data);

    $res->json($info->status, ['message' => $info->message]);
} catch (Exception $e) {
    Error::throwJsonException(500, $e->getMessage());
}
```
