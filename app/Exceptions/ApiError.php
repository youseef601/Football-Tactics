<?php

namespace App\Exceptions;

use Exception;

class ApiError extends Exception
{
    public $statusCode;
    public $status;
    public $isOperational;

    public function __construct($message, $statusCode)
    {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->status = str_starts_with((string) $statusCode, '4') ? 'fail' : 'error';
        $this->isOperational = true;
    }
}
