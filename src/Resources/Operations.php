<?php

namespace Sma\InvoiceService\Resources;

class Operations extends BaseResource
{
    public function getById(string $id)
    {
        return $this->client->get('/operations/' . $id);
    }
}
