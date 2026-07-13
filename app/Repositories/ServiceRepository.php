<?php

namespace App\Repositories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository
{
    /**
     * Get all services.
     *
     * @return Collection<int, Service>
     */
    public function all(): Collection
    {
        return Service::all();
    }

    /**
     * Find a service by ID.
     */
    public function find(int $id): ?Service
    {
        return Service::find($id);
    }

    /**
     * Create a new service.
     */
    public function create(array $data): Service
    {
        return Service::create($data);
    }

    /**
     * Update an existing service.
     */
    public function update(Service $service, array $data): bool
    {
        return $service->update($data);
    }

    public function delete(Service $service): ?bool
    {
        return $service->delete();
    }
}
