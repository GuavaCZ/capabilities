<?php

namespace Tests\Fixtures\Roles;

use Guava\Capabilities\Capability;
use Guava\Capabilities\Configurations\RoleConfiguration;
use Tests\Fixtures\Models\Post;

class MemberRole extends RoleConfiguration
{
    public function capabilities(): array
    {
        return Capability::only([
            Capability::View,
            Capability::Create,
            Capability::Update,
        ], Post::class);
    }

    public function name(): string
    {
        return 'member';
    }

    public function title(): ?string
    {
        return 'Member';
    }

    public function isDefault(): bool
    {
        return true;
    }

    public function isGlobal(): bool
    {
        return false;
    }

    public function getAttributes(): array
    {
        return [];
    }
}
