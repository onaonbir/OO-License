<?php

namespace OnaOnbir\OOLicense\Exceptions;

class DeviceAlreadyActivatedException extends LicenseException
{
    protected $code = 'DEVICE_ALREADY_ACTIVATED';

    public function __construct(string $deviceId)
    {
        parent::__construct("Device '{$deviceId}' is already activated for this license key", 409);
    }
}
