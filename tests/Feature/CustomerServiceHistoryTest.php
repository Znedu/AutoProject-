<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Booking;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerServiceHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $otherCustomer;
    protected Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $customerRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Customer->value],
            ['name' => 'Customer', 'description' => 'Customer Role']
        );

        $viewPerm = Permission::firstOrCreate(
            ['slug' => 'bookings.view'],
            ['name' => 'View Bookings', 'description' => 'View Bookings Permission']
        );

        $customerRole->permissions()->syncWithoutDetaching([$viewPerm->id]);

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->otherCustomer = User::factory()->create([
            'role_id' => $customerRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->vehicle = Vehicle::create([
            'user_id' => $this->customer->id,
            'make' => 'Toyota',
            'model' => 'Supra',
            'year' => 2021,
            'plate_number' => 'SUPRA-888',
        ]);
    }

    public function test_guests_cannot_access_service_history(): void
    {
        $response = $this->get(route('customer.service-history'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_view_service_history_page(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-HIST-001',
            'user_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => Booking::STATUS_COMPLETED,
            'preferred_date' => today()->subDays(10),
            'preferred_time' => '10:00:00',
            'completed_at' => today()->subDays(9),
            'customer_name' => 'John Doe',
            'contact_number' => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        $category = \App\Models\ServiceCategory::create([
            'name' => 'Detailing',
            'slug' => 'detailing',
            'is_active' => true,
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'code' => 'detail-001',
            'name' => 'Ceramic Coating Pro',
            'min_cost' => 8000,
            'max_cost' => 12000,
            'status' => Service::STATUS_ACTIVE,
        ]);

        $booking->bookingServices()->create([
            'service_id' => $service->id,
            'quantity' => 1,
            'preferred_brand' => 'CeramicPro 9H',
        ]);

        $jobOrder = \App\Models\JobOrder::create([
            'job_number' => 'JOB-2026-0001',
            'booking_id' => $booking->id,
            'status' => \App\Models\JobOrder::STATUS_COMPLETED,
            'priority' => \App\Models\JobOrder::PRIORITY_MEDIUM,
        ]);

        \App\Models\ServiceUpdate::create([
            'job_order_id' => $jobOrder->id,
            'user_id' => $this->customer->id,
            'message' => 'First coat ceramic applied successfully',
            'is_visible_to_customer' => true,
        ]);

        \App\Models\ServiceUpdate::create([
            'job_order_id' => $jobOrder->id,
            'user_id' => $this->customer->id,
            'message' => 'Internal mechanic private note',
            'is_visible_to_customer' => false,
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.service-history'));

        $response->assertStatus(200);
        $response->assertViewHas('records');
        $response->assertViewHas('vehicles');
        $response->assertSee('Ceramic Coating Pro');
        $response->assertSee('SUPRA-888');
        $response->assertSee('BK-HIST-001');
    }

    public function test_customer_can_filter_service_history_by_vehicle(): void
    {
        $vehicle2 = Vehicle::create([
            'user_id' => $this->customer->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2020,
            'plate_number' => 'CIVIC-123',
        ]);

        $booking1 = Booking::create([
            'booking_number' => 'BK-SUPRA-01',
            'user_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => Booking::STATUS_COMPLETED,
            'preferred_date' => today()->subDays(5),
            'preferred_time' => '10:00:00',
            'customer_name' => 'John Doe',
            'contact_number' => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        $booking2 = Booking::create([
            'booking_number' => 'BK-CIVIC-02',
            'user_id' => $this->customer->id,
            'vehicle_id' => $vehicle2->id,
            'status' => Booking::STATUS_COMPLETED,
            'preferred_date' => today()->subDays(2),
            'preferred_time' => '10:00:00',
            'customer_name' => 'John Doe',
            'contact_number' => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.service-history', ['vehicle_id' => $this->vehicle->id]));

        $response->assertStatus(200);
        $response->assertSee('BK-SUPRA-01');
        $response->assertDontSee('BK-CIVIC-02');
    }

    public function test_customer_can_search_service_history(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-SEARCH-999',
            'user_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => Booking::STATUS_COMPLETED,
            'preferred_date' => today()->subDays(5),
            'preferred_time' => '10:00:00',
            'customer_name' => 'John Doe',
            'contact_number' => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.service-history', ['search' => 'SEARCH-999']));

        $response->assertStatus(200);
        $response->assertSee('BK-SEARCH-999');
    }

    public function test_customer_can_view_printable_service_record(): void
    {
        $booking = Booking::create([
            'booking_number' => 'BK-PRINT-01',
            'user_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => Booking::STATUS_COMPLETED,
            'preferred_date' => today()->subDays(5),
            'preferred_time' => '10:00:00',
            'customer_name' => 'John Doe',
            'contact_number' => '09123456789',
            'terms_accepted_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.service-history.print', $booking));

        $response->assertStatus(200);
        $response->assertSee('Official Maintenance & Service History Record');
        $response->assertSee('BK-PRINT-01');
    }

    public function test_customer_cannot_view_another_customer_service_record(): void
    {
        $otherVehicle = Vehicle::create([
            'user_id' => $this->otherCustomer->id,
            'make' => 'Mazda',
            'model' => 'Miata',
            'year' => 2019,
            'plate_number' => 'MIATA-99',
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-OTHER-01',
            'user_id' => $this->otherCustomer->id,
            'vehicle_id' => $otherVehicle->id,
            'status' => Booking::STATUS_COMPLETED,
            'preferred_date' => today()->subDays(5),
            'preferred_time' => '10:00:00',
            'customer_name' => 'Other Person',
            'contact_number' => '09123456780',
            'terms_accepted_at' => now(),
        ]);

        $response = $this->actingAs($this->customer)
            ->get(route('customer.service-history.print', $booking));

        $response->assertStatus(403);
    }
}
