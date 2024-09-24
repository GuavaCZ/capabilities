<?php

namespace Guava\Capabilities\Concerns;

use Guava\Capabilities\Auth\Capabilities;
use Guava\Capabilities\Models\Capability;
use Guava\Capabilities\Contracts\Capability as CapabilityContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasCapabilities
{
    public function assignedCapabilities(): MorphToMany
    {
        $pivot = [];

        if (config('capabilities.tenancy', false)) {
            $pivot[] = config('capabilities.tenant_column', 'tenant_id');
        }

        return $this->morphToMany(
            config('capabilities.capability_class', Capability::class),
            'assignee',
            'assigned_capabilities',
            'assignee_id',
            'capability_id',
        )
            ->withPivot($pivot)
        ;
    }

    public function hasCapability(CapabilityContract $capability, ?string $model = null, ?Model $tenant = null): bool
    {
        return $this->assignedCapabilities()
            ->wherePivot('organization_id', $tenant?->id)
            ->where('name', Capabilities::get($capability, $model))
            ->exists()
        ;
    }

    public function assignCapability(CapabilityContract $capability, ?string $model = null, ?Model $tenant = null, bool $detaching = false): static
    {
        $this->assignedCapabilities()->syncWithPivotValues(
            Capabilities::getRecord($capability, $model),
            [
                'organization_id' => $capability->organization_id ?? $tenant?->id,
            ],
            $detaching
        );

        return $this;
    }
}
