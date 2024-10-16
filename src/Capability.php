<?php

namespace Guava\Capabilities;

use Guava\Capabilities\Configurations\CapabilityConfiguration;
use Guava\Capabilities\Contracts\Capability as CapabilityContract;
use Illuminate\Database\Eloquent\Model;

enum Capability: string implements CapabilityContract
{
    case ViewAny = 'viewAny';
    case Create = 'create';
    case View = 'view';
    case Update = 'update';
    case Delete = 'delete';
    case DeleteAny = 'deleteAny';
    case Restore = 'restore';
    case RestoreAny = 'restoreAny';
    case ForceDelete = 'forceDelete';
    case ForceDeleteAny = 'forceDeleteAny';
    case Replicate = 'replicate';
    case Reorder = 'reorder';

    public function model(): ?string
    {
        return null;
    }

    public function getModel(): ?string
    {
        return null;
    }

    public function getName(): string
    {
        return $this->value;
    }

    public function get(null | string | Model $record = null): CapabilityConfiguration
    {
        return self::id($this, $record);
    }

    public function record(null | string | Model $record = null): CapabilityConfiguration
    {
        return self::id($this, $record);
    }

    public static function id(string | Capability $capability, null | string | Model $record = null): CapabilityConfiguration
    {
        $name = is_string($capability) ? $capability : $capability->getName();

        return new CapabilityConfiguration($name, $record);
    }

    public static function custom(string $name, null | string | Model $record = null): CapabilityConfiguration
    {
        return self::id($name, $record);
    }

    public static function only(array $capabilities, null | string | Model $record = null): array
    {
        return collect($capabilities)
            ->map(
                fn (Capability | string $capability) => is_string($capability)
                    ? self::custom($capability, $record)
                    : $capability->record($record)
            )
            ->all()
        ;
    }

    public static function all(null | string | Model $record = null): array
    {
        return self::only(self::cases(), $record);
    }
}
