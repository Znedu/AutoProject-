<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Billing\BookingBillingCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingBillingCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected Booking $booking;

    protected BookingBillingCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(
            ['slug' => 'customer'],
            ['name' => 'Customer', 'description' => 'Customer Role']
        );

        $this->customer = User::factory()->create([
            'role_id' => $role->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $this->customer->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2021,
            'plate_number' => 'XYZ9876',
        ]);

        $this->booking = Booking::create([
            'booking_number' => 'BK-CALC-001',
            'user_id' => $this->customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => Booking::STATUS_CONFIRMED,
            'preferred_date' => today()->addDays(2),
            'preferred_time' => '10:00:00',
            'customer_name' => 'Jane Doe',
            'contact_number' => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        $this->calculator = new BookingBillingCalculatorService;
    }

    public function test_calculator_computes_all_subtotals_discounts_and_balance_accurately(): void
    {
        $quotation = Quotation::create([
            'booking_id' => $this->booking->id,
            'version' => 1,
            'type' => Quotation::TYPE_FINAL,
            'status' => Quotation::STATUS_DRAFT,
            'min_total' => 5000.00,
            'max_total' => 8000.00,
            'currency' => 'PHP',
        ]);

        // Core Service: ₱3,500
        QuotationLineItem::create([
            'quotation_id' => $quotation->id,
            'item_type' => QuotationLineItem::ITEM_TYPE_SERVICE,
            'description' => 'Full Engine Tune Up',
            'quantity' => 1,
            'unit_min' => 3000,
            'unit_max' => 4000,
            'unit_final' => 3500.00,
            'line_total' => 3500.00,
            'sort_order' => 1,
        ]);

        // Additional Service: ₱1,200
        QuotationLineItem::create([
            'quotation_id' => $quotation->id,
            'item_type' => QuotationLineItem::ITEM_TYPE_ADDITIONAL_SERVICE,
            'description' => 'Throttle Body Cleaning',
            'quantity' => 1,
            'unit_min' => 1000,
            'unit_max' => 1500,
            'unit_final' => 1200.00,
            'line_total' => 1200.00,
            'sort_order' => 2,
        ]);

        // Product (Part): 4 * ₱450 = ₱1,800
        QuotationLineItem::create([
            'quotation_id' => $quotation->id,
            'item_type' => QuotationLineItem::ITEM_TYPE_PRODUCT,
            'description' => 'Iridium Spark Plugs',
            'quantity' => 4,
            'unit_min' => 450,
            'unit_max' => 450,
            'unit_final' => 450.00,
            'line_total' => 1800.00,
            'sort_order' => 3,
        ]);

        // Material: 1 * ₱2,800 = ₱2,800
        QuotationLineItem::create([
            'quotation_id' => $quotation->id,
            'item_type' => QuotationLineItem::ITEM_TYPE_MATERIAL,
            'description' => 'Fully Synthetic Oil (4L)',
            'quantity' => 1,
            'unit_min' => 2800,
            'unit_max' => 2800,
            'unit_final' => 2800.00,
            'line_total' => 2800.00,
            'sort_order' => 4,
        ]);

        // Labor: 2 hrs * ₱500 = ₱1,000
        QuotationLineItem::create([
            'quotation_id' => $quotation->id,
            'item_type' => QuotationLineItem::ITEM_TYPE_LABOR,
            'description' => 'Custom Dyno Tuning Labor',
            'quantity' => 2,
            'unit_min' => 500,
            'unit_max' => 500,
            'unit_final' => 500.00,
            'line_total' => 1000.00,
            'sort_order' => 5,
        ]);

        // Fee: ₱250
        QuotationLineItem::create([
            'quotation_id' => $quotation->id,
            'item_type' => QuotationLineItem::ITEM_TYPE_FEE,
            'description' => 'Environmental & Disposal Fee',
            'quantity' => 1,
            'unit_min' => 250,
            'unit_max' => 250,
            'unit_final' => 250.00,
            'line_total' => 250.00,
            'sort_order' => 6,
        ]);

        // Discount: ₱500
        QuotationLineItem::create([
            'quotation_id' => $quotation->id,
            'item_type' => QuotationLineItem::ITEM_TYPE_DISCOUNT,
            'description' => 'Loyalty Club Discount',
            'quantity' => 1,
            'unit_min' => 500,
            'unit_max' => 500,
            'unit_final' => 500.00,
            'line_total' => 500.00,
            'sort_order' => 7,
        ]);

        // Verified Reservation fee: ₱200
        Payment::create([
            'payment_number' => 'PMT-TEST-001',
            'booking_id' => $this->booking->id,
            'user_id' => $this->customer->id,
            'type' => Payment::TYPE_RESERVATION_FEE,
            'amount' => 200.00,
            'currency' => 'PHP',
            'method' => 'gcash',
            'reference_number' => 'REF001',
            'status' => Payment::STATUS_VERIFIED,
            'paid_at' => now(),
        ]);

        // Verified Deposit: ₱2,000
        Payment::create([
            'payment_number' => 'PMT-TEST-002',
            'booking_id' => $this->booking->id,
            'user_id' => $this->customer->id,
            'type' => Payment::TYPE_DEPOSIT,
            'amount' => 2000.00,
            'currency' => 'PHP',
            'method' => 'bank_transfer',
            'reference_number' => 'REF002',
            'status' => Payment::STATUS_VERIFIED,
            'paid_at' => now(),
        ]);

        $summary = $this->calculator->calculate($this->booking);

        // Subtotals assertions:
        // Services = 3500 + 1200 = 4700
        $this->assertEquals(4700.00, $summary->servicesSubtotal);
        // Products & Materials = 1800 + 2800 = 4600
        $this->assertEquals(4600.00, $summary->productsSubtotal);
        // Labor = 1000
        $this->assertEquals(1000.00, $summary->laborSubtotal);
        // Fees = 250
        $this->assertEquals(250.00, $summary->feesSubtotal);
        // Discounts = 500
        $this->assertEquals(500.00, $summary->discountsTotal);

        // Final total = 4700 + 4600 + 1000 + 250 - 500 = 10050
        $this->assertEquals(10050.00, $summary->finalTotal);

        // Total paid credited toward bill = 2000 (deposit), reservation fee is separate
        $this->assertEquals(200.00, $summary->reservationFeePaid);
        $this->assertEquals(2000.00, $summary->depositsPaid);
        $this->assertEquals(2000.00, $summary->totalPaid);

        // Balance due = 10050 - 2000 = 8050
        $this->assertEquals(8050.00, $summary->balanceDue);
    }
}
