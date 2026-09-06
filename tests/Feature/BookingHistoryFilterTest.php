<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingHistoryFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private \App\Models\Vehicle $vehicle;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Administrator', 'description' => 'Admin Role']
        );

        $permission = Permission::firstOrCreate(
            ['slug' => 'approvals.manage'],
            ['name' => 'Manage Approvals', 'description' => 'Manage Approvals']
        );

        $adminRole->permissions()->syncWithoutDetaching([$permission->id]);

        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->vehicle = \App\Models\Vehicle::create([
            'user_id' => $this->admin->id,
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2022,
            'plate_number' => 'ABC-1234',
        ]);
    }

    public function test_history_all_bookings_tab_shows_all_records(): void
    {
        Booking::create([
            'booking_number' => 'BK-HIST-001',
            'user_id' => $this->admin->id,
            'vehicle_id' => $this->vehicle->id,
            'customer_name' => 'John Customer',
            'contact_number' => '09123456789',
            'status' => Booking::STATUS_PENDING_PAYMENT_VERIFICATION,
            'preferred_date' => today()->addDays(2),
            'preferred_time' => '10:00:00',
        ]);

        Booking::create([
            'booking_number' => 'BK-HIST-002',
            'user_id' => $this->admin->id,
            'vehicle_id' => $this->vehicle->id,
            'customer_name' => 'Jane Customer',
            'contact_number' => '09123456788',
            'status' => Booking::STATUS_CONFIRMED,
            'preferred_date' => today()->addDays(3),
            'preferred_time' => '11:00:00',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/bookings/history');

        $response->assertStatus(200);
        $response->assertSee('BK-HIST-001');
        $response->assertSee('BK-HIST-002');
    }

    public function test_history_pending_tab_shows_customer_pending_verification_bookings(): void
    {
        Booking::create([
            'booking_number' => 'BK-PENDING-VERIF',
            'user_id' => $this->admin->id,
            'vehicle_id' => $this->vehicle->id,
            'customer_name' => 'Alice Pending',
            'contact_number' => '09123456789',
            'status' => Booking::STATUS_PENDING_PAYMENT_VERIFICATION,
            'preferred_date' => today()->addDays(2),
            'preferred_time' => '10:00:00',
        ]);

        Booking::create([
            'booking_number' => 'BK-CONFIRMED-USER',
            'user_id' => $this->admin->id,
            'vehicle_id' => $this->vehicle->id,
            'customer_name' => 'Bob Confirmed',
            'contact_number' => '09123456788',
            'status' => Booking::STATUS_CONFIRMED,
            'preferred_date' => today()->addDays(3),
            'preferred_time' => '11:00:00',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/bookings/history?status=pending');

        $response->assertStatus(200);
        $response->assertSee('BK-PENDING-VERIF');
        $response->assertDontSee('BK-CONFIRMED-USER');
    }

    public function test_history_approved_tab_shows_confirmed_and_approved_bookings(): void
    {
        Booking::create([
            'booking_number' => 'BK-CONFIRMED-001',
            'user_id' => $this->admin->id,
            'vehicle_id' => $this->vehicle->id,
            'customer_name' => 'Charlie Confirmed',
            'contact_number' => '09123456789',
            'status' => Booking::STATUS_CONFIRMED,
            'preferred_date' => today()->addDays(2),
            'preferred_time' => '10:00:00',
        ]);

        Booking::create([
            'booking_number' => 'BK-REJECTED-001',
            'user_id' => $this->admin->id,
            'vehicle_id' => $this->vehicle->id,
            'customer_name' => 'David Rejected',
            'contact_number' => '09123456788',
            'status' => Booking::STATUS_REJECTED,
            'preferred_date' => today()->addDays(3),
            'preferred_time' => '11:00:00',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/bookings/history?status=approved');

        $response->assertStatus(200);
        $response->assertSee('BK-CONFIRMED-001');
        $response->assertDontSee('BK-REJECTED-001');
    }
}
