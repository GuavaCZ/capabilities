<?php

namespace Tests\Database\Factories;

use Guava\Capabilities\Models\Capability;
use Illuminate\Database\Eloquent\Factories\Factory;

class CapabilityFactory extends Factory {

    protected $model = Capability::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'title' => $this->faker->word(),
        ];
    }
}
