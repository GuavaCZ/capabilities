<?php

namespace Guava\Capabilities\Configurations;

use Illuminate\Database\Eloquent\Model;

class CustomRoleConfiguration extends RoleConfiguration
{
    public function __construct(
        private string $name,
        private ?string $title = null,
        private ?Model $tenant = null,
    ) {}

    public function capabilities(): array
    {
        return [];
    }

    public function name(): string
    {
        return $this->name;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function isDefault(): bool
    {
        return false;
    }

    public function isGlobal(): bool
    {
        return $this->tenant === null;
    }
}
