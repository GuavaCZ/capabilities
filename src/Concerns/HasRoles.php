<?php

namespace Guava\Capabilities\Concerns;

use Guava\Capabilities\Builders\RoleBuilder;
use Guava\Capabilities\Contracts\Role as RoleContract;
use Guava\Capabilities\Exceptions\TenancyNotEnabledException;
use Guava\Capabilities\Facades\RoleManager;
use Guava\Capabilities\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

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

    public function assignRole(Role | array | Collection $role, ?Model $tenant = null, bool $detaching = false): static
    {
        if (! config('capabilities.tenancy', false) && $tenant) {
            throw TenancyNotEnabledException::make();
        }

        if (is_array($role) || $role instanceof Collection) {
            throw new \Exception('Not implemented');
            //            $capability = Capabilities::getRecords($capability, $model)
            //                ->pluck('id')
            //                ->all();
        }

        if (is_string($role) || $role instanceof RoleContract) {
            $role = RoleManager::getRecord($role, $tenant);
        }

        $pivot = [];

        if (config('capabilities.tenancy', false)) {
            $pivot[config('capabilities.tenant_column')] = $role->getAttribute(config('capabilities.tenant_column')) ?? $tenant?->getKey();
        }

        $this->assignedRoles()
            ->syncWithPivotValues(
                $role,
                $pivot,
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

    public function role(string | RoleContract $role): RoleBuilder
    {
        return RoleBuilder::of($role)
            ->assignee($this)
        ;
    }
}
