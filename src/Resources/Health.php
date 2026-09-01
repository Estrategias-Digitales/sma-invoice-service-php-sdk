<?php

namespace Sma\InvoiceService\Resources;

class Health extends BaseResource
{
    public function get(string $path = "", array $query = [])
    {
        return $this->client->get('/health');
    }
}
