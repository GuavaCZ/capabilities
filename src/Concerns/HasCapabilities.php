<?php

namespace Guava\Capabilities\Concerns;

use Guava\Capabilities\Builders\CapabilityBuilder;
use Guava\Capabilities\Contracts\Capability as CapabilityContract;
use Guava\Capabilities\Exceptions\TenancyNotEnabledException;
use Guava\Capabilities\Facades\CapabilityManager;
use Guava\Capabilities\Models\Capability;
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
            ->where('name', CapabilityManager::get($capability, $model))
            ->exists()
        ;
    }

    public function assignCapability(Capability | array | Collection $capability, null | string | Model $model = null, ?Model $tenant = null, bool $detaching = false): static
    {
        if (! config('capabilities.tenancy', false) && $tenant) {
            throw TenancyNotEnabledException::make();
        }

        if (is_array($capability) || $capability instanceof Collection) {
            $capability = CapabilityManager::getRecords($capability, $model)
                ->pluck('id')
                ->all()
            ;
        }

        if (is_string($capability) || $capability instanceof CapabilityContract) {
            $capability = CapabilityManager::getRecord($capability, $model);
        }

        $pivot = [];

        if ($tenant) {
            $pivot[config('capabilities.tenant_column')] = $capability->getAttribute(config('capabilities.tenant_column')) ?? $tenant->getKey();
        }

        $this->assignedCapabilities()->syncWithPivotValues(
            $capability,
            $pivot,
            $detaching
        );

        return $this;
    }

    public function capability(string | CapabilityContract $capability, string | Model | null $model = null): CapabilityBuilder
    {
        return CapabilityBuilder::of($capability, $model)
            ->assignee($this)
        ;
    }
}
