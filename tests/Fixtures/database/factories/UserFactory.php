<?php

namespace Tests\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Fixtures\Models\User;

class UserFactory extends Factory {

    protected $model = User::class;

    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
