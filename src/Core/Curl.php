<?php

namespace Nano\Core;

use Nano\Core\Env;
use CurlHandle;
use Exception;

class Curl
{
    private CurlHandle $curl;

    public function __construct()
    {
        $this->curl = curl_init();

        if ($this->curl === false) {
            throw new Exception('Error initializing cURL');
        }
    }

    public function get(string $url, array $headers = []): string|false
    {
        return $this->options('GET', $url, $headers);
    }

    public function post(string $url, array $headers, string $body): string|false
    {
        return $this->options('POST', $url, $headers, $body);
    }

    public function put(string $url, array $headers, string $body): string|false
    {
        return $this->options('PUT', $url, $headers, $body);
    }

    public function patch(string $url, array $headers, string $body): string|false
    {
        return $this->options('PATCH', $url, $headers, $body);
    }

    public function delete(string $url, array $headers = []): string|false
    {
        return $this->options('DELETE', $url, $headers);
    }

    private function options(string $method, string $url, array $headers, ?string $body = null): string|false
    {
        if (count($headers) !== 0) {
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        }

        if ($body) {
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
        }

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, (bool) Env::fetch('CURL_SSL_VERIFYPEER'));
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 15);

        $data = curl_exec($this->curl);
        $code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        curl_close($this->curl);

        unset($this->curl);

        return ($code >= 200 && $code < 300) ? $data : false;
    }
}
