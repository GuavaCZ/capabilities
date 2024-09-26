<?php

namespace Guava\Capabilities\Builders;

use Guava\Capabilities\Builders\Concerns\CanCreateIfNotExists;
use Guava\Capabilities\Builders\Concerns\HasAssignees;
use Guava\Capabilities\Builders\Concerns\HasCapabilities;
use Guava\Capabilities\Builders\Concerns\HasOwner;
use Guava\Capabilities\Builders\Concerns\HasTenant;
use Guava\Capabilities\Contracts\Capability as CapabilityContract;
use Guava\Capabilities\Facades\Capabilities;
use Guava\Capabilities\Models\Capability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CapabilityBuilder
{
    use CanCreateIfNotExists;
    use HasAssignees;
    use HasCapabilities;
    use HasOwner;
    use HasTenant;

    private function __construct(
        private CapabilityConfiguration $configuration,
    ) {}

    public function create(): Capability
    {
        return Capabilities::capability()->firstOrCreate($this->getColumns());
    }

    private function getColumns(): array
    {
        return [
            'name' => $this->configuration->getName(),
        ];
    }

    public static function of(string | CapabilityContract $capability, string | Model | null $record = null)
    {
        return new static(CapabilityConfiguration::make($capability, $record));
    }

    public static function fromConfiguration(CapabilityConfiguration $configuration): static
    {
        return new static($configuration);
    }
}
