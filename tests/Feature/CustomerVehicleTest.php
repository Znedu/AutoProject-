<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\Role;
use App\Enums\RoleSlug;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerVehicleTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles if necessary (or assume they exist through seeders, but let's make sure they are in the database)
        $customerRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Customer->value],
            ['name' => 'Customer', 'description' => 'Customer Role']
        );

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
        ]);
    }

    public function test_guests_cannot_access_vehicles_page(): void
    {
        $response = $this->get(route('customer.vehicles.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_customer_can_view_vehicles_page(): void
    {
        $response = $this->actingAs($this->customer)
            ->get(route('customer.vehicles.index'));

        $response->assertStatus(200);
        $response->assertViewHas('vehicles');
    }

    public function test_customer_can_create_vehicle_via_ajax(): void
    {
        $data = [
            'make' => 'Honda',
            'model' => 'Civic',
            'year' => 2022,
            'plate_number' => 'XYZ 7890',
            'color' => 'Red',
            'notes' => 'Some test notes',
        ];

        $response = $this->actingAs($this->customer)
            ->postJson(route('customer.vehicles.store'), $data);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Vehicle added successfully!',
        ]);

        $this->assertDatabaseHas('vehicles', [
            'user_id' => $this->customer->id,
            'make' => 'Honda',
            'model' => 'Civic',
            'plate_number' => 'XYZ 7890',
        ]);
    }

    public function test_customer_can_update_their_own_vehicle(): void
    {
        $vehicle = Vehicle::create([
            'user_id' => $this->customer->id,
            'make' => 'Toyota',
            'model' => 'Vios',
            'year' => 2018,
            'plate_number' => 'ABC 1234',
        ]);

        $updateData = [
            'make' => 'Toyota',
            'model' => 'Vios Gen 3',
            'year' => 2019,
            'plate_number' => 'ABC 1234',
            'color' => 'Silver',
            'notes' => 'Updated notes',
        ];

        $response = $this->actingAs($this->customer)
            ->putJson(route('customer.vehicles.update', $vehicle), $updateData);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Vehicle updated successfully!',
        ]);

        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'model' => 'Vios Gen 3',
            'year' => 2019,
        ]);
    }

    public function test_customer_cannot_update_others_vehicle(): void
    {
        $otherUser = User::factory()->create([
            'role_id' => Role::where('slug', RoleSlug::Customer->value)->first()->id,
        ]);

        $vehicle = Vehicle::create([
            'user_id' => $otherUser->id,
            'make' => 'Ford',
            'model' => 'Mustang',
            'year' => 2020,
            'plate_number' => 'MUST 777',
        ]);

        $updateData = [
            'make' => 'Ford',
            'model' => 'Mustang GT',
            'year' => 2021,
            'plate_number' => 'MUST 777',
        ];

        $response = $this->actingAs($this->customer)
            ->putJson(route('customer.vehicles.update', $vehicle), $updateData);

        $response->assertStatus(403);
    }

    public function test_customer_can_delete_their_own_vehicle(): void
    {
        $vehicle = Vehicle::create([
            'user_id' => $this->customer->id,
            'make' => 'Mitsubishi',
            'model' => 'Lancer',
            'year' => 2015,
            'plate_number' => 'MIT 555',
        ]);

        $response = $this->actingAs($this->customer)
            ->deleteJson(route('customer.vehicles.destroy', $vehicle));

        $response->assertStatus(200);
        
        // Assert it is soft-deleted
        $this->assertSoftDeleted('vehicles', [
            'id' => $vehicle->id,
        ]);
    }
}
