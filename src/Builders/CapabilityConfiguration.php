<?php

namespace Guava\Capabilities\Builders;

use Guava\Capabilities\Contracts\Capability as CapabilityContract;
use Guava\Capabilities\Models\Capability;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Stringable;

final class CapabilityConfiguration implements CapabilityContract
{
    final private function __construct(
        private string $name,
        private ?string $model = null,
        private ?Model $record = null,
    ) {}

    public function getModel(): ?string
    {
        return null;
    }

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

    public static function make(string | CapabilityContract | Capability $capability, string | Model | null $record = null)
    {
        if ($capability instanceof Capability && ! is_null($record)) {
            throw new \Exception('You can\'t pass a record when passing a Capability model instance.');
        }

        $model = null;
        if (is_string($record)) {
            $model = $record;
            $record = null;
        }

        if ($record instanceof Model) {
            $model = $record::class;
        }

        if ($capability instanceof CapabilityContract) {
//            return app(self::class, ['capability' => $capability->getName(), 'model' => $model ?? $capability->getModel(), 'record' => $record]);
            return new static($capability->getName(), $model ?? $capability->getModel(), $record);
        } else {
//            return app(self::class, ['capability' => $capability, 'model' => $model, 'record' => $record]);
            return new static($capability, $model, $record);
        }
    }
}
