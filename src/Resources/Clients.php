<?php

namespace Sma\InvoiceService\Resources;

class Clients extends BaseResource
{
    public function all(array $query = [])
    {
        return $this->client->get('/clients', $query);
    }

    public function create(array $payload)
    {
        return $this->client->post('/clients', $payload);
    }

    public function getById(string $id)
    {
        return $this->client->get('/clients/' . $id);
    }

    public function update(string $id, array $payload)
    {
        return $this->client->put('/clients/' . $id, $payload);
    }

    public function delete(string $id, array $query = [])
    {
        return $this->client->delete('/clients/' . $id, $query);
    }

    public function providerBalance(string $id)
    {
        return $this->client->get('/clients/' . $id . '/provider/balance');
    }

    public function setStampingProviderConfig(string $id, array $payload)
    {
        return $this->client->post('/clients/' . $id . '/stamping-provider-config', $payload);
    }

    public function updateStampingProviderConfig(string $id, array $payload)
    {
        return $this->client->put('/clients/' . $id . '/stamping-provider-config', $payload);
    }

    public function deleteStampingProviderConfig(string $id, array $query = [])
    {
        return $this->client->delete('/clients/' . $id . '/stamping-provider-config', $query);
    }
}
