<?php

namespace Guava\Capabilities\Concerns;

use Guava\Capabilities\Models\Capability;
use Illuminate\Database\Eloquent\Model;
use Guava\Capabilities\Contracts\Capability as CapabilityContract;
use Illuminate\Support\Collection;

trait HasRolesAndCapabilities
{
    use HasCapabilities {
        HasCapabilities::hasCapability as hasDirectCapability;
    }
    use HasRoles {
        HasRoles::hasCapability as hasRoleCapability;
    }

    public function hasCapability(string | Capability | array |Collection $capability, ?Model $tenant = null): bool
    {
        return $this->hasDirectCapability($capability, $tenant) || $this->hasRoleCapability($capability, $tenant);
    }
}
