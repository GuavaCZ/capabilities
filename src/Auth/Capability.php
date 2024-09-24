<?php

namespace Guava\Capabilities\Auth;

enum Capability: string implements CapabilityRegistration
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

}
