<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\EmailVerificationCode;
use App\Models\Role;
use App\Models\User;
use App\Notifications\Auth\EmailVerificationCodeNotification;
use App\Services\Auth\EmailVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
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

    public function test_user_registration_sends_verification_notification_and_redirects_to_notice(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name'                  => 'Test Customer',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertGuest();
        $this->assertEquals('test@example.com', session('verification_email'));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo($user, EmailVerificationCodeNotification::class);
        $this->assertDatabaseHas('email_verification_codes', ['user_id' => $user->id]);
    }

    public function test_unverified_customer_is_redirected_away_from_customer_dashboard(): void
    {
        $unverifiedUser = User::factory()->create([
            'role_id'           => $this->customerRole->id,
            'email_verified_at' => null,
        ]);

        $response = $this->actingAs($unverifiedUser)->get(route('customer.dashboard'));

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_verified_customer_can_access_customer_dashboard(): void
    {
        $verifiedUser = User::factory()->create([
            'role_id'           => $this->customerRole->id,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($verifiedUser)->get(route('customer.dashboard'));

        $response->assertStatus(200);
    }

    public function test_customer_can_verify_email_with_valid_otp_code(): void
    {
        $user = User::factory()->create([
            'role_id'           => $this->customerRole->id,
            'email_verified_at' => null,
        ]);

        $code = '123456';
        EmailVerificationCode::create([
            'user_id'    => $user->id,
            'code'       => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
            'attempts'   => 0,
        ]);

        $response = $this->actingAs($user)->post(route('verification.verify'), [
            'code' => $code,
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing('email_verification_codes', ['user_id' => $user->id]);
    }

    public function test_invalid_otp_code_increments_attempts(): void
    {
        $user = User::factory()->create([
            'role_id'           => $this->customerRole->id,
            'email_verified_at' => null,
        ]);

        EmailVerificationCode::create([
            'user_id'    => $user->id,
            'code'       => Hash::make('123456'),
            'expires_at' => now()->addMinutes(15),
            'attempts'   => 0,
        ]);

        $response = $this->actingAs($user)->post(route('verification.verify'), [
            'code' => '654321',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertDatabaseHas('email_verification_codes', [
            'user_id'  => $user->id,
            'attempts' => 1,
        ]);
    }

    public function test_code_invalidated_after_5_failed_attempts(): void
    {
        $user = User::factory()->create([
            'role_id'           => $this->customerRole->id,
            'email_verified_at' => null,
        ]);

        EmailVerificationCode::create([
            'user_id'    => $user->id,
            'code'       => Hash::make('123456'),
            'expires_at' => now()->addMinutes(15),
            'attempts'   => 4,
        ]);

        $response = $this->actingAs($user)->post(route('verification.verify'), [
            'code' => '654321',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertDatabaseMissing('email_verification_codes', ['user_id' => $user->id]);
    }

    public function test_expired_code_is_rejected(): void
    {
        $user = User::factory()->create([
            'role_id'           => $this->customerRole->id,
            'email_verified_at' => null,
        ]);

        EmailVerificationCode::create([
            'user_id'    => $user->id,
            'code'       => Hash::make('123456'),
            'expires_at' => now()->subMinute(),
            'attempts'   => 0,
        ]);

        $response = $this->actingAs($user)->post(route('verification.verify'), [
            'code' => '123456',
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_resend_cooldown_enforced(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role_id'           => $this->customerRole->id,
            'email_verified_at' => null,
        ]);

        // Code issued just now
        EmailVerificationCode::create([
            'user_id'    => $user->id,
            'code'       => Hash::make('123456'),
            'expires_at' => now()->addMinutes(15),
            'attempts'   => 0,
        ]);

        $response = $this->actingAs($user)->post(route('verification.resend'));

        $response->assertSessionHas('error');
        Notification::assertNothingSent();
    }

    public function test_login_prompts_unverified_customer_to_reregister(): void
    {
        $user = User::factory()->create([
            'role_id'           => $this->customerRole->id,
            'email'             => 'unverified@example.com',
            'password'          => 'password123',
            'email_verified_at' => null,
        ]);

        $response = $this->post('/login', [
            'email'    => 'unverified@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_walk_in_customer_account_is_auto_verified(): void
    {
        $walkinUser = User::create([
            'name'              => 'Walk-in Customer',
            'email'             => 'walkin@example.com',
            'phone'             => '09123456789',
            'role_id'           => $this->customerRole->id,
            'status'            => User::STATUS_ACTIVE,
            'password'          => 'password123',
            'email_verified_at' => now(),
        ]);

        $this->assertNotNull($walkinUser);
        $this->assertTrue($walkinUser->hasVerifiedEmail());
        $this->assertNotNull($walkinUser->email_verified_at);
    }

    public function test_unverified_user_can_re_register_and_receives_fresh_otp(): void
    {
        Notification::fake();

        $unverifiedUser = User::factory()->create([
            'role_id'           => $this->customerRole->id,
            'name'              => 'Old Name',
            'email'             => 'pending@example.com',
            'email_verified_at' => null,
        ]);

        $response = $this->post('/register', [
            'name'                  => 'New Name',
            'email'                 => 'pending@example.com',
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertGuest();
        $this->assertEquals('pending@example.com', session('verification_email'));

        $unverifiedUser->refresh();
        $this->assertEquals('New Name', $unverifiedUser->name);
        $this->assertNull($unverifiedUser->email_verified_at);

        Notification::assertSentTo($unverifiedUser, EmailVerificationCodeNotification::class);
    }

    public function test_verified_user_cannot_re_register_with_same_email(): void
    {
        User::factory()->create([
            'role_id'           => $this->customerRole->id,
            'email'             => 'verified@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/register', [
            'name'                  => 'Another User',
            'email'                 => 'verified@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_user_can_cancel_verification_and_clears_session(): void
    {
        session(['verification_email' => 'pending@example.com']);

        $response = $this->get(route('verification.cancel'));

        $response->assertRedirect('/');
        $this->assertFalse(session()->has('verification_email'));
    }
}
