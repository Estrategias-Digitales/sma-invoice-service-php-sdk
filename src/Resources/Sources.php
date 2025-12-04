<?php

namespace Sma\InvoiceAdmin\Resources;

class Sources extends BaseResource
{
    public function login(string $id, string $password)
    {
        return $this->client->post('/sources/login', [
            'id' => $id,
            'password' => $password,
        ]);
    }

    public function all(array $query = [])
    {
        return $this->client->get('/sources', $query);
    }

    public function create(array $payload)
    {
        return $this->client->post('/sources', $payload);
    }

    public function getById(string $id)
    {
        return $this->client->get('/sources/' . $id);
    }

    public function update(string $id, array $payload)
    {
        return $this->client->put('/sources/' . $id, $payload);
    }

    public function delete(string $id)
    {
        return $this->client->delete('/sources/' . $id);
    }

    public function rotateSecret(string $id)
    {
        return $this->client->post('/sources/' . $id . '/rotate-secret', []);
    }
}
