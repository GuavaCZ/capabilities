<?php

namespace Guava\Capabilities\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Guava\Capabilities\Capabilities
 */
class CapabilityManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Guava\Capabilities\Managers\CapabilityManager::class;
    }
}
