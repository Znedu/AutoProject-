<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected Role $customerRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);

        $this->customerRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Customer->value],
            ['name' => 'Customer', 'description' => 'Customer Role']
        );
    }

    public function test_forgot_password_screen_can_be_rendered(): void
    {
        $response = $this->get(route('password.request'));

        $response->assertStatus(200);
        $response->assertSee('Forgot Password?');
    }

    public function test_reset_password_link_can_be_requested_for_active_user(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_id' => $this->customerRole->id,
            'status'  => User::STATUS_ACTIVE,
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('success');
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_with_unknown_email_returns_same_success_without_notification(): void
    {
        Notification::fake();

        $response = $this->post(route('password.email'), [
            'email' => 'unknown@example.com',
        ]);

        $response->assertSessionHas('success');
        Notification::assertNothingSent();
    }

    public function test_forgot_password_with_inactive_user_returns_same_success_without_notification(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_id' => $this->customerRole->id,
            'status'  => User::STATUS_INACTIVE,
        ]);

        $response = $this->post(route('password.email'), [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('success');
        Notification::assertNothingSent();
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->customerRole->id,
        ]);

        $token = Password::createToken($user);

        $response = $this->get(route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Reset Your Password');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->customerRole->id,
            'password' => Hash::make('oldpassword123'),
        ]);

        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));

        // Verify user can now log in with new password
        $loginResponse = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'newpassword123',
        ]);

        $loginResponse->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->customerRole->id,
        ]);

        $response = $this->post(route('password.update'), [
            'token'                 => 'invalid-token',
            'email'                 => $user->email,
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertFalse(Hash::check('newpassword123', $user->fresh()->password));
    }

    public function test_password_reset_requires_matching_confirmation(): void
    {
        $user = User::factory()->create([
            'role_id' => $this->customerRole->id,
        ]);

        $token = Password::createToken($user);

        $response = $this->post(route('password.update'), [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => 'newpassword123',
            'password_confirmation' => 'mismatch123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_rapid_reset_requests_are_throttled(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_id' => $this->customerRole->id,
            'status'  => User::STATUS_ACTIVE,
        ]);

        // First request sends notification
        $this->post(route('password.email'), ['email' => $user->email]);
        Notification::assertSentTo($user, ResetPasswordNotification::class);

        Notification::fake();

        // Immediate second request gets throttled
        $response = $this->post(route('password.email'), ['email' => $user->email]);
        $response->assertSessionHas('error');
        Notification::assertNothingSent();
    }
}
