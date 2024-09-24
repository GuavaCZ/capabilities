<?php

namespace Guava\Capabilities;

use Guava\Capabilities\Models\Capability;
use Guava\Capabilities\Models\Role;
use Illuminate\Database\Eloquent\Builder;

class Capabilities
{
    public function role(): Builder
    {
        return config('capabilities.role_class', Role::class)::query();
    }

    public function capability(): Builder
    {
        return config('capabilities.capability_class', Capability::class)::query();
    }

    public function tenant(): Builder
    {
        return config('capabilities.tenant_class')::query();
    }

    public function modelName(string $model): string
    {
        if (! class_exists($model)) {
            throw new \Exception("{$model} does not exist");
        }

        return str(class_basename($model))->kebab();
    }
}
