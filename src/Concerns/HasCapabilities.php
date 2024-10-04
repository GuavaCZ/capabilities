<?php

namespace Guava\Capabilities\Concerns;

use Guava\Capabilities\Builders\CapabilityBuilder;
use Guava\Capabilities\Builders\CapabilityConfiguration;
use Guava\Capabilities\Contracts\Capability as CapabilityContract;
use Guava\Capabilities\Exceptions\TenancyNotEnabledException;
use Guava\Capabilities\Facades\Capabilities;
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

    public function hasCapability(string | Capability | array |Collection $capability, ?Model $tenant = null): bool
    {
        //        $configuration = CapabilityConfiguration::make($capability, $record);

        $tenantId = null;

        if (config('capabilities.tenancy', false)) {
            $tenantId = $tenant?->getKey() ?? Capabilities::getTenantId();
        }

//        if ($capability instanceof CapabilityContract) {
//            $capability = $capability->getName();
//        }

        if (is_string($capability)) {
            $capability = Capability::where('name', $capability)->firstOrFail();
        }

        if ($capability instanceof Capability) {
            $capability = collect([$capability]);
        }

        if (is_array($capability)) {
            $capability = collect($capability);
        }

        return $this->assignedCapabilities()
            ->whereIn('id', $capability->pluck('id'))
            ->wherePivot(config('capabilities.tenant_column', 'tenant_id'), $tenantId)
//            ->when(
//                is_string($record),
//                fn ($query) => $query
//                    ->where('entity_type', app($record)->getMorphClass())
//                    ->where('entity_id', null),
//            )
//            ->when(
//                $record instanceof Model,
//                fn ($query) => $query
//                    ->where('entity_type', $record->getMorphClass())
//                    ->where('entity_id', $record->getKey()),
//            )
//            ->where('name', $capability)
            ->count() === $capability->count()
        ;
    }

    public function assignCapability(Capability | array | Collection $capabilities, ?Model $tenant = null, bool $detaching = false): static
    {
        if (! config('capabilities.tenancy', false) && $tenant) {
            throw TenancyNotEnabledException::make();
        }

        //        if (is_array($capability) || $capability instanceof Collection) {
        //            $capability = CapabilityManager::getRecords($capability, $model)
        //                ->pluck('id')
        //                ->all()
        //            ;
        //        }
        //
        //        if (is_string($capability) || $capability instanceof CapabilityContract) {
        //            $capability = CapabilityManager::getRecord($capability, $model);
        //        }

        if (is_array($capabilities)) {
            $capabilities = collect($capabilities);
        }

        if (is_string($capabilities) || $capabilities instanceof Capability) {
            $capabilities = collect([$capabilities]);
        }

        $pivot = [];

        // TODO: Maybe check if the tenant from the capability is the same as the tenant passed in (or null)
        // so that we cant try to assign a capability of tenant A to a user of tenant B
        if ($tenant) {
            $tenantId = $tenant?->getKey();

//            if ($capabilities instanceof Capability) {
//                $tenantId = $capabilities->getAttribute(config('capabilities.tenant_column')) ?? $tenantId;
//            }

            $pivot[config('capabilities.tenant_column')] = $tenantId;
        }

        $this->assignedCapabilities()->syncWithPivotValues(
            $capabilities->pluck('id'),
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
