<?php

namespace Guava\Capabilities\Auth;

use Guava\Capabilities\Contracts\Capability as CapabilityContract;
use Illuminate\Database\Eloquent\Model;

class Capabilities
{
    public static function get(CapabilityContract $capability, ?string $model = null): ?string
    {
        // TODO: change "model" string parameter to ?string|Model. When a Model is passed, used it as a "related" model.
        $model = $model ?? $capability->model();

        return str($capability->value)
            ->when(
                $model,
                fn (\Stringable $stringable) => $stringable
                    ->prepend(\Guava\Capabilities\Facades\Capabilities::modelName($model), '.')
            )
        ;
    }

    public static function getRecord(CapabilityContract $capability, ?string $model = null): ?Model
    {
        return \Guava\Capabilities\Facades\Capabilities::capability()
            ->firstWhere('name', static::get($capability, $model))
        ;
    }
}
