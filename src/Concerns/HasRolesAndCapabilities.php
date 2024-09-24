<?php

namespace Guava\Capabilities\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasRolesAndCapabilities
{
    use HasCapabilities {
        HasCapabilities::hasCapability as hasDirectCapability;
    }
    use HasRoles {
        HasRoles::hasCapability as hasRoleCapability;
    }

    public function hasCapability(string $name, ?Model $tenant = null): bool
    {
        return $this->hasDirectCapability($name, $tenant) || $this->hasRoleCapability($name, $tenant);
    }
}
