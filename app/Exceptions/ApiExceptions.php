<?php

namespace App\Exceptions;

use Exception;

class ApiException extends Exception
{
    protected $errorCode;

    public function __construct(string $message, int $code = 400, string $errorCode = 'API_ERROR')
    {
        parent::__construct($message, $code);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode()
    {
        return $this->errorCode;
    }
}