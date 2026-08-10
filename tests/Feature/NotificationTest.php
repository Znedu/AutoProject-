<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Booking;
use App\Models\JobOrder;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceUpdate;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\Booking\BookingConfirmedNotification;
use App\Notifications\Job\JobAssignedNotification;
use App\Notifications\Job\JobOrderCreatedNotification;
use App\Notifications\Job\ServiceUpdateNotification;
use App\Notifications\Payment\PaymentRejectedNotification;
use App\Notifications\Support\TicketCreatedNotification;
use App\Notifications\Support\TicketReplyNotification;
use App\Services\Booking\JobAssignmentService;
use App\Services\Booking\PaymentVerificationService;
use App\Services\Notification\NotificationDispatcherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected User $staff;
    protected User $mechanic;
    protected Role $customerRole;
    protected Role $adminRole;
    protected Role $staffRole;
    protected Role $mechanicRole;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Customer->value],
            ['name' => 'Customer', 'description' => 'Customer Role']
        );

        $this->adminRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Administrator->value],
            ['name' => 'Administrator', 'description' => 'Admin Role']
        );

        $this->staffRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Staff->value],
            ['name' => 'Staff', 'description' => 'Staff Role']
        );

        $this->mechanicRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Mechanic->value],
            ['name' => 'Mechanic', 'description' => 'Mechanic Role']
        );

        $approvalsPerm = Permission::firstOrCreate(
            ['slug' => 'approvals.manage'],
            ['name' => 'Manage Approvals', 'description' => 'Manage Approvals']
        );

        $supportPerm = Permission::firstOrCreate(
            ['slug' => 'support.view'],
            ['name' => 'Support View', 'description' => 'Support View']
        );

        $notesPerm = Permission::firstOrCreate(
            ['slug' => 'service-notes.view'],
            ['name' => 'Service Notes', 'description' => 'Service Notes']
        );

        $this->adminRole->permissions()->syncWithoutDetaching([$approvalsPerm->id]);
        $this->staffRole->permissions()->syncWithoutDetaching([$supportPerm->id]);
        $this->mechanicRole->permissions()->syncWithoutDetaching([$notesPerm->id]);

        $this->customer = User::factory()->create([
            'role_id' => $this->customerRole->id,
            'status'  => User::STATUS_ACTIVE,
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'status'  => User::STATUS_ACTIVE,
        ]);

        $this->staff = User::factory()->create([
            'role_id' => $this->staffRole->id,
            'status'  => User::STATUS_ACTIVE,
        ]);

        $this->mechanic = User::factory()->create([
            'role_id' => $this->mechanicRole->id,
            'status'  => User::STATUS_ACTIVE,
        ]);

        $this->vehicle = Vehicle::create([
            'user_id'      => $this->customer->id,
            'make'         => 'Honda',
            'model'        => 'Civic',
            'year'         => 2022,
            'plate_number' => 'XYZ9876',
        ]);
    }

    public function test_customer_online_booking_sends_new_booking_notification_to_admin(): void
    {
        Notification::fake();

        $category = \App\Models\ServiceCategory::create([
            'name'       => 'General Maintenance',
            'slug'       => 'general-maintenance',
            'status'     => 'active',
            'sort_order' => 1,
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'code'                => 'SVC-OIL-01',
            'name'                => 'Oil Change',
            'slug'                => 'oil-change',
            'status'              => 'active',
            'min_cost'            => 500,
            'max_cost'            => 1500,
        ]);

        $bookingData = [
            'service_ids'      => [$service->id],
            'customer_name'    => $this->customer->name,
            'contact_number'   => '09123456789',
            'vehicle_make'     => 'Honda',
            'vehicle_model'    => 'Civic',
            'vehicle_year'     => 2022,
            'plate_number'     => 'ABC9999',
            'preferred_date'   => today()->addDays(3)->format('Y-m-d'),
            'preferred_time'   => '10:00:00',
            'payment_method'   => 'gcash',
            'reference_number' => 'REF-NEW-BOOKING-001',
        ];

        $creator = app(\App\Services\Booking\BookingCreatorService::class);
        $creator->create($this->customer, $bookingData);

        Notification::assertSentTo($this->admin, \App\Notifications\Booking\NewBookingNotification::class);
    }

    public function test_payment_confirm_sends_booking_confirmed_and_job_order_created_notifications(): void
    {
        Notification::fake();

        $booking = Booking::create([
            'booking_number'    => 'BK-CONFIRM-001',
            'user_id'           => $this->customer->id,
            'vehicle_id'        => $this->vehicle->id,
            'status'            => Booking::STATUS_PENDING_PAYMENT_VERIFICATION,
            'preferred_date'    => today()->addDays(2),
            'preferred_time'    => '10:00:00',
            'customer_name'     => $this->customer->name,
            'contact_number'    => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        Payment::create([
            'payment_number'   => 'PMT-CONFIRM-001',
            'booking_id'       => $booking->id,
            'user_id'          => $this->customer->id,
            'type'             => Payment::TYPE_RESERVATION_FEE,
            'amount'           => 200.00,
            'currency'         => 'PHP',
            'method'           => 'gcash',
            'reference_number' => 'REF-001',
            'status'           => Payment::STATUS_SUBMITTED,
            'paid_at'          => now(),
        ]);

        $service = app(PaymentVerificationService::class);
        $service->confirm($booking, $this->admin);

        Notification::assertSentTo($this->customer, BookingConfirmedNotification::class);
        Notification::assertSentTo($this->admin, JobOrderCreatedNotification::class);
    }

    public function test_payment_reject_sends_payment_rejected_notification(): void
    {
        Notification::fake();

        $booking = Booking::create([
            'booking_number'    => 'BK-REJECT-001',
            'user_id'           => $this->customer->id,
            'vehicle_id'        => $this->vehicle->id,
            'status'            => Booking::STATUS_PENDING_PAYMENT_VERIFICATION,
            'preferred_date'    => today()->addDays(2),
            'preferred_time'    => '10:00:00',
            'customer_name'     => $this->customer->name,
            'contact_number'    => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        Payment::create([
            'payment_number'   => 'PMT-REJECT-001',
            'booking_id'       => $booking->id,
            'user_id'          => $this->customer->id,
            'type'             => Payment::TYPE_RESERVATION_FEE,
            'amount'           => 200.00,
            'currency'         => 'PHP',
            'method'           => 'gcash',
            'reference_number' => 'REF-REJECT-1',
            'status'           => Payment::STATUS_SUBMITTED,
            'paid_at'          => now(),
        ]);

        $service = app(PaymentVerificationService::class);
        $service->rejectPayment($booking, $this->admin, 'Blurry receipt');

        Notification::assertSentTo($this->customer, PaymentRejectedNotification::class);
    }

    public function test_job_assign_sends_job_assigned_notification_to_mechanic_and_customer(): void
    {
        Notification::fake();

        $booking = Booking::create([
            'booking_number'    => 'BK-ASSIGN-001',
            'user_id'           => $this->customer->id,
            'vehicle_id'        => $this->vehicle->id,
            'status'            => Booking::STATUS_CONFIRMED,
            'preferred_date'    => today()->addDays(2),
            'preferred_time'    => '10:00:00',
            'customer_name'     => $this->customer->name,
            'contact_number'    => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        $job = JobOrder::create([
            'job_number'       => 'JO-ASSIGN-001',
            'booking_id'       => $booking->id,
            'status'           => JobOrder::STATUS_PENDING,
            'priority'         => JobOrder::PRIORITY_MEDIUM,
            'progress_percent' => 0,
        ]);

        $assignmentService = app(JobAssignmentService::class);
        $assignmentService->assign($job, $this->admin, [
            'mechanic_id' => $this->mechanic->id,
        ]);

        Notification::assertSentTo($this->mechanic, JobAssignedNotification::class);
        Notification::assertSentTo($this->customer, JobAssignedNotification::class);
    }

    public function test_service_update_with_visible_to_customer_sends_service_update_notification(): void
    {
        Notification::fake();

        $booking = Booking::create([
            'booking_number'    => 'BK-UPDATE-001',
            'user_id'           => $this->customer->id,
            'vehicle_id'        => $this->vehicle->id,
            'status'            => Booking::STATUS_CONFIRMED,
            'preferred_date'    => today()->addDays(2),
            'preferred_time'    => '10:00:00',
            'customer_name'     => $this->customer->name,
            'contact_number'    => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        $job = JobOrder::create([
            'job_number'       => 'JO-UPDATE-001',
            'booking_id'       => $booking->id,
            'mechanic_id'      => $this->mechanic->id,
            'status'           => JobOrder::STATUS_IN_PROGRESS,
            'priority'         => JobOrder::PRIORITY_MEDIUM,
            'progress_percent' => 20,
        ]);

        $response = $this->actingAs($this->mechanic)
            ->postJson('/mechanic/notes', [
                'jobId' => $job->id,
                'note'  => 'Oil filter replaced successfully.',
            ]);

        $response->assertOk();
        Notification::assertSentTo($this->customer, ServiceUpdateNotification::class);
    }

    public function test_support_ticket_create_sends_ticket_created_notification_to_staff(): void
    {
        Notification::fake();

        $response = $this->actingAs($this->customer)
            ->postJson('/customer/support', [
                'subject' => 'Need help with billing',
                'message' => 'Please explain the reservation fee calculation.',
            ]);

        $response->assertOk();
        Notification::assertSentTo($this->staff, TicketCreatedNotification::class);
    }

    public function test_staff_reply_sends_ticket_reply_notification_to_customer(): void
    {
        Notification::fake();

        $ticket = SupportTicket::create([
            'ticket_number' => 'TKT-TEST-001',
            'user_id'       => $this->customer->id,
            'subject'       => 'Billing issue',
            'message'       => 'Please clarify charge.',
            'status'        => 'open',
        ]);

        $response = $this->actingAs($this->staff)
            ->postJson("/staff/assistance/{$ticket->id}/reply", [
                'message' => 'The reservation fee is deducted from total service cost.',
            ]);

        $response->assertOk();
        Notification::assertSentTo($this->customer, TicketReplyNotification::class);
    }

    public function test_notification_api_endpoints(): void
    {
        $booking = Booking::create([
            'booking_number'    => 'BK-API-001',
            'user_id'           => $this->customer->id,
            'vehicle_id'        => $this->vehicle->id,
            'status'            => Booking::STATUS_CONFIRMED,
            'preferred_date'    => today()->addDays(2),
            'preferred_time'    => '10:00:00',
            'customer_name'     => $this->customer->name,
            'contact_number'    => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        // Send a notification directly to database
        $this->customer->notify(new BookingConfirmedNotification($booking));

        // Index endpoint returns unread count 1
        $indexResponse = $this->actingAs($this->customer)
            ->getJson('/notifications');

        $indexResponse->assertOk()
            ->assertJson([
                'count' => 1,
            ]);

        $notificationId = $indexResponse->json('notifications.0.id');
        $this->assertNotNull($notificationId);

        // Mark single notification read
        $readResponse = $this->actingAs($this->customer)
            ->postJson("/notifications/{$notificationId}/read");

        $readResponse->assertOk()
            ->assertJson(['success' => true]);

        // Index count is now 0
        $this->actingAs($this->customer)
            ->getJson('/notifications')
            ->assertJson(['count' => 0]);
    }

    public function test_inactive_users_are_not_notified(): void
    {
        Notification::fake();

        $inactiveCustomer = User::factory()->create([
            'role_id' => $this->customerRole->id,
            'status'  => User::STATUS_INACTIVE,
        ]);

        $booking = Booking::create([
            'booking_number'    => 'BK-INACTIVE-001',
            'user_id'           => $inactiveCustomer->id,
            'vehicle_id'        => $this->vehicle->id,
            'status'            => Booking::STATUS_CONFIRMED,
            'preferred_date'    => today()->addDays(2),
            'preferred_time'    => '10:00:00',
            'customer_name'     => $inactiveCustomer->name,
            'contact_number'    => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        $dispatcher = app(NotificationDispatcherService::class);
        $dispatcher->notifyUser($inactiveCustomer, new BookingConfirmedNotification($booking));

        Notification::assertNotSentTo($inactiveCustomer, BookingConfirmedNotification::class);
    }
}
