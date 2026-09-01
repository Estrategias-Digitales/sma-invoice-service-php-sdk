<?php

namespace Sma\InvoiceService\Exception;

use Exception;

class ApiException extends Exception
{
    private int $statusCode;
    private mixed $responseBody;

    public function __construct(string $message, int $statusCode = 0, $responseBody = null)
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getResponseBody()
    {
        return $this->responseBody;
    }
}
