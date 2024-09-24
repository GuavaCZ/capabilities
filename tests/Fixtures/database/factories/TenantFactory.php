<?php

namespace Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Fixtures\Models\Tenant;
use Tests\Fixtures\Models\User;

class TenantFactory extends Factory {

    protected $model = Tenant::class;

    public function definition()
    {
        return [
            'name' => $this->faker->company,
        ];
    }
}
