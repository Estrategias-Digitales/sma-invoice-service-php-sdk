<?php

namespace Sma\InvoiceAdmin\Http;

use Sma\InvoiceAdmin\Exception\ApiException;

class HttpClient
{
    private string $baseUrl;
    private array $defaultHeaders = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json'
    ];

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * @param string $method
     * @param string $path Path starting with '/'
     * @param array|null $query
     * @param array|null $body
     * @param array $headers
     * @return array{status:int,headers:array,body:mixed}
     * @throws ApiException
     */
    public function request(string $method, string $path, ?array $query = null, ?array $body = null, array $headers = []): array
    {
        $url = $this->baseUrl . '/v1' . $path;
        if (!empty($query)) {
            $qs = http_build_query($query);
            if ($qs) {
                $url .= (strpos($url, '?') === false ? '?' : '&') . $qs;
            }
        }

        $ch = curl_init($url);
        if ($ch === false) {
            throw new ApiException('Failed to initialize cURL');
        }

        $method = strtoupper($method);
        switch ($method) {
            case 'GET':
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                break;
            default:
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $finalHeaders = array_merge($this->defaultHeaders, $headers);
        $headerLines = [];
        foreach ($finalHeaders as $k => $v) {
            $headerLines[] = $k . ': ' . $v;
        }

        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 60,
        ]);

        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            $code = curl_errno($ch);
            curl_close($ch);
            throw new ApiException('cURL error: ' . $err, $code);
        }

        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $rawHeaders = substr($raw, 0, $headerSize);
        $rawBody = substr($raw, $headerSize);
        $parsedHeaders = $this->parseHeaders($rawHeaders);

        $decoded = null;
        if ($rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $decoded = $rawBody; // not JSON
            }
        }

        if ($status >= 400) {
            throw new ApiException('HTTP ' . $status, (int)$status, $decoded);
        }

        return [
            'status' => (int)$status,
            'headers' => $parsedHeaders,
            'body' => $decoded,
        ];
    }

    private function parseHeaders(string $rawHeaders): array
    {
        $headers = [];
        $lines = preg_split('/\r?\n/', trim($rawHeaders));
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                [$k, $v] = explode(':', $line, 2);
                $headers[trim($k)] = trim($v);
            }
        }
        return $headers;
    }
}
