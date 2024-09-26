<?php

namespace Guava\Capabilities\Builders;

use Guava\Capabilities\Builders\Concerns\CanCreateIfNotExists;
use Guava\Capabilities\Builders\Concerns\HasAssignees;
use Guava\Capabilities\Builders\Concerns\HasCapabilities;
use Guava\Capabilities\Builders\Concerns\HasOwner;
use Guava\Capabilities\Builders\Concerns\HasTenant;
use Guava\Capabilities\Contracts\Role as RoleContract;
use Guava\Capabilities\Facades\Capabilities;
use Guava\Capabilities\Models\Role;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleBuilder
{
    use CanCreateIfNotExists;
    use HasAssignees;
    use HasCapabilities;
    use HasOwner;
    use HasTenant;

    final private function __construct(
        private RoleConfiguration $configuration,
    ) {}

    public function create(): Role
    {
        /** @var Role $record */
        $record = Capabilities::role()->firstOrCreate($this->getColumns());

        $ids = $this->getCapabilities()
            ->map(
                fn (CapabilityConfiguration $configuration) => CapabilityBuilder::fromConfiguration($configuration)->create()
            )
            ->pluck('id')
        ;

        if (! empty($ids)) {
            $record->assignedCapabilities()->syncWithPivotValues(
                $ids,
                $this->getPivotColumns(),
                false,
            );
        }

        return $record;
    }

    public function find(): ?Role
    {
        return Capabilities::role()->firstWhere($this->getColumns());
    }

    public function assign(): Role
    {
        /** @var Role $record */
        $record = $this->shouldCreateIfNotExists()
            ? $this->create()
            : $this->find();

        if (! $record) {
            throw new ModelNotFoundException('Role could not be found');
        }

        $record->users()->syncWithPivotValues(
            $this->getAssignees()->pluck('id'),
            $this->getPivotColumns(),
            false,
        );

        return $record;
    }

    private function getColumns(): array
    {
        $columns = [
            'name' => $this->configuration->getName(),
        ];

        if (config('capabilities.tenancy', false)) {
            $columns[config('capabilities.tenant_column')] = $this->getOwner()?->getKey();
        }

        return $columns;
    }

    private function getPivotColumns(): array
    {
        $columns = [];

        if (config('capabilities.tenancy', false)) {
            $tenant = $this->getTenant() ?? $this->getOwner();
            $columns[config('capabilities.tenant_column', 'tenant_id')] = $tenant?->getKey();
        }

        return $columns;
    }

    public static function of(string | RoleContract $role): static
    {
        return new static(RoleConfiguration::make($role));
    }
}
