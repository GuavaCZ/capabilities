<?php

namespace Guava\Capabilities\Concerns;

use Guava\Capabilities\Builders\CapabilityConfiguration;
use Guava\Capabilities\Builders\RoleBuilder;
use Guava\Capabilities\Contracts\Capability;
use Guava\Capabilities\Contracts\Role as CapabilityContract;
use Guava\Capabilities\Exceptions\TenancyNotEnabledException;
use Guava\Capabilities\Facades\Capabilities;
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

        if (is_string($role) || $role instanceof CapabilityContract) {
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

    public function hasCapability(string |\Guava\Capabilities\Models\Capability| array |Collection $capability, ?Model $tenant = null): bool
    {
//        $configuration = CapabilityConfiguration::make($capability, $record);

        $tenantId = null;

        if (config('capabilities.tenancy', false)) {
            $tenantId = $tenant?->getKey() ?? Capabilities::getTenantId();
        }

        if (is_string($capability)) {
            $capability = \Guava\Capabilities\Models\Capability::where('name', $capability)->firstOrFail();
        }

        if (is_array($capability)) {
            $capability = collect($capability);
        }

        if ($capability instanceof Collection) {
            foreach ($capability as $cap) {
                if (!$this->hasCapability($cap, $tenant)) {
                    return false;
                }
            }
            return true;
        }

        return $this->assignedRoles()
            ->wherePivot(config('capabilities.tenant_column', 'tenant_id'), $tenantId)
            ->whereHas(
                'assignedCapabilities',
                fn (Builder $query) => $query
                ->where('name', $capability->name)
                    ->where('entity_type', $capability->entity_type)
//                ->where(fn (Builder $query) => $query
//                        ->whereNull('entity_id')
//                        ->orWhere('entity_id', $capability->entity_id)
//                )
//                    ->whereIn('id', $capability->pluck('id'))
//                    ->where('name', $configuration->getName())
//                    ->where('name', $capability)
            )
            ->exists()
        ;
    }

    public function role(string | CapabilityContract $role): RoleBuilder
    {
        return RoleBuilder::of($role)
            ->assignee($this)
        ;
    }
}
