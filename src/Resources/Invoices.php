<?php

namespace Sma\InvoiceAdmin\Resources;

class Invoices extends BaseResource
{
    public function all(array $query = [])
    {
        return $this->client->get('/invoices', $query);
    }

    public function create(array $payload)
    {
        return $this->client->post('/invoices', $payload);
    }

    public function getById(string $id)
    {
        return $this->client->get('/invoices/' . $id);
    }

    public function update(string $id, array $payload)
    {
        return $this->client->put('/invoices/' . $id, $payload);
    }

    public function delete(string $id, array $query = [])
    {
        return $this->client->delete('/invoices/' . $id, $query);
    }

    public function cancel(string $id, array $query = [])
    {
        return $this->delete($id, $query);
    }

    public function seal(string $id, array $payload = [])
    {
        return $this->client->post('/invoices/' . $id . '/seal', $payload);
    }

    public function stamp(string $id, array $payload = [])
    {
        return $this->client->post('/invoices/' . $id . '/stamp', $payload);
    }

    public function batch(array $payload)
    {
        return $this->client->post('/invoices/batch', $payload);
    }

    public function batchStamp(array $payload)
    {
        return $this->client->post('/invoices/batch-stamp', $payload);
    }
}
