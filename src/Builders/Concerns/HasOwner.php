<?php

namespace Guava\Capabilities\Builders\Concerns;

use Guava\Capabilities\Exceptions\TenancyNotEnabledException;
use Illuminate\Database\Eloquent\Model;

trait HasOwner
{
    private ?Model $owner = null;

    public function owner(Model $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    protected function getOwner(): ?Model
    {
        // TODO: Maybe refactor into 'capabilities.ownership' and differenciate between 'ownership' and 'tenancy'
        if (! config('capabilities.tenancy', false)) {
            throw TenancyNotEnabledException::make();
        }

        return $this->owner;
    }
}
