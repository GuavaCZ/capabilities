<?php

namespace Tests\Feature;

use Tests\Fixtures\Models\Capability;
use Tests\Fixtures\Models\Tenant;
use Tests\Fixtures\Models\User;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    public function test_can_assign_custom_capability()
    {
        $user = User::factory()->create();
        $capability = Capability::factory()->create();

        $user->assignCapability($capability);

        $this->assertTrue($user->assignedCapabilities->contains(Capability::firstWhere('name', $capability->name)));
    }

    public function test_cant_assign_same_custom_capability_multiple_times()
    {
        $user = User::factory()->create();
        $capability = Capability::factory()->create();

        $user->assignCapability($capability);
        $user->assignCapability([$capability, $capability]);

        $this->assertTrue($user->assignedCapabilities->contains($capability));
        $this->assertCount(1, $user->assignedCapabilities);
    }

    public function test_can_assign_custom_tenant_capability()
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $capability = Capability::factory()->create();

        $user->assignCapability($capability, $tenant);

        $this->assertTrue($user->assignedCapabilities()
            ->wherePivot(
                'tenant_id',
                $tenant->id,
            )
            ->get()
            ->contains($capability));
    }

    public function test_cant_assign_same_custom_tenant_role_multiple_times()
    {

        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $capability = Capability::factory()->create();

        $user->assignCapability($capability, $tenant);
        $user->assignCapability($capability, $tenant);

        $this->assertTrue(
            $user->assignedCapabilities()
                ->wherePivot('tenant_id', $tenant->id)
                ->get()
                ->contains($capability)
        );
        $this->assertCount(1, $user->assignedCapabilities()
            ->wherePivot('tenant_id', $tenant->id)
            ->get());
    }
}
