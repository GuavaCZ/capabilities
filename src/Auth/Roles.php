<?php

namespace Guava\Capabilities\Auth;

use Guava\Capabilities\Facades\Capabilities;
use Illuminate\Database\Eloquent\Model;

class Roles
{
    public static function get(string $name, ?Model $tenant = null, array $attributes = []): ?Model
    {
        if (class_exists($name)) {
            $registration = app($name);

            if (! ($registration instanceof RoleRegistration)) {
                throw new \Exception("{$name} must be an instance of RoleRegistration");
            }

            return $registration->findOrCreate($tenant);
        }

        return Capabilities::role()->firstOrCreate([
            'name' => $name,
            'organization_id' => $tenant?->id,
        ], $attributes);
    }
}
