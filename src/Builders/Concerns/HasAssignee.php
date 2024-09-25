<?php

namespace Guava\Capabilities\Builders\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasAssignee
{
    private ?Model $assignee = null;

    public function assignee(Model $assignee): static
    {
        $this->assignee = $assignee;

        return $this;
    }

    protected function getAssignee(): ?Model
    {
        return $this->assignee;
    }
}
