<?php

namespace Tests\Feature;

use Guava\Capabilities\Auth\Capability;
use Guava\Capabilities\Builders\RoleBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\Models\Document;
use Tests\Fixtures\Models\Post;
use Tests\Fixtures\Models\Tenant;
use Tests\Fixtures\Models\User;
use Tests\TestCase;

use function Pest\Laravel\assertDatabaseHas;

class RoleBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_role()
    {
        RoleBuilder::of('admin')->create();

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 0);
    }

    public function test_can_create_tenant_role()
    {
        $tenant = Tenant::factory()->create();
        RoleBuilder::of('admin')->owner($tenant)->create();

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 0);
    }

    public function test_will_not_create_duplicate_role_if_exists()
    {
        RoleBuilder::of('admin')->create();
        RoleBuilder::of('admin')->create();

        // TODO: Add database has assertions

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 0);
    }

    public function test_can_create_role_and_assign_to_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        RoleBuilder::of('assign_single')
            ->assignees($user1)
            ->createIfNotExists()
            ->assign()
        ;

        RoleBuilder::of('assign_multiple')
            ->assignee($user1)
            ->assignee($user2)
            ->createIfNotExists()
            ->assign()
        ;

        // TODO: Add database has assertions

        $this->assertDatabaseCount('roles', 2);
        $this->assertDatabaseCount('assigned_roles', 3);
    }

    public function test_can_create_tenant_role_and_assign_to_users()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tenant = Tenant::factory()->create();

        RoleBuilder::of('assign_single')
            ->owner($tenant)
            ->assignee($user1)
            ->createIfNotExists()
            ->assign()
        ;

        RoleBuilder::of('assign_multiple')
            ->owner($tenant)
            ->assignee($user1)
            ->assignee($user2)
            ->createIfNotExists()
            ->assign()
        ;

        // TODO: Add database has assertions
        $this->assertDatabaseHas('roles', [
            'name' => 'assign_single',
            'tenant_id' => $tenant->getKey(),
        ]);
        $this->assertDatabaseHas('roles', [
            'name' => 'assign_multiple',
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 2);
        $this->assertDatabaseCount('assigned_roles', 3);
    }

    public function test_can_create_global_role_and_assign_to_users_within_tenant()
    {

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $tenant = Tenant::factory()->create();

        $role1 = RoleBuilder::of('assign_single')
            ->assignee($user1)
            ->tenant($tenant)
            ->createIfNotExists()
            ->assign()
        ;

        $role2 = RoleBuilder::of('assign_multiple')
            ->assignee($user1)
            ->assignee($user2)
            ->tenant($tenant)
            ->createIfNotExists()
            ->assign()
        ;

        $this->assertDatabaseHas('roles', [
            'name' => 'assign_single',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'assign_multiple',
            'tenant_id' => null,
        ]);

        //        $this->assertDatabaseHas('assigned_roles', [
        //            'role_id' => $role1->getKey(),
        //            'assignee_type' => $user1->getMorphClass(),
        //            'assignee_id' => $user1->getKey(),
        //            'tenant_id' => $tenant->getKey(),
        //        ]);
        //
        //        $this->assertDatabaseHas('assigned_roles', [
        //            'role_id' => $role2->getKey(),
        //            'assignee_type' => $user2->getMorphClass(),
        //            'assignee_id' => $user2->getKey(),
        //            'tenant_id' => $tenant->getKey(),
        //        ]);
        //
        //        $this->assertDatabaseHas('assigned_roles', [
        //            'role_id' => $role2->getKey(),
        //            'assignee_type' => $user1->getMorphClass(),
        //            'assignee_id' => $user1->getKey(),
        //            'tenant_id' => $tenant->getKey(),
        //        ]);

        $this->assertDatabaseCount('roles', 2);
        $this->assertDatabaseCount('assigned_roles', 3);
    }

    public function test_can_create_role_with_direct_capabilities()
    {
        $role = RoleBuilder::of('admin')
            ->capabilities(['custom', 'create', Capability::Edit, Capability::Delete])
            ->create()
        ;

        // TODO: Add database has assertions
        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseHas('capabilities', [
            'name' => 'custom',
        ]);

        $this->assertDatabaseHas('assigned_capabilities', [
            'capability_id' => 1,
            'assignee_type' => $role->getMorphClass(),
            'assignee_id' => $role->getKey(),
            'tenant_id' => null,
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 0);
        $this->assertDatabaseCount('capabilities', 4);
        $this->assertDatabaseCount('assigned_capabilities', 4);
    }

    public function test_can_create_role_with_model_capabilities()
    {
        $role = RoleBuilder::of('admin')
            ->capabilities(['custom', 'create', Capability::Edit, Capability::Delete], Post::class)
            ->create()
        ;

        // TODO: Add database has assertions
        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseHas('capabilities', [
            'name' => 'post.create',
        ]);

        $this->assertDatabaseHas('assigned_capabilities', [
            'capability_id' => 1,
            'assignee_type' => $role->getMorphClass(),
            'assignee_id' => $role->getKey(),
            'tenant_id' => null,
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 0);
        $this->assertDatabaseCount('capabilities', 4);
        $this->assertDatabaseCount('assigned_capabilities', 4);
    }

    public function test_can_create_role_with_record_capabilities()
    {
        $document = Document::factory()->create();

        $role = RoleBuilder::of('admin')
            ->capabilities(['custom', 'create', Capability::Edit, Capability::Delete], $document)
            ->create()
        ;

        // TODO: Add database has assertions
        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'tenant_id' => null,
        ]);

        $this->assertDatabaseHas('capabilities', [
            'name' => 'document.delete.' . $document->getKey(),
        ]);

        $this->assertDatabaseHas('assigned_capabilities', [
            'capability_id' => 1,
            'assignee_type' => $role->getMorphClass(),
            'assignee_id' => $role->getKey(),
            'tenant_id' => null,
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 0);
        $this->assertDatabaseCount('capabilities', 4);
        $this->assertDatabaseCount('assigned_capabilities', 4);
    }

    public function test_can_create_tenant_role_with_capabilities()
    {
        $tenant = Tenant::factory()->create();

        $role = RoleBuilder::of('admin')
            ->capabilities([Capability::View], Post::class)
            ->owner($tenant)
            ->create()
        ;

        $this->assertDatabaseHas('roles', [
            'name' => 'admin',
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseHas('capabilities', [
            'name' => 'post.view',
        ]);

        $this->assertDatabaseHas('assigned_capabilities', [
            'capability_id' => 1,
            'assignee_type' => $role->getMorphClass(),
            'assignee_id' => $role->getKey(),
            'tenant_id' => $tenant->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 1);
        $this->assertDatabaseCount('assigned_roles', 0);
        $this->assertDatabaseCount('capabilities', 1);
        $this->assertDatabaseCount('assigned_capabilities', 1);
    }

    public function test_can_check_if_capability_is_assigned()
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();

        $role = RoleBuilder::of('admin')
            ->capabilities([Capability::View], Post::class)
            ->owner($tenant)
            ->createIfNotExists()
            ->assignee($user)
            ->assign()
        ;

        $this->assertTrue($user->hasCapability('post.view'));
    }
}
