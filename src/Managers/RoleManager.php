<?php

namespace Guava\Capabilities\Managers;

use Guava\Capabilities\Contracts\Role as RoleContract;
use Guava\Capabilities\Exceptions\InvalidRoleArgumentException;
use Guava\Capabilities\Facades\Capabilities;
use Guava\Capabilities\Models\Role;
use Illuminate\Database\Eloquent\Model;

class RoleManager
{
    public function getName(string | RoleContract $role): string
    {
        if (is_string($role) && class_exists($role)) {
            $role = new $role;
        }

        if ($role instanceof RoleContract) {
            $role = $role->getName();
        } elseif (! is_string($role)) {
            throw InvalidRoleArgumentException::make();
        }

        return $role;
    }

    public function getRecord(string | RoleContract $role, ?Model $tenant = null, bool $create = true): ?Role
    {
        $name = $this->getName($role);

        $columns = [
            'name' => $name,
        ];

        if (config('capabilities.tenancy', false)) {
            $columns[config('capabilities.tenant_column', 'tenant_id')] = $tenant?->getKey();
        }

        $record = Capabilities::role()
            ->where($columns)
            ->first()
        ;

        if ($create && is_null($record)) {
            $record = Capabilities::role()
                ->create($columns)
            ;
        }

        return $record;
    }
}
