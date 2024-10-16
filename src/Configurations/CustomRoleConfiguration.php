<?php

namespace Guava\Capabilities\Configurations;

use Illuminate\Database\Eloquent\Model;

class CustomRoleConfiguration extends RoleConfiguration
{
    public function __construct(
        private string $name,
        private ?string $title = null,
        private ?Model $tenant = null,
        private array $attributes = [],
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

    public function isGlobal(): bool
    {
        return $this->tenant === null;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
