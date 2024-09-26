<?php

namespace Guava\Capabilities\Builders;

use Guava\Capabilities\Builders\Concerns\CanCreateIfNotExists;
use Guava\Capabilities\Builders\Concerns\HasAssignees;
use Guava\Capabilities\Builders\Concerns\HasCapabilities;
use Guava\Capabilities\Builders\Concerns\HasOwner;
use Guava\Capabilities\Builders\Concerns\HasTenant;
use Guava\Capabilities\Contracts\Capability as CapabilityContract;
use Guava\Capabilities\Facades\Capabilities;
use Guava\Capabilities\Models\Capability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Stringable;

class AssigneeBuilder
{
    use CanCreateIfNotExists;
    use HasAssignees;
    use HasCapabilities;
    use HasOwner;
    use HasTenant;


    public function __construct(string | CapabilityContract $role)
    {
    }

    public static function of(string | CapabilityContract $role)
    {
        return app(static::class, ['role' => $role]);
    }
}
