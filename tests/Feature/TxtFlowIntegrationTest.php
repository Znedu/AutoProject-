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
use App\Services\Notification\TxtFlowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TxtFlowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected string $secretToken = 'test-secret-token-12345';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.txtflow.enabled' => true,
            'services.txtflow.token'   => $this->secretToken,
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
            'phone'   => '+639171234567',
            'status'  => User::STATUS_ACTIVE,
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'phone'   => '+639179998888',
            'status'  => User::STATUS_ACTIVE,
        ]);
    }

    public function test_health_check_requires_valid_token(): void
    {
        // Missing token
        $response = $this->get('/api/txtflow/health-check');
        $response->assertStatus(401);

        // Invalid token
        $response = $this->get('/api/txtflow/health-check?token=wrong-token');
        $response->assertStatus(401);

        // Valid token
        $response = $this->get('/api/txtflow/health-check?token=' . $this->secretToken);
        $response->assertStatus(200);
        $this->assertSame('OK', $response->getContent());
    }

    public function test_customer_notification_queues_sms_in_outbox(): void
    {
        $booking = new Booking();
        $booking->id = 1;
        $booking->booking_number = 'BK-1001';
        $booking->user_id = $this->customer->id;

        // Send customer-facing notification
        $this->customer->notify(new BookingApprovedNotification($booking));

        // Assert record exists in database
        $this->assertDatabaseHas('sms_outbox', [
            'to'     => '+639171234567',
            'status' => SmsStatus::PENDING->value,
        ]);

        $queued = SmsOutbox::first();
        $this->assertStringContainsString('BK-1001', $queued->body);
        $this->assertStringContainsString('approved', strtolower($queued->body));
    }

    public function test_admin_notification_does_not_queue_sms(): void
    {
        $booking = new Booking();
        $booking->id = 2;
        $booking->booking_number = 'BK-1002';
        $booking->customer_name = 'John Doe';

        // NewBookingNotification has $sendSms = false and admin is not customer
        $this->admin->notify(new NewBookingNotification($booking));

        $this->assertDatabaseCount('sms_outbox', 0);
    }

    public function test_messages_endpoint_returns_pending_messages(): void
    {
        SmsOutbox::create([
            'to'     => '+639171234567',
            'body'   => 'Test SMS 1',
            'status' => SmsStatus::PENDING,
        ]);

        SmsOutbox::create([
            'to'     => '+639171234568',
            'body'   => 'Test SMS 2',
            'status' => SmsStatus::SENT,
        ]);

        $response = $this->getJson('/api/txtflow/messages?token=' . $this->secretToken);

        $response->assertStatus(200);
        $data = $response->json();

        // Only the pending one should be returned
        $this->assertCount(1, $data);
        $this->assertSame('+639171234567', $data[0]['to']);
        $this->assertSame('Test SMS 1', $data[0]['body']);
    }

    public function test_receive_delivery_report_marks_outbox_as_sent(): void
    {
        $item = SmsOutbox::create([
            'to'     => '+639171234567',
            'body'   => 'Test pending message',
            'status' => SmsStatus::PENDING,
        ]);

        $response = $this->postJson('/api/txtflow/message?token=' . $this->secretToken, [
            'type'      => 'delivery_report',
            'id'        => (string) $item->id,
            'from'      => '+639171234567',
            'timestamp' => time(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $item->refresh();
        $this->assertSame(SmsStatus::SENT, $item->status);
        $this->assertNotNull($item->sent_at);
    }

    public function test_receive_incoming_sms_reply(): void
    {
        $response = $this->postJson('/api/txtflow/message?token=' . $this->secretToken, [
            'type'      => 'incoming',
            'from'      => '+639171234567',
            'body'      => 'Thank you, confirmed!',
            'timestamp' => time(),
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);
    }

    public function test_clean_endpoint_purges_old_sent_messages(): void
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

        $response = $this->postJson('/api/txtflow/cron/clean?token=' . $this->secretToken);

        $response->assertStatus(200);
        $response->assertJson([
            'status'  => 'success',
            'deleted' => 1,
        ]);

        $this->assertDatabaseMissing('sms_outbox', ['id' => $oldSent->id]);
        $this->assertDatabaseHas('sms_outbox', ['id' => $recentSent->id]);
    }
}
