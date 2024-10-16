<?php

namespace Guava\Capabilities\Contracts;

interface Capability
{
    public function getModel(): ?string;

    public function getName(): string;
}
