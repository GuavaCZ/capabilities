<?php

namespace Tests\Feature;

use Guava\Capabilities\Capability;
use Tests\Fixtures\Models\Document;
use Tests\Fixtures\Models\Post;
use Tests\Fixtures\Models\Role;
use Tests\Fixtures\Models\Tenant;
use Tests\Fixtures\Models\User;
use Tests\Fixtures\Roles\AdminRole;
use Tests\Fixtures\Roles\MemberRole;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    public function test_can_assign_role()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->assignRole($role);

        $this->assertTrue($user->assignedRoles->contains($role));
    }

    public function test_can_not_assign_duplicate_roles()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $user->assignRole($role);
        $user->assignRole($role);

        $this->assertTrue($user->assignedRoles->contains($role));
        $this->assertCount(1, $user->assignedRoles);
    }

    public function test_user_can_have_direct_model_permissions(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();

        $role->assignCapability(Capability::all(Post::class));
        $user->assignRole($role);

        foreach (Capability::cases() as $capability) {
            $this->assertTrue($user->hasCapability($capability->get(Post::class)));
        }
    }

    public function test_user_can_have_direct_record_permissions_via_record_permissions()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        $role->assignCapability(Capability::all($post1));
        $user->assignRole($role);

        foreach (Capability::cases() as $capability) {
            $this->assertTrue($user->hasCapability($capability->get($post1)));
            $this->assertFalse($user->hasCapability($capability->get($post2)));
        }
    }

    public function test_user_can_have_direct_record_permissions_via_model_permissions()
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        $role->assignCapability(Capability::all($post1));
        $user->assignRole($role);

        foreach (Capability::cases() as $capability) {
            $this->assertTrue($user->hasCapability($capability->get($post1)));
            $this->assertFalse($user->hasCapability($capability->get($post2)));
        }
    }

    public function test_user_can_have_direct_tenant_model_permissions(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $tenant = Tenant::factory()->create();
        $role->assignCapability(Capability::all(Document::class), $tenant);
        $user->assignRole($role);

        foreach (Capability::cases() as $capability) {
            $this->assertTrue($user->hasCapability($capability->get(Document::class), $tenant));
            $this->assertFalse($user->hasCapability($capability->get(Document::class)));
        }
    }

    public function test_user_can_have_direct_tenant_record_permissions_via_record_permissions(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $tenant = Tenant::factory()->create();
        $document1 = Document::factory()->create();
        $document2 = Document::factory()->create();
        $role->assignCapability(Capability::all($document1), $tenant);
        $user->assignRole($role);

        foreach (Capability::cases() as $capability) {
            $this->assertTrue($user->hasCapability($capability->get($document1), $tenant));
            $this->assertFalse($user->hasCapability($capability->get($document1)));
            $this->assertFalse($user->hasCapability($capability->get($document2), $tenant));
            $this->assertFalse($user->hasCapability($capability->get($document2)));
        }
    }

    public function test_user_can_have_direct_tenant_record_permissions_via_model_permissions(): void
    {
        $user = User::factory()->create();
        $role = Role::factory()->create();
        $tenant = Tenant::factory()->create();
        $document1 = Document::factory()->create();
        $document2 = Document::factory()->create();
        $role->assignCapability(Capability::all(Document::class), $tenant);
        $user->assignRole($role);

        foreach (Capability::cases() as $capability) {
            $this->assertTrue($user->hasCapability($capability->get($document1), $tenant));
            $this->assertFalse($user->hasCapability($capability->get($document1)));
            $this->assertTrue($user->hasCapability($capability->get($document2), $tenant));
            $this->assertFalse($user->hasCapability($capability->get($document2)));
        }
    }

    public function test_user_can_have_record_permissions_via_preset_role_admin()
    {
        $user = User::factory()->create();
        $document = Document::factory()->create();
        $post = Post::factory()->create();
        $user->assignRole(new AdminRole);

        foreach (Capability::cases() as $capability) {
            $this->assertTrue($user->hasCapability($capability->get($document)));
            $this->assertTrue($user->hasCapability($capability->get($post)));
        }
    }

    public function test_user_can_have_record_permissions_via_preset_role_member()
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $document = Document::factory()->create();
        $post = Post::factory()->create();
        $user->assignRole(new MemberRole, $tenant);

        foreach (Capability::cases() as $capability) {
            $this->assertFalse($user->hasCapability($capability->get($document)));
            match ($capability) {
                Capability::View,Capability::Create,Capability::Update => $this->assertTrue($user->hasCapability($capability->get($post), $tenant)),
                default => $this->assertFalse($user->hasCapability($capability->get($post), $tenant)),
            };
        }
    }
}
