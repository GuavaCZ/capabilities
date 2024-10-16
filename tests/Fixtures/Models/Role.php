<?php

namespace Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Guava\Capabilities\Models\Role as BaseRole;

class Role extends BaseRole
{
    use HasFactory;

    public function getFillable(): array
    {
        return [
            ...parent::getFillable(),
            'custom_attribute_1',
            'custom_attribute_2',
        ];
    }
}
