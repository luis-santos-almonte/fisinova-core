<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected string $errorCode;
    protected ?array $details;

    public function __construct(string $message, int $code = 400, string $errorCode = 'API_ERROR', ?array $details = null)
    {
        parent::__construct($message, $code);
        $this->errorCode = $errorCode;
        $this->details = $details;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }
}
