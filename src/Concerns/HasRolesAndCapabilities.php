<?php

namespace Guava\Capabilities\Concerns;

use Guava\Capabilities\Configurations\CapabilityConfiguration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait HasRolesAndCapabilities
{
    use HasCapabilities {
        HasCapabilities::hasCapability as traitHasDirectCapability;
    }
    use HasRoles {
        HasRoles::hasCapability as traitHasRoleCapability;
    }

    public function hasCapability(CapabilityConfiguration | array | Collection $capability, ?Model $tenant = null): bool
    {
        return $this->hasDirectCapability($capability, $tenant) || $this->hasRoleCapability($capability, $tenant);
    }
}
