<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\Role;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingFinalPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $admin;

    protected Role $customerRole;

    protected Role $adminRole;

    protected Booking $booking;

    protected Quotation $finalQuotation;

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

        $approvalsPerm = Permission::firstOrCreate(['slug' => 'approvals.manage'], ['name' => 'Approvals Manage']);
        $this->adminRole->permissions()->syncWithoutDetaching([$approvalsPerm->id]);

        $this->customer = User::factory()->create([
            'role_id' => $this->customerRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $this->customer->id,
            'make' => 'Mazda',
            'model' => 'MX-5',
            'year' => 2022,
            'plate_number' => 'MIATA99',
        ]);

        $this->booking = Booking::create([
            'booking_number' => 'BK-PMT-001',
            'user_id' => $this->customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => Booking::STATUS_COMPLETED,
            'preferred_date' => today(),
            'preferred_time' => '10:00:00',
            'customer_name' => 'Mazda Lover',
            'contact_number' => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        // Reservation fee payment already verified: ₱200
        Payment::create([
            'payment_number' => 'PMT-RES-001',
            'booking_id' => $this->booking->id,
            'user_id' => $this->customer->id,
            'type' => Payment::TYPE_RESERVATION_FEE,
            'amount' => 200.00,
            'currency' => 'PHP',
            'method' => 'gcash',
            'status' => Payment::STATUS_VERIFIED,
            'paid_at' => now()->subDays(2),
        ]);

        // Finalized Quotation: Total ₱5,000 (Balance Due: ₱5,000)
        $this->finalQuotation = Quotation::create([
            'booking_id' => $this->booking->id,
            'version' => 1,
            'type' => Quotation::TYPE_FINAL,
            'status' => Quotation::STATUS_APPROVED,
            'min_total' => 5000.00,
            'max_total' => 5000.00,
            'final_total' => 5000.00,
            'services_subtotal' => 5000.00,
            'amount_paid_snapshot' => 0.00,
            'balance_due_snapshot' => 5000.00,
            'currency' => 'PHP',
            'finalized_at' => now(),
            'finalized_by' => $this->admin->id,
        ]);

        QuotationLineItem::create([
            'quotation_id' => $this->finalQuotation->id,
            'item_type' => QuotationLineItem::ITEM_TYPE_SERVICE,
            'description' => 'Exhaust Fabrication',
            'quantity' => 1,
            'unit_min' => 5000,
            'unit_max' => 5000,
            'unit_final' => 5000.00,
            'line_total' => 5000.00,
            'sort_order' => 1,
        ]);
    }

    public function test_recording_cash_payment_auto_verifies_and_updates_balance(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.bookings.billing.payments.store', $this->booking), [
                'type' => 'final_payment',
                'method' => 'cash',
                'amount' => 5000.00,
                'notes' => 'Received full final cash payment in person.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment = $this->booking->payments()->where('type', 'final_payment')->first();
        $this->assertNotNull($payment);
        $this->assertEquals(Payment::STATUS_VERIFIED, $payment->status);
        $this->assertEquals(5000.00, (float) $payment->amount);
        $this->assertEquals($this->admin->id, $payment->verified_by);

        $this->finalQuotation->refresh();
        $this->assertEquals(5000.00, (float) $this->finalQuotation->amount_paid_snapshot);
        $this->assertEquals(0.00, (float) $this->finalQuotation->balance_due_snapshot);
    }

    public function test_recording_gcash_payment_attaches_proof_and_marks_submitted(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('receipt.jpg');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.bookings.billing.payments.store', $this->booking), [
                'type' => 'deposit',
                'method' => 'gcash',
                'amount' => 2000.00,
                'reference_number' => 'GCASH-998877',
                'payment_proof' => $file,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $payment = $this->booking->payments()->where('type', 'deposit')->first();
        $this->assertNotNull($payment);
        $this->assertEquals(Payment::STATUS_SUBMITTED, $payment->status);
        $this->assertCount(1, $payment->proofs);
    }

    public function test_payment_exceeding_balance_due_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.bookings.billing.payments.store', $this->booking), [
                'type' => 'final_payment',
                'method' => 'cash',
                'amount' => 5500.00, // Balance due is 5000
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
