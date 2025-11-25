<?php

namespace OnaOnbir\OOLicense\Exceptions;

class InvalidKeyException extends LicenseException
{
    protected $code = 'INVALID_KEY';

    public function __construct(string $message = 'Invalid license key')
    {
        parent::__construct($message, 404);
    }
}
