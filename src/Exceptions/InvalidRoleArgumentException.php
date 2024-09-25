<?php

namespace Guava\Capabilities\Exceptions;

use Exception;
use Guava\Capabilities\Contracts\Role;

class InvalidRoleArgumentException extends Exception
{
    public static function make(): static
    {
        return new static('The role needs to be an instance of "' . Role::class . '".');
    }
}
