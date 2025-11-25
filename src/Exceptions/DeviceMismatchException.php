<?php

namespace OnaOnbir\OOLicense\Exceptions;

class DeviceMismatchException extends LicenseException
{
    protected $code = 'DEVICE_MISMATCH';

    public function __construct(string $message = 'Device information mismatch')
    {
        parent::__construct($message, 400);
    }
}
