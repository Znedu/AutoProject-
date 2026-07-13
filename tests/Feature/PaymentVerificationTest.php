<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Service;
use App\Enums\RoleSlug;
use App\Services\Booking\PaymentVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $admin;
    protected Role $customerRole;
    protected Role $adminRole;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        $this->customerRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Customer->value],
            ['name' => 'Customer', 'description' => 'Customer Role']
        );

        $this->adminRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Administrator->value],
            ['name' => 'Administrator', 'description' => 'Admin Role']
        );

        // Ensure permissions exist and attach them
        $verifyPerm = Permission::firstOrCreate(
            ['slug' => 'approvals.manage'],
            ['name' => 'Manage Approvals', 'description' => 'Manage Booking Approvals']
        );

        $submitPerm = Permission::firstOrCreate(
            ['slug' => 'payments.submit'],
            ['name' => 'Submit Payments', 'description' => 'Submit Payment Details']
        );

        $this->adminRole->permissions()->syncWithoutDetaching([$verifyPerm->id]);
        $this->customerRole->permissions()->syncWithoutDetaching([$submitPerm->id]);

        // Create users
        $this->customer = User::factory()->create([
            'role_id' => $this->customerRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        // Create vehicle
        $this->vehicle = Vehicle::create([
            'user_id' => $this->customer->id,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'ABC1234',
        ]);
    }

    public function test_payment_verification_lifecycle(): void
    {
        Storage::fake('public');

        // 1. Create a booking that starts as pending_payment_verification
        $booking = Booking::create([
            'booking_number' => 'BK-TEST1234',
            'user_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => Booking::STATUS_PENDING_PAYMENT_VERIFICATION,
            'preferred_date' => today()->addDays(2),
            'preferred_time' => '10:00:00',
            'customer_name' => 'John Doe',
            'contact_number' => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        // 2. Submit initial payment screenshot
        $payment = Payment::create([
            'payment_number' => 'PMT-TEST1234',
            'booking_id' => $booking->id,
            'user_id' => $this->customer->id,
            'type' => Payment::TYPE_RESERVATION_FEE,
            'amount' => 200.00,
            'currency' => 'PHP',
            'method' => 'gcash',
            'reference_number' => 'REF11111',
            'status' => Payment::STATUS_SUBMITTED,
            'paid_at' => now(),
        ]);

        $payment->proofs()->create([
            'disk' => 'public',
            'file_path' => 'payment_proofs/proof1.png',
            'original_name' => 'proof1.png',
            'mime_type' => 'image/png',
            'size_bytes' => 1024,
        ]);

        // 3. Admin rejects payment (Attempt 1)
        $response = $this->actingAs($this->admin)
            ->post(route('admin.bookings.reject-payment', $booking), [
                'reason' => 'Screenshot is blurry. Please upload a clear image.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $booking->refresh();
        $payment->refresh();

        $this->assertEquals(Booking::STATUS_PAYMENT_REQUIRES_RESUBMISSION, $booking->status);
        $this->assertEquals(1, $booking->payment_attempts);
        $this->assertEquals(Payment::STATUS_REJECTED, $payment->status);
        $this->assertEquals('Screenshot is blurry. Please upload a clear image.', $payment->rejection_reason);

        // 4. Customer resubmits payment (Attempt 2)
        $file = UploadedFile::fake()->image('proof2.png');
        $resubmitResponse = $this->actingAs($this->customer)
            ->post(route('customer.payment.resubmit', $booking->id), [
                'payment_method' => 'maya',
                'reference_number' => 'REF22222',
                'payment_screenshot' => $file,
            ]);

        $resubmitResponse->assertRedirect();
        $resubmitResponse->assertSessionHasNoErrors();

        $booking->refresh();
        // Booking should go back to pending payment verification
        $this->assertEquals(Booking::STATUS_PENDING_PAYMENT_VERIFICATION, $booking->status);
        // Note: payment_attempts remains 1 (it is incremented ONLY on rejection, not submission)
        $this->assertEquals(1, $booking->payment_attempts);

        // A new payment record should be created
        $newPayment = $booking->payments()->latest('id')->first();
        $this->assertNotEquals($payment->id, $newPayment->id);
        $this->assertEquals(Payment::STATUS_SUBMITTED, $newPayment->status);
        $this->assertEquals('maya', $newPayment->method);
        $this->assertEquals('REF22222', $newPayment->reference_number);
        $this->assertCount(1, $newPayment->proofs);

        // 5. Admin rejects payment again (Attempt 2 rejection)
        $response = $this->actingAs($this->admin)
            ->post(route('admin.bookings.reject-payment', $booking), [
                'reason' => 'Invalid reference number. Please check.',
            ]);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_PAYMENT_REQUIRES_RESUBMISSION, $booking->status);
        $this->assertEquals(2, $booking->payment_attempts);

        // 6. Customer resubmits payment again (Attempt 3)
        $file3 = UploadedFile::fake()->image('proof3.png');
        $this->actingAs($this->customer)
            ->post(route('customer.payment.resubmit', $booking->id), [
                'payment_method' => 'gcash',
                'reference_number' => 'REF33333',
                'payment_screenshot' => $file3,
            ]);

        $booking->refresh();
        $this->assertEquals(Booking::STATUS_PENDING_PAYMENT_VERIFICATION, $booking->status);
        $this->assertEquals(2, $booking->payment_attempts);

        // 7. Admin rejects payment for the 3rd time (Attempt 3 rejection -> Cancelled)
        $response = $this->actingAs($this->admin)
            ->post(route('admin.bookings.reject-payment', $booking), [
                'reason' => 'Third rejection. Fake screenshot.',
            ]);

        $booking->refresh();
        // Booking status should be cancelled
        $this->assertEquals(Booking::STATUS_CANCELLED, $booking->status);
        $this->assertEquals(3, $booking->payment_attempts);
    }

    public function test_payment_verification_confirmation(): void
    {
        // Setup initial booking
        $booking = Booking::create([
            'booking_number' => 'BK-TEST5555',
            'user_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => Booking::STATUS_PENDING_PAYMENT_VERIFICATION,
            'preferred_date' => today()->addDays(2),
            'preferred_time' => '11:00:00',
            'customer_name' => 'Jane Doe',
            'contact_number' => '09123456780',
            'terms_accepted_at' => now(),
        ]);

        $payment = Payment::create([
            'payment_number' => 'PMT-TEST5555',
            'booking_id' => $booking->id,
            'user_id' => $this->customer->id,
            'type' => Payment::TYPE_RESERVATION_FEE,
            'amount' => 200.00,
            'currency' => 'PHP',
            'method' => 'gcash',
            'reference_number' => 'REF55555',
            'status' => Payment::STATUS_SUBMITTED,
            'paid_at' => now(),
        ]);

        // Admin confirms the payment
        $response = $this->actingAs($this->admin)
            ->post(route('admin.bookings.confirm-payment', $booking));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $booking->refresh();
        $payment->refresh();

        // Booking status becomes confirmed, payment becomes verified
        $this->assertEquals(Booking::STATUS_CONFIRMED, $booking->status);
        $this->assertEquals(Payment::STATUS_VERIFIED, $payment->status);
        $this->assertNotNull($booking->approved_by);
        $this->assertNotNull($booking->approved_at);

        // Job order must be automatically created
        $this->assertNotNull($booking->jobOrder);
        $this->assertEquals('pending', $booking->jobOrder->status);
    }
}
