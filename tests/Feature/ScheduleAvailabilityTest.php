<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Booking\ScheduleAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected ScheduleAvailabilityService $availabilityService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->availabilityService = app(ScheduleAvailabilityService::class);
    }

    public function test_past_time_slots_on_today_date_are_marked_as_past(): void
    {
        // Freeze time at 02:01 PM (14:01) on a Monday
        $monday = Carbon::parse('2026-09-07 14:01:00'); // 2026-09-07 is Monday
        Carbon::setTestNow($monday);

        $date = '2026-09-07';
        $availability = $this->availabilityService->availabilityForDate($date);

        // Morning and early afternoon slots (08:00 AM..02:00 PM) should be in past_slots
        $this->assertContains('08:00 AM', $availability['past_slots']);
        $this->assertContains('09:00 AM', $availability['past_slots']);
        $this->assertContains('10:00 AM', $availability['past_slots']);
        $this->assertContains('11:00 AM', $availability['past_slots']);
        $this->assertContains('12:00 PM', $availability['past_slots']);
        $this->assertContains('01:00 PM', $availability['past_slots']);
        $this->assertContains('02:00 PM', $availability['past_slots']);

        // Future slots (03:00 PM, 04:00 PM, 05:00 PM) should be available
        $this->assertContains('03:00 PM', $availability['available_slots']);
        $this->assertContains('04:00 PM', $availability['available_slots']);
        $this->assertContains('05:00 PM', $availability['available_slots']);

        // Past slot is not available
        $this->assertFalse($this->availabilityService->isSlotAvailable($date, '08:00 AM'));
        // Future slot is available
        $this->assertTrue($this->availabilityService->isSlotAvailable($date, '03:00 PM'));

        Carbon::setTestNow();
    }

    public function test_booked_time_slot_is_marked_as_booked_and_unavailable(): void
    {
        // Freeze time on Monday morning at 07:00 AM
        $monday = Carbon::parse('2026-09-07 07:00:00');
        Carbon::setTestNow($monday);

        $customer = User::factory()->create();
        $vehicle = Vehicle::create([
            'user_id' => $customer->id,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'plate_number' => 'TST 1234',
        ]);

        Booking::create([
            'booking_number' => 'BK-TEST-001',
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => Booking::STATUS_PENDING_PAYMENT_VERIFICATION,
            'preferred_date' => '2026-09-07',
            'preferred_time' => '10:00:00',
            'customer_name' => 'John Doe',
            'contact_number' => '09123456789',
        ]);

        $availability = $this->availabilityService->availabilityForDate('2026-09-07');

        $this->assertContains('10:00 AM', $availability['booked_slots']);
        $this->assertNotContains('10:00 AM', $availability['available_slots']);
        $this->assertFalse($this->availabilityService->isSlotAvailable('2026-09-07', '10:00 AM'));

        Carbon::setTestNow();
    }

    public function test_schedule_availability_api_endpoint(): void
    {
        $user = User::factory()->create();
        $monday = Carbon::parse('2026-09-07 07:00:00');
        Carbon::setTestNow($monday);

        $response = $this->actingAs($user)->getJson(route('customer.schedule.availability', ['date' => '2026-09-07']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'is_fully_booked',
            'available_slots',
            'booked_slots',
            'past_slots',
        ]);

        Carbon::setTestNow();
    }
}
