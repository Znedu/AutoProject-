<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffViewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_staff_views_do_not_render_invalid_alpine_bindings(): void
    {
        $bookingQueueHtml = view('staff.booking-queue', ['bookings' => []])->render();
        $jobsHtml = view('staff.jobs', ['jobs' => [], 'selectedFilter' => 'all', 'stats' => ['total' => 0, 'unassigned' => 0, 'in_progress' => 0, 'completed' => 0]])->render();
        $assistanceHtml = view('staff.assistance', ['tickets' => []])->render();

        foreach ([$bookingQueueHtml, $jobsHtml, $assistanceHtml] as $html) {
            $this->assertStringNotContainsString('::variant', $html);
            $this->assertStringNotContainsString('::class', $html);
            $this->assertStringNotContainsString('::status', $html);
            $this->assertStringNotContainsString('::value', $html);
        }
    }
    public function test_staff_dashboard_includes_pending_payment_verification_bookings(): void
    {
        $staffRole = \App\Models\Role::query()->where('slug', \App\Enums\RoleSlug::Staff->value)->first();
        $staff = \App\Models\User::factory()->create(['role_id' => $staffRole->id]);

        $customer = \App\Models\User::factory()->create();
        $vehicle = \App\Models\Vehicle::create([
            'user_id' => $customer->id,
            'make' => 'Toyota',
            'model' => 'Camry',
            'year' => 2022,
            'plate_number' => 'XYZ 9999',
        ]);

        $booking = \App\Models\Booking::create([
            'booking_number' => 'BK-TEST-ONLINE-01',
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => \App\Models\Booking::STATUS_PENDING_PAYMENT_VERIFICATION,
            'preferred_date' => now()->addDays(2),
            'preferred_time' => '10:00:00',
            'customer_name' => 'Jane Online',
            'contact_number' => '09171234567',
        ]);

        $response = $this->actingAs($staff)->get(route('staff.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Jane Online');
    }

    public function test_staff_booking_queue_includes_online_pending_bookings(): void
    {
        $staffRole = \App\Models\Role::query()->where('slug', \App\Enums\RoleSlug::Staff->value)->first();
        $staff = \App\Models\User::factory()->create(['role_id' => $staffRole->id]);

        $customer = \App\Models\User::factory()->create();
        $vehicle = \App\Models\Vehicle::create([
            'user_id' => $customer->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2021,
            'plate_number' => 'HND 8888',
        ]);

        \App\Models\Booking::create([
            'booking_number' => 'BK-TEST-ONLINE-02',
            'user_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'status' => \App\Models\Booking::STATUS_PENDING_PAYMENT_VERIFICATION,
            'preferred_date' => now()->addDays(3),
            'preferred_time' => '14:00:00',
            'customer_name' => 'Mark Online',
            'contact_number' => '09181234567',
        ]);

        $response = $this->actingAs($staff)->get(route('staff.booking-queue'));

        $response->assertStatus(200);
        $response->assertSee('Mark Online');
    }

    public function test_walk_in_booking_rejects_sunday_date(): void
    {
        $staffRole = \App\Models\Role::query()->where('slug', \App\Enums\RoleSlug::Staff->value)->first();
        $staff = \App\Models\User::factory()->create(['role_id' => $staffRole->id]);

        $category = \App\Models\ServiceCategory::create([
            'name' => 'General Maintenance',
            'slug' => 'general-maintenance',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $service = \App\Models\Service::create([
            'service_category_id' => $category->id,
            'code' => 'SVC-OIL-01',
            'name' => 'Oil Change',
            'slug' => 'oil-change',
            'status' => 'active',
            'min_cost' => 1000,
            'max_cost' => 2000,
        ]);

        // Next Sunday
        $nextSunday = \Carbon\Carbon::now()->next(\Carbon\Carbon::SUNDAY)->format('Y-m-d');

        $response = $this->actingAs($staff)->post(route('staff.walk-in-booking.store'), [
            'booking_type' => 'new',
            'customer_name' => 'Sunday Customer',
            'contact_number' => '09123456789',
            'new_email' => 'sunday@example.com',
            'new_password' => 'password123',
            'vehicle_make' => 'Toyota',
            'vehicle_model' => 'Corolla',
            'vehicle_year' => 2020,
            'plate_number' => 'SUN 777',
            'service_ids' => [$service->id],
            'preferred_date' => $nextSunday,
            'preferred_time' => '10:00',
        ]);

        $response->assertSessionHasErrors(['preferred_date']);
    }

    public function test_walk_in_booking_creates_new_customer_and_appears_in_customers_and_booking_queue(): void
    {
        $staffRole = \App\Models\Role::query()->where('slug', \App\Enums\RoleSlug::Staff->value)->first();
        $staff = \App\Models\User::factory()->create(['role_id' => $staffRole->id]);

        $category = \App\Models\ServiceCategory::create([
            'name' => 'Brake Service',
            'slug' => 'brake-service',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $service = \App\Models\Service::create([
            'service_category_id' => $category->id,
            'code' => 'SVC-BRK-01',
            'name' => 'Brake Cleaning',
            'slug' => 'brake-cleaning',
            'status' => 'active',
            'min_cost' => 1500,
            'max_cost' => 3000,
        ]);

        $nextMonday = \Carbon\Carbon::now()->next(\Carbon\Carbon::MONDAY)->format('Y-m-d');

        $response = $this->actingAs($staff)->post(route('staff.walk-in-booking.store'), [
            'booking_type' => 'new',
            'customer_name' => 'WalkIn NewCustomer',
            'contact_number' => '09998887777',
            'new_email' => '', // Leave blank to test auto-generation
            'new_password' => '', // Leave blank to test auto-generation
            'vehicle_make' => 'Nissan',
            'vehicle_model' => 'Navara',
            'vehicle_year' => 2021,
            'plate_number' => 'NAV 1234',
            'service_ids' => [$service->id],
            'preferred_date' => $nextMonday,
            'preferred_time' => '11:00',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', [
            'name' => 'WalkIn NewCustomer',
            'phone' => '09998887777',
        ]);

        $newCustomer = \App\Models\User::where('name', 'WalkIn NewCustomer')->firstOrFail();
        $this->assertTrue($newCustomer->isCustomer());
        $this->assertDatabaseHas('vehicles', [
            'user_id' => $newCustomer->id,
            'plate_number' => 'NAV 1234',
        ]);

        // Check listed in Staff Customers page
        $customersRes = $this->actingAs($staff)->get(route('staff.customers.index'));
        $customersRes->assertStatus(200);
        $customersRes->assertSee('WalkIn NewCustomer');

        // Check listed in Staff Booking Queue page
        $queueRes = $this->actingAs($staff)->get(route('staff.booking-queue'));
        $queueRes->assertStatus(200);
        $queueRes->assertSee('WalkIn NewCustomer');
    }
}
