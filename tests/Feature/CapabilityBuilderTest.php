<?php

namespace Tests\Feature;

use Guava\Capabilities\Auth\Capability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fixtures\Models\Document;
use Tests\Fixtures\Models\Post;
use Tests\Fixtures\Models\Role;
use Tests\TestCase;

class CapabilityBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_custom_capability()
    {
        /** @var Role $role */
        $role = Role::factory()->create();

        $role->capability('custom-capability')->create();

        $this->assertDatabaseHas('capabilities', [
            'name' => 'custom-capability',
        ]);

        $this->assertDatabaseCount('capabilities', 1);
        $this->assertDatabaseCount('assigned_capabilities', 0);
    }

    public function test_can_create_and_assign_custom_capability()
    {
        /** @var Role $role */
        $role = Role::factory()->create();

        $capability = $role->capability('custom-capability')->createIfNotExists()->assign()->get();

        $this->assertDatabaseHas('capabilities', [
            'name' => 'custom-capability',
        ]);

        $this->assertDatabaseHas('assigned_capabilities', [
            'capability_id' => $capability->getKey(),
            'assignee_type' => $role->getMorphClass(),
            'assignee_id' => $role->getKey(),
            'tenant_id' => null,
        ]);

        $this->assertDatabaseCount('capabilities', 1);
        $this->assertDatabaseCount('assigned_capabilities', 1);
    }

    public function test_can_create_direct_capability()
    {
        /** @var Role $role */
        $role = Role::factory()->create();

        $role->capability(Capability::Access)->create();

        $this->assertDatabaseHas('capabilities', [
            'name' => 'viewAny',
        ]);

        $this->assertDatabaseCount('capabilities', 1);
        $this->assertDatabaseCount('assigned_capabilities', 0);
    }

    public function test_can_create_model_capability()
    {

        /** @var Role $role */
        $role = Role::factory()->create();

        $role->capability(Capability::Access, Post::class)->create();

        $this->assertDatabaseHas('capabilities', [
            'name' => 'post.viewAny',
        ]);

        $this->assertDatabaseCount('capabilities', 1);
        $this->assertDatabaseCount('assigned_capabilities', 0);
    }

    public function test_can_create_record_capability()
    {

        /** @var Role $role */
        $role = Role::factory()->create();
        /** @var Document $document */
        $document = Document::factory()->create();

        $role->capability(Capability::Access, $document)->create();

        $this->assertDatabaseHas('capabilities', [
            'name' => 'document.viewAny.' . $document->getKey(),
        ]);

        $this->assertDatabaseCount('capabilities', 1);
        $this->assertDatabaseCount('assigned_capabilities', 0);
    }

    public function test_can_create_multiple_capabilities()
    {

        /** @var Role $role */
        $role = Role::factory()->create();

        //        $role->capability()
    }

    public function test_can_assign_same_capability_to_multiple_roles()
    {
        /** @var Role $role */
        $role1 = Role::factory()->create();
        /** @var Role $role */
        $role2 = Role::factory()->create();

        $role1->capability('same-capability')->createIfNotExists()->assign();
        $role1->capability('another-capability')->createIfNotExists()->assign();

        $role2->capability('same-capability')->createIfNotExists()->assign();

        $this->assertDatabaseHas('assigned_capabilities', [
            'capability_id' => \Guava\Capabilities\Models\Capability::firstWhere(
                'name',
                'same-capability',
            )->getKey(),
            'assignee_type' => $role1->getMorphClass(),
            'assignee_id' => $role1->getKey(),
        ]);

        $this->assertDatabaseHas('assigned_capabilities', [
            'capability_id' => \Guava\Capabilities\Models\Capability::firstWhere(
                'name',
                'another-capability',
            )->getKey(),
            'assignee_type' => $role1->getMorphClass(),
            'assignee_id' => $role1->getKey(),
        ]);

        $this->assertDatabaseHas('assigned_capabilities', [
            'capability_id' => \Guava\Capabilities\Models\Capability::firstWhere(
                'name',
                'same-capability',
            )->getKey(),
            'assignee_type' => $role2->getMorphClass(),
            'assignee_id' => $role2->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 2);
        $this->assertDatabaseCount('capabilities', 2);
        $this->assertDatabaseCount('assigned_capabilities', 3);
    }

    public function test_assigned_capabilities_do_not_duplicate()
    {
        /** @var Role $role1 */
        $role1 = Role::factory()->create();
        /** @var Role $role2 */
        $role2 = Role::factory()->create();

        $role1->capability('unique-capability')->createIfNotExists()->assign();
        // Assign again
        $role1->capability('unique-capability')->createIfNotExists()->assign();

        $role2->capability('unique-capability')->createIfNotExists()->assign();

        $this->assertDatabaseHas('assigned_capabilities', [
            'capability_id' => \Guava\Capabilities\Models\Capability::firstWhere(
                'name',
                'unique-capability',
            )->getKey(),
            'assignee_type' => $role1->getMorphClass(),
            'assignee_id' => $role1->getKey(),
        ]);

        $this->assertDatabaseHas('assigned_capabilities', [
            'capability_id' => \Guava\Capabilities\Models\Capability::firstWhere(
                'name',
                'unique-capability',
            )->getKey(),
            'assignee_type' => $role2->getMorphClass(),
            'assignee_id' => $role2->getKey(),
        ]);

        $this->assertDatabaseCount('roles', 2);
        $this->assertDatabaseCount('capabilities', 1);
        $this->assertDatabaseCount('assigned_capabilities', 2);
    }
}
