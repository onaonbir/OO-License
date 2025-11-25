<?php

namespace OnaOnbir\OOLicense\Exceptions;

class KeyInactiveException extends LicenseException
{
    protected $code = 'KEY_INACTIVE';

    public function __construct(string $message = 'License key is inactive')
    {
        parent::__construct($message, 403);
    }
}
