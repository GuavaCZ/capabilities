<?php

// config for Guava/Capabilities
return [
    'user_class' => \Illuminate\Foundation\Auth\User::class,
    'capability_class' => \Guava\Capabilities\Models\Capability::class,
    'role_class' => \Guava\Capabilities\Models\Role::class,
    'tenant_class' => null,

    'tenancy' => false,
    'tenant_column' => 'tenant_id',
];
