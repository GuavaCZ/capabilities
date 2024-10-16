<?php

namespace Tests\Fixtures\Models;

use Guava\Capabilities\Models\Role as BaseRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
