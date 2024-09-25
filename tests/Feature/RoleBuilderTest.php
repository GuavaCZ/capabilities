<?php

namespace Tests\Feature;

use Guava\Capabilities\Builders\RoleBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\Models\Tenant;
use Tests\Fixtures\Models\User;
use Tests\Fixtures\Roles\AdminRole;
use Tests\TestCase;

class RoleBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_global_custom_role()
    {
        RoleBuilder::of('create-only')
            ->create()
        ;

        $this->assertDatabaseHas('roles', [
            'name' => 'create-only',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 0);
    }

    public function test_can_create_global_predefined_role()
    {
        RoleBuilder::of(AdminRole::class)
            ->create()
        ;

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 0);
    }

    public function test_can_create_and_assign_global_custom_role()
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Create and Assign
        $role = RoleBuilder::of('create-and-assign')
            ->assignee($user)
            ->create()
            ->get()
        ;

        $this->assertDatabaseHas('roles', [
            'name' => 'create-and-assign',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => $role->getKey(),
            'tenant_id' => null,
            'assignee_type' => $user->getMorphClass(),
            'assignee_id' => $user->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 1);
    }

    public function test_can_create_only_and_then_assign_global_custom_role()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $role = RoleBuilder::of('create-only-and-then-assign')
            ->create()
            ->get()
        ;
        $user->role('create-only-and-then-assign')->assign();

        $this->assertDatabaseHas('roles', [
            'name' => 'create-only-and-then-assign',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => $role->getKey(),
            'tenant_id' => null,
            'assignee_type' => $user->getMorphClass(),
            'assignee_id' => $user->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 1);
    }

    public function test_can_not_associate_non_existant_global_role()
    {
        /** @var User $user */
        $user = User::factory()->create();

        RoleBuilder::of(AdminRole::class)
            ->assignee($user)
            ->assign()
        ;
        $this->assertDatabaseCount('roles', 0);
        $this->assertDatabaseCount('assigned_roles', 0);
    }

    public function test_create_if_trying_to_assign_non_existant_global_role()
    {
        /** @var User $user */
        $user = User::factory()->create();

        RoleBuilder::of(AdminRole::class)
            ->createIfNotExists()
            ->assignee($user)
            ->assign()
        ;
        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 1);
    }

    public function test_can_assign_global_predefined_role_to_user_within_tenant()
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create();

        // Assign global role to user within tenant (don't create - either throw error or fail silently)
        // variant 1
        $role1 = RoleBuilder::of(AdminRole::class)
            ->createIfNotExists()
            ->assign($user, $tenant)
            ->get()
        ;
        // variant 2
        $role2 = RoleBuilder::of('second-role')
            ->createIfNotExists()
            ->assignee($user)
            ->tenant($tenant)
            ->assign()
            ->get()
        ;
        // variant 3
        $role3 = RoleBuilder::of('third-role')
            ->createIfNotExists()
            ->owner($tenant)
            ->assignee($user)
            ->tenant($tenant)
            ->assign()
            ->get()
        ;

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => $role1->getKey(),
            'tenant_id' => $tenant->getKey(),
            'assignee_type' => $user->getMorphClass(),
            'assignee_id' => $user->getKey(),
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'second-role',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => $role2->getKey(),
            'tenant_id' => $tenant->getKey(),
            'assignee_type' => $user->getMorphClass(),
            'assignee_id' => $user->getKey(),
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'third-role',
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => $role3->getKey(),
            'tenant_id' => $tenant->getKey(),
            'assignee_type' => $user->getMorphClass(),
            'assignee_id' => $user->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 3);
        $this->assertDatabaseCount('assigned_roles', 3);
    }

    public function test_can_create_tenant_role()
    {
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create();

        RoleBuilder::of('accountant')
            ->owner($tenant)
            ->create()
        ;

        $this->assertDatabaseHas('roles', [
            'name' => 'accountant',
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 0);
    }

    public function test_can_create_tenant_role_and_assign_to_user()
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var Tenant $tenant */
        $tenant = Tenant::factory()->create();

        $role = RoleBuilder::of('accountant')
            ->owner($tenant)
            ->assignee($user)
            ->create()
            ->assign()
            ->get()
        ;

        $this->assertDatabaseHas('roles', [
            'name' => 'accountant',
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => $role->getKey(),
            'tenant_id' => $tenant->getKey(),
            'assignee_type' => $user->getMorphClass(),
            'assignee_id' => $user->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 1);
    }

    public function can_test()
    {

        // Assign global role to user within tenant (don't create - either throw error or fail silently)
        // variant 1
        RoleBuilder::of(AdminRole::class)
            ->assign($user, $tenant)
        ;
        // variant 2
        RoleBuilder::of(AdminRole::class)
            ->asignee($user)
            ->tenant($tenant)
            ->assign()
        ;
        // variant 3
        RoleBuilder::of(AdminRole::class)
            ->asignee($user, $tenant)
            ->assign()
        ;

        // Create global role and assign to user
        // variant 1
        RoleBuilder::make(AdminRole::class)
            ->createAndAssign($user)
        ;
        // variant 2
        RoleBuilder::make(AdminRole::class)
            ->asignee($user)
            ->createIfNotExists()
            ->assign()
        ;

        // Create global role and assign to user within tenant
        // variant 1
        RoleBuilder::make(AdminRole::class)
            ->createAndAssign($user, $tenant)
        ;
        // variant 2
        RoleBuilder::make(AdminRole::class)
            ->asignee($user)
            ->tenant($tenant)
            ->createIfNotExists()
            ->assign()
        ;
        // variant 3
        RoleBuilder::make(AdminRole::class)
            ->asignee($user, $tenant)
            ->createIfNotExists()
            ->assign()
        ;

        // Create tenant role
        RoleBuilder::make(AdminRole::class)
            ->belongsTo($tenant) // ->ownedBy($tenant)
            ->create()
        ;

        // Assign tenant role to user (don't create - either throw error or fail silently)
        // variant 1
        RoleBuilder::of(AdminRole::class)
            ->belongsTo($tenant)
            ->assign($user)
        ;
        // variant 2
        RoleBuilder::of(AdminRole::class)
            ->belongsTo($tenant)
            ->assignee($user)
            ->assign()
        ;
        // variant 3
        RoleBuilder::of(AdminRole::class)
            ->belongsTo($tenant)
            ->assignee($user)
            ->tenant($tenant) //redundant with belongsTo
            ->assign()
        ;

        // Create tenant role and assign to user (within tenant automatically)
        // variant 1
        RoleBuilder::make(AdminRole::class)
            ->belongsTo($tenant)
            ->createAndAssign($user)
        ;
        // variant 2
        RoleBuilder::make(AdminRole::class)
            ->belongsTo($tenant)
            ->assignee($user)
            ->create()
        ;
        // variant 3
        RoleBuilder::make(AdminRole::class)
            ->belongsTo($tenant)
            ->assignee($user, $tenant)
            ->create()
        ;
        // variant 4
        RoleBuilder::make(AdminRole::class)
            ->belongsTo($tenant)
            ->assignee($user)
            ->tenant($tenant) // redundant if belongsTo is set.
            ->create()
        ;
    }
}
