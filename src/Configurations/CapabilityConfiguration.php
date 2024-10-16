<?php

namespace Guava\Capabilities\Configurations;

use Illuminate\Database\Eloquent\Model;

class CapabilityConfiguration
{
    public function __construct(
        private string $name,
        private null | string | Model $entity = null,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getEntity(): null | string | Model
    {
        return $this->entity;
    }

    public function getEntityType(): ?string
    {
        return $this->entity instanceof Model
            ? $this->entity->getMorphClass()
            : $this->entity;
    }

    public function getEntityKey(): mixed
    {
        return $this->entity instanceof Model
            ? $this->entity->getKey()
            : null;
    }
}
