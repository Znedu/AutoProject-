<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\Booking\BookingCreatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingDuplicateSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_submission_with_same_reference_number_returns_existing_booking_without_duplication(): void
    {
        $customer = User::factory()->create();

        $category = ServiceCategory::create([
            'name' => 'General Maintenance',
            'slug' => 'general-maintenance',
            'icon' => 'wrench',
            'color' => '#E63946',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'code' => 'GEN-001',
            'name' => 'General PMS',
            'description' => 'Periodic maintenance service',
            'min_cost' => 1500,
            'max_cost' => 3500,
            'status' => 'active',
        ]);

        $bookingData = [
            'service_ids' => [$service->id],
            'customer_name' => 'John Doe',
            'contact_number' => '09123456789',
            'vehicle_make' => 'Toyota',
            'vehicle_model' => 'Vios',
            'vehicle_year' => 2021,
            'plate_number' => 'ABC1234',
            'preferred_date' => today()->nextWeekday()->format('Y-m-d'),
            'preferred_time' => '10:00:00',
            'payment_method' => 'gcash',
            'reference_number' => 'GCASH-SPAM-CLICK-REF-1234',
        ];

        $creator = app(BookingCreatorService::class);

        // First submission
        $booking1 = $creator->create($customer, $bookingData);

        // Spam click / second concurrent submission
        $booking2 = $creator->create($customer, $bookingData);

        // Third submission
        $booking3 = $creator->create($customer, $bookingData);

        $this->assertEquals($booking1->id, $booking2->id);
        $this->assertEquals($booking1->id, $booking3->id);
        $this->assertEquals($booking1->booking_number, $booking2->booking_number);

        // Ensure database only contains 1 booking and 1 payment
        $this->assertEquals(1, Booking::count());
        $this->assertEquals(1, Payment::where('reference_number', 'GCASH-SPAM-CLICK-REF-1234')->count());
    }
}
