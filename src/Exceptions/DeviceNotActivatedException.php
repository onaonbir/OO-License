<?php

namespace OnaOnbir\OOLicense\Exceptions;

class DeviceNotActivatedException extends LicenseException
{
    protected $code = 'NOT_ACTIVATED';

    public function __construct(string $message = 'Device not activated. Please activate first.')
    {
        parent::__construct($message, 403);
    }
}
