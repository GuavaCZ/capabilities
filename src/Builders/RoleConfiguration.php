<?php

namespace Guava\Capabilities\Builders;

use Guava\Capabilities\Contracts\Role;
use Guava\Capabilities\Exceptions\InvalidRoleArgumentException;

final class RoleConfiguration implements Role
{
    public function __construct(
        private string $name,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public static function make(string | Role $role)
    {
        if (is_string($role) && ! class_exists($role)) {
            return app(self::class, ['name' => $role]);
        }

        if (is_string($role) && class_exists($role)) {
            return new $role;
        }

        if ($role instanceof Role) {
            return $role;
        } else {
            throw InvalidRoleArgumentException::make();
        }
    }
}
