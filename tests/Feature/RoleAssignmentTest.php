<?php

namespace Tests\Feature;

use Guava\Capabilities\Auth\Roles;
use Tests\Fixtures\Models\User;
use Tests\Fixtures\Roles\AdminRole;
use Tests\TestCase;

class RoleAssignmentTest extends TestCase
{
    public function test_can_assign_direct_role()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $role = Roles::get('test');

        $this->assertDatabaseHas('roles', [
            'name' => 'test',
        ]);
        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => $role->id,
            'assignee_type' => User::class,
            'assignee_id' => $user->id,
        ]);
    }

    public function test_can_assign_predefined_role()
    {

        /** @var User $user */
        $user = User::factory()->create();

        $role = Roles::get(AdminRole::class);
        $user->assignRole($role);

        $correctData = new AdminRole();
        $this->assertDatabaseHas('roles', [
            'name' => $correctData->name(),
            'title' => $correctData->title(),
        ]);

        $this->assertDatabaseHas('assigned_roles', [
            'role_id' => $role->id,
            'assignee_type' => User::class,
            'assignee_id' => $user->id,
        ]);

        foreach ($correctData->capabilities() as $capability) {
            $this->assertDatabaseHas('assigned_capabilities', [
                'capability_id' => $capability->id,
                'assignee_type' => User::class,
                'assignee_id' => $user->id,
            ]);
        }
    }
}
