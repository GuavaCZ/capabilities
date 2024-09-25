<?php

namespace Guava\Capabilities\Auth;

use Guava\Capabilities\Facades\Capabilities;
use Guava\Capabilities\Models\Role;
use Illuminate\Database\Eloquent\Model;

abstract class RoleRegistration
{
    abstract public function capabilities(): array;

    abstract public function name(): string;

    abstract public function title(): string;

    public function defaultAttributes(): array
    {
        return [
            'name' => $this->name(),
            'title' => $this->title(),
        ];
    }

    public function findOrCreate(?Model $tenant = null): Role
    {
        /** @var Role $role */
        if ($role = Capabilities::role()->firstWhere([
            'name' => $this->name(),
            'organization_id' => $tenant?->id,
        ])) {
            return $role;
        }

        /** @var Role $role */
        $role = Capabilities::role()->create([
            ...$this->defaultAttributes(),
            'organization_id' => $tenant?->id,
        ]);

        // TODO: Sync capabilities
        return $role;
    }
}
