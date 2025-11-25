<?php

namespace OnaOnbir\OOLicense\Exceptions;

use Exception;

class LicenseException extends Exception
{
    protected $code = 'LICENSE_ERROR';

    public function __construct(string $message = 'License error occurred', int $statusCode = 400)
    {
        parent::__construct($message, $statusCode);
    }

    public function getErrorCode(): string
    {
        return $this->code;
    }
}
