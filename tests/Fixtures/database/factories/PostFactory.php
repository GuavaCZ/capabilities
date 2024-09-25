<?php

namespace Tests\Database\Factories;

use Guava\Capabilities\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Fixtures\Models\Document;
use Tests\Fixtures\Models\Post;
use Tests\Fixtures\Models\User;

class PostFactory extends Factory {

    protected $model = Post::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(),
        ];
    }
}
