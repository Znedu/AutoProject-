<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Repositories\VehicleRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function __construct(
        protected VehicleRepository $vehicleRepository
    ) {}

    public function index(Request $request): View
    {
        $vehicles = $this->vehicleRepository->allForUser($request->user()->id);

        return view('customer.vehicles', [
            'vehicles' => $vehicles,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'make'         => 'required|string|max:100',
            'model'        => 'required|string|max:100',
            'year'         => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'plate_number' => 'required|string|max:20',
            'color'        => 'nullable|string|max:50',
            'notes'        => 'nullable|string|max:500',
        ]);

        $vehicle = $this->vehicleRepository->create($request->user()->id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle added successfully!',
            'vehicle' => $this->formatVehicle($vehicle),
        ]);
    }

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorize('update', $vehicle);

        $validated = $request->validate([
            'make'         => 'required|string|max:100',
            'model'        => 'required|string|max:100',
            'year'         => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'plate_number' => 'required|string|max:20',
            'color'        => 'nullable|string|max:50',
            'notes'        => 'nullable|string|max:500',
        ]);

        $this->vehicleRepository->update($vehicle, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle updated successfully!',
            'vehicle' => $this->formatVehicle($vehicle->fresh()),
        ]);
    }

    public function destroy(Vehicle $vehicle): JsonResponse
    {
        $this->authorize('delete', $vehicle);

        $this->vehicleRepository->delete($vehicle);

        return response()->json([
            'success' => true,
            'message' => 'Vehicle removed successfully!',
        ]);
    }

    private function formatVehicle(Vehicle $vehicle): array
    {
        return [
            'id'           => $vehicle->id,
            'make'         => $vehicle->make,
            'model'        => $vehicle->model,
            'year'         => $vehicle->year,
            'plate_number' => $vehicle->plate_number,
            'color'        => $vehicle->color ?? '',
            'notes'        => $vehicle->notes ?? '',
            'display_name' => $vehicle->display_name,
        ];
    }
}
