<?php

namespace Tests\Fixtures\Roles;

use Guava\Capabilities\Contracts\Role;

class AdminRole implements Role
{
    public function capabilities(): array
    {
        return [
            'course.view',
            'course.create',
            'course.edit',
            'course.delete',
        ];
    }

    public function getName(): string
    {
        return 'admin';
    }
}
