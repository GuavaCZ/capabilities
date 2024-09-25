<?php

namespace Guava\Capabilities\Builders\Concerns;

use Illuminate\Support\Collection;

trait HasCapabilities
{
    private ?Collection $capabilities = null;

    public function capabilities(array | Collection $capabilities): static
    {
        $this->capabilities = collect($capabilities);

        return $this;
    }

    protected function getCapabilities(): Collection
    {
        return $this->capabilities ?? collect();
    }
}
