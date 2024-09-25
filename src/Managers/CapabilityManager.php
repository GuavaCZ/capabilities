<?php

namespace Guava\Capabilities\Managers;

use Guava\Capabilities\Contracts\Capability as CapabilityContract;
use Guava\Capabilities\Facades\Capabilities;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;

class CapabilityManager
{
    public function getName(string | CapabilityContract $capability, null | Model | string $model = null): string
    {
        $record = null;

        if ($capability instanceof CapabilityContract) {
            $model ??= $capability->getModel();
            $capability = $capability->getValue();
        }

        if ($model instanceof Model) {
            $record = $model;
            $model = $model::class;
        }

        if (is_string($model) && class_exists($model)) {
            $model = str(class_basename($model))->kebab();
        }

        return str($capability)
            ->when(
                $model,
                fn (Stringable $stringable) => $stringable
                    ->prepend('.')
                    ->prepend($model)
            )
            ->when(
                $record,
                fn (Stringable $stringable) => $stringable
                    ->append('.')
                    ->append($record->getKey())
            )
        ;
    }

    public function getRecord(string | CapabilityContract $capability, null | Model | string $model = null, bool $create = true): ?Model
    {
        $name = $this->getName($capability, $model);

        $record = Capabilities::capability()
            ->firstWhere('name', $name)
        ;

        if ($create && is_null($record)) {
            $record = Capabilities::capability()
                ->create([
                    'name' => $name,
                ])
            ;
        }

        return $record;
    }

    public function getRecords(array | Collection $capabilities, null | Model | string $model = null, bool $create = true): Collection
    {
        return collect($capabilities)
            ->map(fn ($capability) => static::getRecord($capability, $model, $create))
        ;
    }
}
