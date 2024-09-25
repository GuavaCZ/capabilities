<?php

namespace Guava\Capabilities\Auth;

use Guava\Capabilities\Contracts\Capability as CapabilityContract;

enum Capability: string implements CapabilityContract
{
    case Access = 'viewAny';
    case Create = 'create';
    case View = 'view';
    case Edit = 'update';
    case Delete = 'delete';
    case DeleteBulk = 'deleteAny';
    case Restore = 'restore';
    case RestoreBulk = 'restoreAny';
    case ForceDelete = 'forceDelete';
    case ForceDeleteBulk = 'forceDeleteBulk';
    case Replicate = 'replicate';

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
}
