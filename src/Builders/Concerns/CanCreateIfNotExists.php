<?php

namespace Guava\Capabilities\Builders\Concerns;

trait CanCreateIfNotExists
{
    private bool $createIfNotExists = false;

    public function createIfNotExists(bool $condition = true): static
    {
        $this->createIfNotExists = $condition;

        return $this;
    }

    protected function shouldCreateIfNotExists(): bool
    {
        return $this->createIfNotExists;
    }
}
