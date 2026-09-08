<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Enums\SmsStatus;
use App\Models\Booking;
use App\Models\Role;
use App\Models\SmsOutbox;
use App\Models\User;
use App\Notifications\Booking\BookingApprovedNotification;
use App\Notifications\Booking\NewBookingNotification;
use App\Services\Notification\SmsGateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsGateIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected string $testLogin = 'sms-user-123';
    protected string $testPassword = 'sms-pass-456';
    protected string $baseUrl = 'https://api.sms-gate.app/3rdparty/v1';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.smsgate.enabled'  => true,
            'services.smsgate.base_url' => $this->baseUrl,
            'services.smsgate.login'    => $this->testLogin,
            'services.smsgate.password' => $this->testPassword,
        ]);

        $customerRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Customer->value],
            ['name' => 'Customer', 'description' => 'Customer']
        );

        $adminRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Administrator->value],
            ['name' => 'Admin', 'description' => 'Admin']
        );

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'phone'   => '+63 917 123 4567', // includes spaces to test sanitization
            'status'  => User::STATUS_ACTIVE,
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'phone'   => '+639179998888',
            'status'  => User::STATUS_ACTIVE,
        ]);
    }

    public function test_send_pushes_message_to_sms_gateway_with_basic_auth(): void
    {
        Http::fake([
            'api.sms-gate.app/3rdparty/v1/message' => Http::response([
                'id'    => 'gw-msg-uuid-999',
                'state' => 'Pending',
            ], 200),
        ]);

        $service = app(SmsGateService::class);
        $outbox = $service->send('+639171234567', 'Hello from AutoProject+');

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://api.sms-gate.app/3rdparty/v1/message'
                && $request->hasHeader('Authorization', 'Basic ' . base64_encode("{$this->testLogin}:{$this->testPassword}"))
                && $request['message'] === 'Hello from AutoProject+'
                && $request['phoneNumbers'] === ['+639171234567'];
        });

        $this->assertSame(SmsStatus::SENT, $outbox->status);
        $this->assertSame('gw-msg-uuid-999', $outbox->gateway_id);
        $this->assertNotNull($outbox->sent_at);

        $this->assertDatabaseHas('sms_outbox', [
            'id'         => $outbox->id,
            'to'         => '+639171234567',
            'status'     => SmsStatus::SENT->value,
            'gateway_id' => 'gw-msg-uuid-999',
        ]);
    }

    public function test_send_handles_api_failure_gracefully(): void
    {
        Http::fake([
            'api.sms-gate.app/3rdparty/v1/message' => Http::response([
                'error' => 'Device offline',
            ], 500),
        ]);

        $service = app(SmsGateService::class);
        $outbox = $service->send('+639171234567', 'Hello failure test');

        $this->assertSame(SmsStatus::FAILED, $outbox->status);
        $this->assertStringContainsString('500', $outbox->error_message);
        $this->assertNull($outbox->gateway_id);
        $this->assertNull($outbox->sent_at);

        $this->assertDatabaseHas('sms_outbox', [
            'id'     => $outbox->id,
            'status' => SmsStatus::FAILED->value,
        ]);
    }

    public function test_send_skips_http_when_service_is_disabled(): void
    {
        config(['services.smsgate.enabled' => false]);
        Http::fake();

        $service = app(SmsGateService::class);
        $outbox = $service->send('+639171234567', 'Disabled service test');

        Http::assertNothingSent();
        $this->assertSame(SmsStatus::PENDING, $outbox->status);
    }

    public function test_customer_notification_triggers_sms_gate_with_sanitized_phone(): void
    {
        Http::fake([
            'api.sms-gate.app/3rdparty/v1/message' => Http::response([
                'id'    => 'gw-msg-booking-approved',
                'state' => 'Pending',
            ], 200),
        ]);

        $booking = new Booking();
        $booking->id = 1;
        $booking->booking_number = 'BK-1001';
        $booking->user_id = $this->customer->id;

        // Send customer-facing notification
        $this->customer->notify(new BookingApprovedNotification($booking));

        Http::assertSent(function (Request $request) {
            return $request['phoneNumbers'] === ['+639171234567']
                && str_contains($request['message'], 'BK-1001')
                && str_contains(strtolower($request['message']), 'approved');
        });

        $this->assertDatabaseHas('sms_outbox', [
            'to'         => '+639171234567',
            'status'     => SmsStatus::SENT->value,
            'gateway_id' => 'gw-msg-booking-approved',
        ]);
    }

    public function test_customer_notification_falls_back_to_booking_contact_number(): void
    {
        Http::fake([
            'api.sms-gate.app/3rdparty/v1/message' => Http::response([
                'id'    => 'gw-msg-fallback',
                'state' => 'Pending',
            ], 200),
        ]);

        $customerWithoutPhone = User::factory()->create([
            'role_id' => $this->customer->role_id,
            'phone'   => null,
            'status'  => User::STATUS_ACTIVE,
        ]);

        $booking = new Booking();
        $booking->id = 55;
        $booking->booking_number = 'BK-5555';
        $booking->contact_number = '09223334444'; // Local 09 format
        $booking->user_id = $customerWithoutPhone->id;

        $customerWithoutPhone->notify(new BookingApprovedNotification($booking));

        Http::assertSent(function (Request $request) {
            return $request['phoneNumbers'] === ['+639223334444'];
        });

        $this->assertDatabaseHas('sms_outbox', [
            'to'         => '+639223334444',
            'status'     => SmsStatus::SENT->value,
            'gateway_id' => 'gw-msg-fallback',
        ]);
    }

    public function test_admin_notification_does_not_trigger_sms(): void
    {
        Http::fake();

        $booking = new Booking();
        $booking->id = 2;
        $booking->booking_number = 'BK-1002';
        $booking->customer_name = 'John Doe';

        $this->admin->notify(new NewBookingNotification($booking));

        Http::assertNothingSent();
        $this->assertDatabaseCount('sms_outbox', 0);
    }

    public function test_broadcast_sends_to_multiple_recipients(): void
    {
        Http::fake([
            'api.sms-gate.app/3rdparty/v1/message' => Http::response([
                'id'    => 'gw-msg-broadcast',
                'state' => 'Pending',
            ], 200),
        ]);

        $service = app(SmsGateService::class);
        $result = $service->broadcast(['09171112222', '+63 918 333 4444'], 'Broadcast test message');

        $this->assertSame(2, $result['success']);
        $this->assertSame(0, $result['failed']);

        $this->assertDatabaseHas('sms_outbox', ['to' => '+639171112222', 'status' => SmsStatus::SENT->value]);
        $this->assertDatabaseHas('sms_outbox', ['to' => '+639183334444', 'status' => SmsStatus::SENT->value]);
    }

    public function test_clean_sent_purges_old_sent_messages(): void
    {
        // Sent 35 days ago (should be cleaned)
        $oldSent = SmsOutbox::create([
            'to'      => '+639171234567',
            'body'    => 'Old sent message',
            'status'  => SmsStatus::SENT,
            'sent_at' => Carbon::now()->subDays(35),
        ]);

        // Sent 5 days ago (should remain)
        $recentSent = SmsOutbox::create([
            'to'      => '+639171234567',
            'body'    => 'Recent sent message',
            'status'  => SmsStatus::SENT,
            'sent_at' => Carbon::now()->subDays(5),
        ]);

        $service = app(SmsGateService::class);
        $purgedCount = $service->cleanSent(30);

        $this->assertSame(1, $purgedCount);
        $this->assertDatabaseMissing('sms_outbox', ['id' => $oldSent->id]);
        $this->assertDatabaseHas('sms_outbox', ['id' => $recentSent->id]);
    }
}
