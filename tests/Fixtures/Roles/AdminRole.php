<?php

namespace Tests\Fixtures\Roles;

use Guava\Capabilities\Capability;
use Guava\Capabilities\Configurations\RoleConfiguration;
use Tests\Fixtures\Models\Document;
use Tests\Fixtures\Models\Post;

class AdminRole extends RoleConfiguration
{
    public function capabilities(): array
    {
        return [
            ...Capability::all(Post::class),
            ...Capability::all(Document::class),
        ];
    }

    public function name(): string
    {
        return 'admin';
    }

    public function title(): ?string
    {
        return 'Administrator';
    }

    public function isDefault(): bool
    {
        return true;
    }

    public function isGlobal(): bool
    {
        return true;
    }

    public function getAttributes(): array
    {
        return [];
    }
}
