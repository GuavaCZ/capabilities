<?php

namespace Guava\Capabilities\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Guava\Capabilities\Capabilities
 */
class Capabilities extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Guava\Capabilities\Capabilities::class;
    }
}
