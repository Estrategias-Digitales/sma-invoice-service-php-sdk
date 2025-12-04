<?php

namespace Sma\InvoiceAdmin;

class Config
{
    public const DEFAULT_BASE_URL = 'https://api-y744ss7wmq-uc.a.run.app';

    private string $baseUrl;
    private ?string $sourceId;
    private ?string $sourcePassword;

    public function __construct(
        ?string $baseUrl = null,
        ?string $sourceId = null,
        ?string $sourcePassword = null
    ) {
        $this->baseUrl = rtrim($baseUrl ?? self::DEFAULT_BASE_URL, '/');
        $this->sourceId = $sourceId;
        $this->sourcePassword = $sourcePassword;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getSourceId(): ?string
    {
        return $this->sourceId;
    }

    public function getSourcePassword(): ?string
    {
        return $this->sourcePassword;
    }
}
