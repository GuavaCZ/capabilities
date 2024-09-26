<?php

namespace Guava\Capabilities\Builders\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

trait HasAssignees
{
    private Collection $assignees;

    public function assignee(Model $assignee): static
    {
        return $this->assignees($assignee);
    }

    public function assignees(Model ...$assignees): static
    {
        $this->assignees ??= collect();

        $this->assignees->push(...$assignees);

        return $this;
    }

    protected function getAssignees(): Collection
    {
        return $this->assignees;
    }
}
