<?php

namespace Guava\Capabilities\Concerns;

use Guava\Capabilities\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasRoles
{
    public function assignedRoles(): MorphToMany
    {
        $pivot = [];

        if (config('capabilities.tenancy', false)) {
            $pivot[] = config('capabilities.tenant_column', 'tenant_id');
        }

        return $this->morphToMany(
            config('capabilities.role_class', Role::class),
            'assignee',
            'assigned_roles',
            'assignee_id',
            'role_id',
        )
            ->withPivot($pivot)
        ;
    }

    public function assignRole(Model $role, ?Model $tenant = null, bool $detaching = false): static
    {
        $this->assignedRoles()
            ->syncWithPivotValues(
                $role,
                [
                    config('capabilities.tenant_column', 'tenant_id') => $role->getAttributeValue(config('capabilities.tenant-column', 'tenant_id')) ?? $tenant?->getKey(),
                ],
                $detaching
            )
        ;

        return $this;
    }

    public function hasCapability(string $name, ?Model $tenant = null): bool
    {
        return $this->assignedRoles()
            ->wherePivot(config('capabilities.tenant_column', 'tenant_id'), $tenant?->getKey())
            ->whereHas(
                'assignedCapabilities',
                fn (Builder $query) => $query
                    ->where('name', $name)
            )
            ->exists()
        ;
    }
}
