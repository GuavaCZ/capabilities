<?php

namespace Tests\Feature;

use Guava\Capabilities\Capability;
use Tests\Fixtures\Models\Document;
use Tests\Fixtures\Models\Post;
use Tests\Fixtures\Models\Tenant;
use Tests\Fixtures\Models\User;
use Tests\TestCase;

class UserPermissionsTest extends TestCase
{
    public function test_will_not_create_duplicate_direct_model_permissions()
    {
        $user = User::factory()->create();
        $user->assignCapability(Capability::all(Post::class));
        $user->assignCapability(Capability::all(Post::class));
        $this->assertCount(count(Capability::cases()), $user->assignedCapabilities);
    }

    public function test_will_not_create_duplicate_direct_record_permissions()
    {
        $user = User::factory()->create();
        $post1 = Post::factory()->create();
        $user->assignCapability(Capability::all($post1));
        $user->assignCapability(Capability::all($post1));
        $this->assertCount(count(Capability::cases()), $user->assignedCapabilities);

        $post2 = Post::factory()->create();
        $user->assignCapability(Capability::all($post2));
        $this->assertCount(count(Capability::cases()) * 2, $user->refresh()->assignedCapabilities);
    }

    public function test_user_can_have_direct_model_permissions(): void
    {
        $user = User::factory()->create();
        $user->assignCapability(Capability::all(Post::class));

        foreach (Capability::cases() as $capability) {
            $this->assertTrue($user->hasCapability($capability->get(Post::class)));
        }
    }

    public function test_user_can_have_direct_record_permissions_via_record_permissions()
    {
        $user = User::factory()->create();
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        $user->assignCapability(Capability::all($post1));

        foreach (Capability::cases() as $capability) {
            $this->assertTrue($user->hasCapability($capability->get($post1)));
            $this->assertFalse($user->hasCapability($capability->get($post2)));
        }
    }

    public function test_user_can_have_direct_record_permissions_via_model_permissions()
    {
        $user = User::factory()->create();
        $post1 = Post::factory()->create();
        $post2 = Post::factory()->create();
        $user->assignCapability(Capability::all($post1));

        foreach (Capability::cases() as $capability) {
            $this->assertTrue($user->hasCapability($capability->get($post1)));
            $this->assertFalse($user->hasCapability($capability->get($post2)));
        }
    }

    public function test_user_can_have_direct_tenant_model_permissions(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $user->assignCapability(Capability::all(Document::class), $tenant);

        foreach (Capability::cases() as $capability) {
            $this->assertTrue($user->hasCapability($capability->get(Document::class), $tenant));
            $this->assertFalse($user->hasCapability($capability->get(Document::class)));
        }
    }

    public function test_user_can_have_direct_tenant_record_permissions_via_record_permissions(): void
    {
        $user = User::factory()->create();
        $tenant = Tenant::factory()->create();
        $document1 = Document::factory()->create();
        $document2 = Document::factory()->create();
        $user->assignCapability(Capability::all($document1), $tenant);

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
        $tenant = Tenant::factory()->create();
        $document1 = Document::factory()->create();
        $document2 = Document::factory()->create();
        $user->assignCapability(Capability::all(Document::class), $tenant);

        foreach (Capability::cases() as $capability) {
            $this->assertTrue($user->hasCapability($capability->get($document1), $tenant));
            $this->assertFalse($user->hasCapability($capability->get($document1)));
            $this->assertTrue($user->hasCapability($capability->get($document2), $tenant));
            $this->assertFalse($user->hasCapability($capability->get($document2)));
        }
    }
}
