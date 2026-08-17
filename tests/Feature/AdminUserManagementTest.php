<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Role $adminRole;

    protected Role $staffRole;

    protected Role $customerRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

        $this->adminRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Administrator->value],
            ['name' => 'Administrator', 'description' => 'Admin Role']
        );

        $this->staffRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Staff->value],
            ['name' => 'Staff', 'description' => 'Staff Role']
        );

        $this->customerRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Customer->value],
            ['name' => 'Customer', 'description' => 'Customer Role']
        );

        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'status'  => User::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_can_view_users_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('User Management');
    }

    public function test_admin_can_create_new_user(): void
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.users.store'), [
            'name'                  => 'John Staff',
            'email'                 => 'johnstaff@example.com',
            'phone'                 => '09123456789',
            'role'                  => RoleSlug::Staff->value,
            'status'                => 'active',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'email'   => 'johnstaff@example.com',
            'role_id' => $this->staffRole->id,
        ]);
    }

    public function test_admin_can_edit_existing_user(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->customerRole->id,
            'name'    => 'Old Name',
            'email'   => 'oldemail@example.com',
            'status'  => User::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($this->admin)->putJson(route('admin.users.update', $user), [
            'name'   => 'Updated Name',
            'email'  => 'newemail@example.com',
            'phone'  => '09987654321',
            'role'   => RoleSlug::Staff->value,
            'status' => 'inactive',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'id'      => $user->id,
            'name'    => 'Updated Name',
            'email'   => 'newemail@example.com',
            'role_id' => $this->staffRole->id,
            'status'  => User::STATUS_INACTIVE,
        ]);
    }

    public function test_admin_can_delete_another_user(): void
    {
        $otherUser = User::factory()->create([
            'role_id' => $this->customerRole->id,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson(route('admin.users.destroy', $otherUser));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertSoftDeleted('users', [
            'id' => $otherUser->id,
        ]);
    }

    public function test_admin_cannot_delete_their_own_account(): void
    {
        $response = $this->actingAs($this->admin)->deleteJson(route('admin.users.destroy', $this->admin));

        $response->assertStatus(403);
        $response->assertJson(['success' => false]);

        $this->assertDatabaseHas('users', [
            'id'         => $this->admin->id,
            'deleted_at' => null,
        ]);
    }
}
