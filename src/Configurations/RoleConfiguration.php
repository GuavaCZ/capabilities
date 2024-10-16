<?php

namespace Guava\Capabilities\Configurations;

use Guava\Capabilities\Facades\Capabilities;
use Guava\Capabilities\Models\Role;
use Illuminate\Database\Eloquent\Model;

abstract class RoleConfiguration
{
    abstract public function capabilities(): array;

    abstract public function name(): string;

    abstract public function title(): ?string;

    abstract public function isDefault(): bool;

    abstract public function isGlobal(): bool;

    public function find(?Model $tenant = null): ?Role
    {
        if (! config('capabilities.tenancy', false) && $tenant) {
            throw new \Exception('Tenant roles can only be used with tenancy enabled.');
        }

        if ($this->isGlobal() && $tenant) {
            throw new \Exception('Global roles cannot have tenants');
        }

        if (! $this->isGlobal() && ! $tenant) {
            throw new \Exception('Tenant roles must have tenants');
        }

        $attributes = [
            'name' => $this->name(),
            'title' => $this->title(),
            'is_default' => $this->isDefault(),
        ];

        if ($this->isGlobal()) {
            $attributes[config('capabilities.tenant_column', 'tenant_id')] = null;
        } else {
            $attributes[config('capabilities.tenant_column', 'tenant_id')] = $tenant->getKey();
        }

        if ($record = Capabilities::role()->first($attributes)) {
            return $record;
        }

        /** @var Role $record */
        $record = Capabilities::role()->create($attributes);
        $record->assignCapability($this->capabilities(), $tenant);

        return $record;
    }
}
