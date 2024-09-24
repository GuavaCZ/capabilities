<?php

namespace Guava\Capabilities\Auth;

abstract class CapabilityRegistration
{
    abstract public function capabilities(): array;

    abstract public function group(): string;

    public static function get(Capability ...$capabilities): string | array
    {
        $instance = app(static::class);

        if (is_array($capabilities)) {
            return collect($capabilities)
                ->map(fn (Capability $capability) => static::get($capability))
                ->all()
            ;
        }

        if (! in_array($capabilities, $instance->permissions())) {
            throw new \Exception;
        }

        return $instance->group() . '.' . $capabilities->value;
    }
}
