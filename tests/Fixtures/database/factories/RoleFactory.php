<?php

namespace Tests\Database\Factories;

use Guava\Capabilities\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Fixtures\Models\User;

class RoleFactory extends Factory {

    protected $model = Role::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'title' => $this->faker->word(),
        ];
    }
}
