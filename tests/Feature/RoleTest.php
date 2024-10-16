<?php

namespace Tests\Feature;

use Tests\Fixtures\Models\Role;
use Tests\Fixtures\Models\Tenant;
use Tests\Fixtures\Models\User;
use Tests\Fixtures\Roles\AdminRole;
use Tests\TestCase;

class RoleTest extends TestCase
{
    public function test_can_assign_custom_role()
    {
        $user = User::factory()->create();

        $user->assignRole('my-custom-role');

        $this->assertTrue($user->assignedRoles->contains(Role::firstWhere('name', 'my-custom-role')));
    }

    public function test_cant_assign_same_custom_role_multiple_times()
    {

        $user = User::factory()->create();

        $user->assignRole('my-custom-role');
        $user->assignRole('my-custom-role');

        $this->assertTrue($user->assignedRoles->contains(Role::firstWhere('name', 'my-custom-role')));
        $this->assertCount(1, $user->assignedRoles);
    }

    public function test_can_assign_custom_tenant_role()
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();

        $user->assignRole('my-custom-role', $tenant);

        $this->assertTrue($user->assignedRoles->contains(
            Role::query()
                ->where('name', 'my-custom-role')
                ->where('tenant_id', $tenant->id)
                ->first()
        ));
    }

    public function test_cant_assign_same_custom_tenant_role_multiple_times()
    {

        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();

        $user->assignRole('my-custom-role', $tenant);
        $user->assignRole('my-custom-role', $tenant);

        $this->assertTrue($user->assignedRoles->contains(Role::firstWhere('name', 'my-custom-role')));
        $this->assertCount(1, $user->assignedRoles);
    }

    public function test_can_assign_preset_role()
    {
        $user = User::factory()->create();

        $user->assignRole(new AdminRole);

        $this->assertTrue($user->assignedRoles->contains(Role::firstWhere('name', 'admin')));
    }

    public function test_cant_assign_preset_role_multiple_times()
    {
        $user = User::factory()->create();

        $user->assignRole(new AdminRole);
        $user->assignRole(new AdminRole);

        $this->assertTrue($user->assignedRoles->contains(Role::firstWhere('name', 'admin')));
        $this->assertCount(1, $user->assignedRoles);
    }
}
