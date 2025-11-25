<?php

namespace OnaOnbir\OOLicense\Exceptions;

class LicenseExpiredException extends LicenseException
{
    protected $code = 'EXPIRED';

    public function __construct(string $message = 'License key has expired')
    {
        parent::__construct($message, 403);
    }
}
