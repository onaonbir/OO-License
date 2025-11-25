<?php

namespace OnaOnbir\OOLicense\Exceptions;

class MaxDevicesReachedException extends LicenseException
{
    protected $code = 'MAX_DEVICES_REACHED';

    public function __construct(int $maxDevices)
    {
        parent::__construct("Maximum device limit ({$maxDevices}) reached", 403);
    }
}
