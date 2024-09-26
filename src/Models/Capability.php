<?php

namespace Guava\Capabilities\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User;

class Capability extends Model
{
    public $timestamps = false;

    public function getFillable(): array
    {
        return [
            'name',
            'title',
        ];
    }

    public function users(): MorphToMany
    {
        $pivot = [];

        if (config('capabilities.tenancy', false)) {
            $pivot[] = config('capabilities.tenant_column', 'tenant_id');
        }

        return $this
            ->morphedByMany(
                config('capabilities.user_class', User::class),
                'assignee',
                'assigned_capabilities',
                'assignee_id',
                'capability_id',
            )
            ->withPivot($pivot)
        ;
    }

    public function roles(): MorphToMany
    {
        $pivot = [];

        if (config('capabilities.tenancy', false)) {
            $pivot[] = config('capabilities.tenant_column', 'tenant_id');
        }

        return $this
            ->morphedByMany(
                config('capabilities.role_class', Role::class),
                'assignee',
                'assigned_roles',
                'assignee_id',
                'role_id',
            )
            ->withPivot($pivot)
        ;
    }
}
