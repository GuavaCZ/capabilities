<?php

namespace Tests\Feature;

use Guava\Capabilities\Facades\Capabilities;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\Models\Role;
use Tests\Fixtures\Models\Tenant;
use Tests\Fixtures\Models\User;
use Tests\Fixtures\Roles\AdminRole;
use Tests\TestCase;

class RoleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_assign_custom_role()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $user->role('my-custom-role')->createIfNotExists()->assign();
        // old way
        //        $user->assignRole('my-custom-role');

        $this->assertDatabaseHas('roles', [
            'name' => 'my-custom-role',
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => Role::firstWhere('name', 'my-custom-role')->getKey(),
            'assignee_type' => $user->getMorphClass(),
            'assignee_id' => $user->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 1);
    }

    public function test_can_assign_custom_tenant_role()
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create();

        $user->role('my-custom-role')->owner($tenant)->createAndAssign();
        // old way
        //        $user->assignRole('my-custom-role', $tenant);

        $this->assertDatabaseHas('roles', [
            'name' => 'my-custom-role',
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => Role::firstWhere('name', 'my-custom-role')->getKey(),
            'assignee_type' => $user->getMorphClass(),
            'assignee_id' => $user->getKey(),
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 1);
    }

    public function test_can_assign_custom_tenant_role_with_custom_capabilities()
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create();

        $role = $user->role('my-custom-role')
            ->owner($tenant)
            ->capabilities(['course.view', 'course.create'])
            ->createAndAssign()
//            ->syncCapabilities()
            ->get()
        ;
        // old way
        //        $tenant->assignRole('my-custom-role', $tenant, ['course.view', 'course.create']);

        $this->assertDatabaseHas('roles', [
            'name' => 'my-custom-role',
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => Role::firstWhere('name', 'my-custom-role')->getKey(),
            'assignee_type' => $user->getMorphClass(),
            'assignee_id' => $user->getKey(),
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseHas('capabilities', [
            'name' => 'course.view',
        ]);
        $this->assertDatabaseHas('capabilities', [
            'name' => 'course.create',
        ]);
        $this->assertDatabaseHas('assigned_capabilities', [
            'capability_id' => Capabilities::capability()->firstWhere('name', 'course.view')->getKey(),
            'assignee_type' => $role->getMorphClass(),
            'assignee_id' => $role->getKey(),
            'tenant_id' => null,
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 1);
        $this->assertDatabaseCount('capabilities', 2);
        $this->assertDatabaseCount('assigned_capabilities', 2);
    }

    public function test_can_assign_predefined_role()
    {

        /** @var User $user */
        $user = User::factory()->create();

        $user->role(AdminRole::class)->createAndAssign();
        // Old way
        //        $user->assignRole(AdminRole::class);

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => Role::firstWhere('name', 'admin')->getKey(),
            'assignee_type' => $user->getMorphClass(),
            'assignee_id' => $user->getKey(),
            'tenant_id' => null,
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 1);
    }

    public function test_can_assign_predefined_tenant_role()
    {

        /** @var User $user */
        $user = User::factory()->create();
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create();

        $user->role(AdminRole::class)->owner($tenant)->createAndAssign();
        // Old way
        //        $user->assignRole(AdminRole::class, $tenant);

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => Role::firstWhere('name', 'admin')->getKey(),
            'assignee_type' => $user->getMorphClass(),
            'assignee_id' => $user->getKey(),
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 1);
    }

    public function test_can_assign_global_role_to_tenant()
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create();

        $user->role(AdminRole::class)->tenant($tenant)->createAndAssign();
        // Old way, not even working
        //        $user->role(AdminRole::class);
        //        $user->assignRole(AdminRole::class, $tenant);
        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => Role::firstWhere('name', 'admin')->getKey(),
            'assignee_type' => $user->getMorphClass(),
            'assignee_id' => $user->getKey(),
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 1);
    }
}
