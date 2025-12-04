<?php

namespace Sma\InvoiceAdmin\Resources;

use Sma\InvoiceAdmin\Client;

abstract class BaseResource
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    protected function get(string $path, array $query = []) { return $this->client->get($path, $query); }
    protected function post(string $path, array $body = []) { return $this->client->post($path, $body); }
    protected function put(string $path, array $body = []) { return $this->client->put($path, $body); }
    protected function delete(string $path) { return $this->client->delete($path); }
}
