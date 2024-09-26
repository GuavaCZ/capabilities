<?php

namespace Guava\Capabilities\Builders\Concerns;

use Guava\Capabilities\Builders\CapabilityConfiguration;
use Guava\Capabilities\Contracts\Capability as CapabilityContract;
use Guava\Capabilities\Models\Capability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait HasCapabilities
{
    private Collection $capabilities;

    public function capability(string | CapabilityContract | Capability $capability, string | Model | null $record = null): static
    {
        $this->capabilities ??= collect();
        $this->capabilities->push(CapabilityConfiguration::make($capability, $record));

        return $this;
    }

    public function capabilities(array | Collection $capabilities, string | Model | null $record = null): static
    {
        collect($capabilities)->each(fn ($capability) => $this->capability($capability, $record));

        return $this;
    }

    protected function getCapabilities(): Collection
    {
        return $this->capabilities ?? collect();
    }
}
