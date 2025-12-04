<?php

namespace Sma\InvoiceAdmin\Auth;

use Sma\InvoiceAdmin\Config;
use Sma\InvoiceAdmin\Exception\ApiException;
use Sma\InvoiceAdmin\Http\HttpClient;

/**
 * Handles bearer token acquisition and refresh.
 */
class TokenManager
{
    private Config $config;
    private HttpClient $http;

    private ?string $accessToken = null;
    private ?int $expiresAt = null; // epoch seconds

    public function __construct(Config $config, HttpClient $http)
    {
        $this->config = $config;
        $this->http = $http;
    }

    /**
     * Returns a valid bearer token, refreshing if needed.
     * @throws ApiException
     */
    public function getToken(): string
    {
        if ($this->isTokenValid()) {
            return $this->accessToken;
        }
        $this->login();
        if (!$this->accessToken) {
            throw new ApiException('Failed to obtain access token');
        }
        return $this->accessToken;
    }

    /**
     * Force re-login to obtain a fresh token.
     * @throws ApiException
     */
    public function refresh(): void
    {
        $this->login();
    }

    private function isTokenValid(): bool
    {
        if (!$this->accessToken) return false;
        if ($this->expiresAt === null) return true; // if unknown, assume valid until 401
        $now = time();
        // add a small safety window (30 seconds)
        return ($now + 30) < $this->expiresAt;
    }

    /**
     * Calls POST /sources/login with { id, password } and stores token.
     * @throws ApiException
     */
    private function login(): void
    {
        $id = $this->config->getSourceId();
        $pw = $this->config->getSourcePassword();
        if (!$id || !$pw) {
            throw new ApiException('Source credentials are required (id, password)');
        }
        $res = $this->http->request('POST', '/sources/login', null, [
            'id' => $id,
            'password' => $pw,
        ]);
        $body = $res['body'];
        if (!is_array($body)) {
            throw new ApiException('Unexpected login response');
        }
        $token = $this->extractToken($body);
        if (!$token) {
            throw new ApiException('Token not found in login response');
        }
        $this->accessToken = $token;
        $this->expiresAt = $this->inferExpiryFromJwt($token);
    }

    private function extractToken(array $body): ?string
    {
        // Try common fields
        foreach (['token', 'access_token', 'jwt'] as $k) {
            if (isset($body[$k]) && is_string($body[$k]) && $body[$k] !== '') {
                return $body[$k];
            }
        }
        // If response contains nested data
        if (isset($body['data']) && is_array($body['data'])) {
            return $this->extractToken($body['data']);
        }
        return null;
    }

    private function inferExpiryFromJwt(string $jwt): ?int
    {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) return null;
        $payloadB64 = $parts[1];
        $payloadJson = base64_decode(strtr($payloadB64, '-_', '+/'), true);
        if ($payloadJson === false) return null;
        $payload = json_decode($payloadJson, true);
        if (!is_array($payload)) return null;
        if (isset($payload['exp']) && is_int($payload['exp'])) {
            return $payload['exp'];
        }
        return null;
    }
}
