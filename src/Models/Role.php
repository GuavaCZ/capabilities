<?php

namespace Guava\Capabilities\Models;

use Guava\Capabilities\Concerns\HasCapabilities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Foundation\Auth\User;

class Role extends Model
{
    use HasCapabilities;

    public $timestamps = false;

    public function getFillable(): array
    {
        return [
            'name',
            'title',
            config('capabilities.tenant_column', 'tenant_id'),
        ];
    }

    public function users(): MorphToMany
    {
        return $this
            ->morphedByMany(
                config('capabilities.user_class', User::class),
                'assignee',
                'assigned_roles',
                'assignee_id',
                'id',
            )
            ->when(
                config('capabilities.tenancy', false),
                fn (MorphToMany $query) => $query->withPivot([
                    config('capabilities.tenant_column', 'tenant_id'),
                ]),
            )
        ;
    }
}
