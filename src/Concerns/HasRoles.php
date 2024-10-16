<?php

namespace Guava\Capabilities\Concerns;

use Guava\Capabilities\Configurations\CapabilityConfiguration;
use Guava\Capabilities\Configurations\CustomRoleConfiguration;
use Guava\Capabilities\Configurations\RoleConfiguration;
use Guava\Capabilities\Exceptions\TenancyNotEnabledException;
use Guava\Capabilities\Models\Role;
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

    public function assignRole(Role | string | RoleConfiguration | array | Collection $roles, ?Model $tenant = null, bool $detaching = false): static
    {
        if (! config('capabilities.tenancy', false) && $tenant) {
            throw TenancyNotEnabledException::make();
        }

        if (is_array($roles)) {
            $roles = collect($roles);
        }

        if ($roles instanceof Collection) {
            foreach ($roles as $role) {
                $this->assignRole($role, $tenant);
            }

            return $this;
        }

        /** @var string|Role|RoleConfiguration $role */
        $role = $roles;
        if (is_string($role)) {
            $role = new CustomRoleConfiguration($role, tenant: $tenant);
        }

        if ($role instanceof RoleConfiguration) {
            $role = $role->find($tenant);
        }

        $pivot = [];

        if ($tenant) {
            $tenantId = $tenant->getKey();
            $pivot[config('capabilities.tenant_column')] = $tenantId;
        }

        $this->assignedRoles()
            ->syncWithPivotValues(
                [$role->getKey()],
                $pivot,
                $detaching
            )
        ;

        return $this;
    }

    public function hasCapability(CapabilityConfiguration | array | Collection $capability, ?Model $tenant = null): bool
    {
        return $this->hasRoleCapability($capability, $tenant);
    }

    public function hasRoleCapability(CapabilityConfiguration | array | Collection $capability, ?Model $tenant = null): bool
    {
        if (is_array($capability)) {
            $capability = collect($capability);
        }

        if ($capability instanceof Collection) {
            foreach ($capability as $cap) {
                if (! $this->hasRoleCapability($cap, $tenant)) {
                    return false;
                }
            }

            return true;
        }

        foreach ($this->assignedRoles as $role) {
            if (! $role->hasDirectCapability($capability, $tenant)) {
                return false;
            }
        }

        return $this->assignedRoles->count() > 0;
    }
}
