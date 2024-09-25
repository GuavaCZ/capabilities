<?php

namespace Guava\Capabilities\Builders;

use Illuminate\Database\Eloquent\Model;

interface Builder
{
    public function assign(): static;

    public function create(): static;

    public function get(): ?Model;
}
