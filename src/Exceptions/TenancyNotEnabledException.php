<?php

namespace Guava\Capabilities\Exceptions;

use Exception;

class TenancyNotEnabledException extends Exception
{
    public static function make(): static
    {
        return new static('Trying to access tenancy capabilities without tenancy enabled. Please enable tenancy in the config file.');
    }
}
