<?php

namespace Guava\Capabilities\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Guava\Capabilities\Capabilities
 */
class RoleManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Guava\Capabilities\Managers\RoleManager::class;
    }
}
