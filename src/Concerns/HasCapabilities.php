<?php

namespace Guava\Capabilities\Concerns;

use Guava\Capabilities\Configurations\CapabilityConfiguration;
use Guava\Capabilities\Exceptions\TenancyNotEnabledException;
use Guava\Capabilities\Facades\Capabilities;
use Guava\Capabilities\Models\Capability;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

trait HasCapabilities
{
    public function assignedCapabilities(): MorphToMany
    {
        $pivot = [];

        if (config('capabilities.tenancy', false)) {
            $pivot[] = config('capabilities.tenant_column', 'tenant_id');
        }

        return $this->morphToMany(
            Capability::class,
            //            config('capabilities.capability_class', Capability::class),
            'assignee',
            'assigned_capabilities',
            'assignee_id',
            'capability_id',
        )
            ->withPivot($pivot)
        ;
    }

    public function hasCapability(CapabilityConfiguration | array | Collection $capability, ?Model $tenant = null): bool
    {
        return $this->hasDirectCapability($capability, $tenant);
    }

    public function hasDirectCapability(CapabilityConfiguration | array | Collection $capability, ?Model $tenant = null): bool
    {
        $tenantId = null;

        if ($tenant) {
            if (config('capabilities.tenancy', false)) {
                $tenantId = $tenant?->getKey() ?? Capabilities::getTenantId();
            } else {
                throw TenancyNotEnabledException::make();
            }
        }

        if (is_array($capability)) {
            $capability = collect($capability);
        }

        if ($capability instanceof Collection) {
            foreach ($capability as $cap) {
                if (! $this->hasDirectCapability($cap, $tenant)) {
                    return false;
                }
            }

            return true;
        }

        /** @var CapabilityConfiguration $cap */
        $cap = $capability;

        return $this->assignedCapabilities()
            ->where('name', $cap->getName())
            ->where('entity_type', $cap->getEntityType())
            ->when(
                ! is_null($cap->getEntityKey()),
                fn (Builder $query) => $query
                    ->where(
                        fn (Builder $query) => $query
                            ->where('entity_id', $cap->getEntityKey())
                            ->orWhere('entity_id', null)
                    ),
                fn (Builder $query) => $query->where('entity_id', null)
            )
            ->wherePivot(config('capabilities.tenant_column', 'tenant_id'), $tenantId)
            ->exists()
        ;
    }

    public function assignCapability(Capability | CapabilityConfiguration | array | Collection $capabilities, ?Model $tenant = null, bool $detaching = false): static
    {
        if (! config('capabilities.tenancy', false) && $tenant) {
            throw TenancyNotEnabledException::make();
        }

        if (is_array($capabilities)) {
            $capabilities = collect($capabilities);
        }

        if ($capabilities instanceof Collection) {
            if ($detaching) {
                $this->assignedCapabilities()->detach();
            }
            foreach ($capabilities as $capability) {
                $this->assignCapability($capability, $tenant, $detaching);
            }

            return $this;
        }

        /** @var Capability|CapabilityConfiguration $capability */
        $capability = $capabilities;

        $pivot = [];

        // TODO: Maybe check if the tenant from the capability is the same as the tenant passed in (or null)
        // so that we cant try to assign a capability of tenant A to a user of tenant B
        if ($tenant) {
            $tenantId = $tenant->getKey();

            $pivot[config('capabilities.tenant_column')] = $tenantId;
        }

        if ($capability instanceof Capability) {
            $this->assignedCapabilities()
                ->syncWithPivotValues(
                    [
                        $capability->getKey(),
                    ],
                    $pivot,
                    $detaching
                )
            ;
        } else {
            $this->assignedCapabilities()->firstOrCreate([
                //                'assignee_id' => $this->getKey(),
                //                'assignee_type' => $this->getMorphClass(),
                'name' => $capability->getName(),
                'entity_type' => $capability->getEntityType(),
                'entity_id' => $capability->getEntityKey(),
            ], joining: [
                ...$pivot,
                'assignee_id' => $this->getKey(),
                'assignee_type' => $this->getMorphClass(),
            ]);
        }

        return $this;
    }
}
