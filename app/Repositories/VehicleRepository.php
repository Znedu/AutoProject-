<?php

namespace App\Repositories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;

class VehicleRepository
{
    /**
     * Get all active (non-deleted) vehicles for a given user.
     *
     * @return Collection<int, Vehicle>
     */
    public function allForUser(int $userId): Collection
    {
        return Vehicle::forUser($userId)->latest()->get();
    }

    /**
     * Create a new vehicle for a user.
     */
    public function create(int $userId, array $data): Vehicle
    {
        return Vehicle::create(array_merge($data, ['user_id' => $userId]));
    }

    /**
     * Update an existing vehicle.
     */
    public function update(Vehicle $vehicle, array $data): bool
    {
        return $vehicle->update($data);
    }

    /**
     * Soft-delete a vehicle.
     */
    public function delete(Vehicle $vehicle): ?bool
    {
        return $vehicle->delete();
    }
}
