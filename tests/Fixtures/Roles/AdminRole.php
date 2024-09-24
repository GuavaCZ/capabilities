<?php

namespace Tests\Fixtures\Roles;

use Guava\Capabilities\Auth\RoleRegistration;

class AdminRole extends RoleRegistration {

    public function capabilities(): array
    {
        return [
            'course.view',
            'course.create',
            'course.edit',
            'course.delete',
        ];
    }

    public function name(): string
    {
        return 'admin';
    }

    public function title(): string
    {
        return 'Administrator';
    }
}
