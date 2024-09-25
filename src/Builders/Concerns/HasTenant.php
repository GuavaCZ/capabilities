<?php

namespace Guava\Capabilities\Builders\Concerns;

use Guava\Capabilities\Exceptions\TenancyNotEnabledException;
use Illuminate\Database\Eloquent\Model;

trait HasTenant
{
    private ?Model $tenant = null;

    public function tenant(Model $tenant): static
    {
        $this->tenant = $tenant;

        return $this;
    }

    protected function getTenant(): ?Model
    {
        if (! config('capabilities.tenancy', false)) {
            throw TenancyNotEnabledException::make();
        }

        return $this->tenant;
    }
}
