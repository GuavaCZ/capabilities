<?php

namespace Guava\Capabilities\Builders;

use Guava\Capabilities\Builders\Concerns\CanCreateIfNotExists;
use Guava\Capabilities\Builders\Concerns\HasAssignee;
use Guava\Capabilities\Builders\Concerns\HasTenant;
use Guava\Capabilities\Contracts\Capability as CapabilityContract;
use Guava\Capabilities\Facades\Capabilities;
use Guava\Capabilities\Models\Capability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Stringable;

class CapabilityBuilder implements Builder
{
    use CanCreateIfNotExists;
    use HasAssignee;
    use HasTenant;

    protected CapabilityContract $configuration;

    protected ?Capability $model = null;

    public function __construct(string | CapabilityContract $capability, string | Model | null $model = null)
    {
        $this->configuration = static::configuration($capability instanceof CapabilityContract
            ? $capability->getName()
            : $capability, $model);
        //        $this->configuration = $capability instanceof CapabilityContract
        //            ? $capability
        //            : static::configuration($capability, $model);
    }

    public function assign(?Model $assignee = null, ?Model $tenant = null): static
    {
        $assignee ??= $this->getAssignee();
        if (! $assignee) {
            return $this;
        }

        $tenant ??= $this->getTenant();

        $model = $this->get();

        if (! $model && $this->shouldCreateIfNotExists()) {
            $model = $this->create()->get();
        }

        if ($model) {
            $assignee->assignCapability($model, $tenant);
        }

        return $this;
    }

    public function create(): static
    {
        $this->model = Capabilities::capability()->create([
            'name' => $this->configuration->getName(),
        ]);

        return $this;
    }

    public function get(): ?Model
    {
        return $this->model ?? $this->find();
    }

    private function find(): ?Capability
    {
        return Capabilities::capability()->firstWhere([
            'name' => $this->configuration->getName(),
        ]);
    }

    public static function of(string | CapabilityContract $capability, string | Model | null $model = null)
    {
        return app(static::class, ['capability' => $capability, 'model' => $model]);
    }

    private static function configuration(string $name, string | Model | null $model = null): CapabilityContract
    {
        $record = null;
        if ($model instanceof Model) {
            $record = $model;
            $model = $record::class;
        }

        return new class($name, $model, $record) implements CapabilityContract
        {
            public function __construct(
                private string $name,
                private ?string $model = null,
                private ?Model $record = null,
            ) {}

            public function getName(): string
            {
                return str($this->name)
                    ->when(
                        $this->model,
                        fn (Stringable $stringable) => $stringable
                            ->prepend('.')
                            ->prepend(str(class_basename($this->model))->kebab())
                    )
                    ->when(
                        $this->record,
                        fn (Stringable $stringable) => $stringable
                            ->append('.')
                            ->append($this->record->getKey())
                    )
                ;
            }

            public function getModel(): ?string
            {
                return null;
            }
        };
    }
}
