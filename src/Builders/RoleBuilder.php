<?php

namespace Guava\Capabilities\Builders;

use Guava\Capabilities\Builders\Concerns\CanCreateIfNotExists;
use Guava\Capabilities\Builders\Concerns\HasAssignee;
use Guava\Capabilities\Builders\Concerns\HasCapabilities;
use Guava\Capabilities\Builders\Concerns\HasOwner;
use Guava\Capabilities\Builders\Concerns\HasTenant;
use Guava\Capabilities\Contracts\Capability;
use Guava\Capabilities\Contracts\Role as RoleContract;
use Guava\Capabilities\Exceptions\InvalidRoleArgumentException;
use Guava\Capabilities\Facades\Capabilities;
use Guava\Capabilities\Models\Role;
use Illuminate\Database\Eloquent\Model;

class RoleBuilder implements Builder
{
    use CanCreateIfNotExists;
    use HasAssignee;
    use HasCapabilities;
    use HasOwner;
    use HasTenant;

    protected RoleContract $configuration;

    protected ?Role $model = null;

    public function __construct(string | RoleContract $role)
    {
        if (is_string($role) && ! class_exists($role)) {
            $role = static::configuration($role);
        }

        if (is_string($role) && class_exists($role)) {
            $role = new $role;
        }

        if ($role instanceof RoleContract) {
            $this->configuration = $role;
        } else {
            throw InvalidRoleArgumentException::make();
        }
    }

    public function assign(?Model $assignee = null, ?Model $tenant = null): static
    {
        $assignee ??= $this->getAssignee();
        if (! $assignee) {
            return $this;
        }

        $tenant ??= $this->getTenant() ?? $this->getOwner();

        $model = $this->get();

        if (! $model && $this->shouldCreateIfNotExists()) {
            $model = $this->create()->get();
        }

        if ($model) {
            $assignee->assignRole($model, $tenant);
        }

        return $this;
    }

    public function create(): static
    {
        $columns = [
            'name' => $this->configuration->getName(),
        ];

        if ($owner = $this->getOwner()) {
            $columns[config('capabilities.tenant_column')] = $owner->getKey();
        }

        $this->model = Capabilities::role()->create($columns);

        $this->syncCapabilities();

        // Assign can safely be called, since it verifies whether assignee is set
        return $this->assign();
    }

    public function createAndAssign(?Model $assignee = null, ?Model $tenant = null): static
    {
        return $this->createIfNotExists()->assign($assignee, $tenant);
    }

    public function syncCapabilities(): static
    {
        /** @var Role $model */
        $model = $this->get();
        if ($model) {
            $this->getCapabilities()
                ->each(fn (string | Capability $capability) => $model->capability($capability)
                    ->create()->assign())
            ;
        }

        return $this;
    }

    public function get(): ?Model
    {
        return $this->model ?? $this->find();
    }

    private function find(): ?Role
    {
        return Capabilities::role()->firstWhere([
            'name' => $this->configuration->getName(),
        ]);
    }

    public static function of(string | RoleContract $role)
    {
        return app(static::class, ['role' => $role]);
    }

    private static function configuration(string $name): RoleContract
    {
        return new class($name) implements RoleContract
        {
            public function __construct(
                private string $name
            ) {}

            public function getName(): string
            {
                return $this->name;
            }
        };
    }
}
