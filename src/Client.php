<?php

namespace Sma\InvoiceAdmin;

use Sma\InvoiceAdmin\Auth\TokenManager;
use Sma\InvoiceAdmin\Exception\ApiException;
use Sma\InvoiceAdmin\Http\HttpClient;

class Client
{
    private Config $config;
    private HttpClient $http;
    private TokenManager $tokens;

    public function __construct(?Config $config = null)
    {
        $this->config = $config ?? new Config();
        $this->http = new HttpClient($this->config->getBaseUrl());
        $this->tokens = new TokenManager($this->config, $this->http);
    }

    /**
     * Perform an HTTP request with bearer injection and 401 retry once.
     * @param string $method
     * @param string $path
     * @param array|null $query
     * @param array|null $body
     * @param array $headers
     * @return array{status:int,headers:array,body:mixed}
     * @throws ApiException
     */
    public function request(string $method, string $path, ?array $query = null, ?array $body = null, array $headers = []): array
    {
        $useAuth = $path !== '/health' && $path !== '/openapi.json' && strpos($path, '/sources/login') === false;
        $finalHeaders = $headers;
        if ($useAuth) {
            $token = $this->tokens->getToken();
            $finalHeaders['Authorization'] = 'Bearer ' . $token;
        }

        try {
            return $this->http->request($method, $path, $query, $body, $finalHeaders);
        } catch (ApiException $e) {
            if ($useAuth && $e->getStatusCode() === 401) {
                // refresh and retry once
                $this->tokens->refresh();
                $finalHeaders['Authorization'] = 'Bearer ' . $this->tokens->getToken();
                return $this->http->request($method, $path, $query, $body, $finalHeaders);
            }
            throw $e;
        }
    }

    // Convenience helpers
    public function get(string $path, array $query = [], array $headers = []) { return $this->request('GET', $path, $query, null, $headers); }
    public function post(string $path, array $body = [], array $headers = []) { return $this->request('POST', $path, null, $body, $headers); }
    public function put(string $path, array $body = [], array $headers = []) { return $this->request('PUT', $path, null, $body, $headers); }
    public function delete(string $path, array $headers = []) { return $this->request('DELETE', $path, null, null, $headers); }

    // Resource accessors
    public function health(): Resources\Health { return new Resources\Health($this); }
    public function sources(): Resources\Sources { return new Resources\Sources($this); }
    public function clients(): Resources\Clients { return new Resources\Clients($this); }
    public function invoices(): Resources\Invoices { return new Resources\Invoices($this); }
    public function operations(): Resources\Operations { return new Resources\Operations($this); }
}
