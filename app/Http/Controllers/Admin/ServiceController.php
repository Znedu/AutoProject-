<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    protected ServiceRepository $serviceRepository;

    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }

    public function index()
    {
        $services = $this->serviceRepository->all()->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description ?? '',
                'minCost' => (float) $service->min_cost,
                'maxCost' => (float) $service->max_cost,
                'duration' => $service->duration_label ?? '1 day',
                'status' => ucfirst($service->status),
                'code' => $service->code,
                'service_category_id' => $service->service_category_id,
            ];
        });

        $categories = ServiceCategory::active()->ordered()->get();

        return view('admin.services', [
            'services' => $services,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'code' => 'nullable|string|max:50|unique:services,code',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'minCost' => 'required|numeric|min:0',
            'maxCost' => 'required|numeric|min:0|gte:minCost',
            'duration' => 'required|string|max:255',
        ]);

        $code = $validated['code'] ?? Str::slug($validated['name']);
        
        // Ensure code uniqueness if auto-generated
        $originalCode = $code;
        $counter = 1;
        while (Service::where('code', $code)->exists()) {
            $code = $originalCode . '-' . $counter;
            $counter++;
        }

        $service = $this->serviceRepository->create([
            'service_category_id' => $validated['service_category_id'],
            'code' => $code,
            'name' => $validated['name'],
            'description' => $validated['description'],
            'min_cost' => $validated['minCost'],
            'max_cost' => $validated['maxCost'],
            'duration_label' => $validated['duration'],
            'status' => Service::STATUS_ACTIVE,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service added successfully!',
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description ?? '',
                'minCost' => (float) $service->min_cost,
                'maxCost' => (float) $service->max_cost,
                'duration' => $service->duration_label ?? '1 day',
                'status' => ucfirst($service->status),
                'code' => $service->code,
                'service_category_id' => $service->service_category_id,
            ]
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'service_category_id' => 'required|exists:service_categories,id',
            'code' => 'required|string|max:50|unique:services,code,' . $service->id,
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'minCost' => 'required|numeric|min:0',
            'maxCost' => 'required|numeric|min:0|gte:minCost',
            'duration' => 'required|string|max:255',
        ]);

        $this->serviceRepository->update($service, [
            'service_category_id' => $validated['service_category_id'],
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'min_cost' => $validated['minCost'],
            'max_cost' => $validated['maxCost'],
            'duration_label' => $validated['duration'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service updated successfully!',
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description ?? '',
                'minCost' => (float) $service->min_cost,
                'maxCost' => (float) $service->max_cost,
                'duration' => $service->duration_label ?? '1 day',
                'status' => ucfirst($service->status),
                'code' => $service->code,
                'service_category_id' => $service->service_category_id,
            ]
        ]);
    }

    public function toggleStatus(Service $service)
    {
        $newStatus = $service->status === Service::STATUS_ACTIVE 
            ? Service::STATUS_INACTIVE 
            : Service::STATUS_ACTIVE;

        $this->serviceRepository->update($service, [
            'status' => $newStatus,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Service status updated successfully!',
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description ?? '',
                'minCost' => (float) $service->min_cost,
                'maxCost' => (float) $service->max_cost,
                'duration' => $service->duration_label ?? '1 day',
                'status' => ucfirst($service->status),
                'code' => $service->code,
                'service_category_id' => $service->service_category_id,
            ]
        ]);
    }

    public function destroy(Service $service)
    {
        $this->serviceRepository->delete($service);

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully!',
        ]);
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:service_categories,name',
        ]);

        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;
        while (ServiceCategory::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $maxOrder = ServiceCategory::max('sort_order') ?? 0;

        $category = ServiceCategory::create([
            'name'       => $validated['name'],
            'slug'       => $slug,
            'icon'       => 'settings',
            'color'      => '#457B9D',
            'sort_order' => $maxOrder + 1,
            'is_active'  => true,
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Category created successfully!',
            'category' => [
                'id'   => $category->id,
                'name' => $category->name,
            ],
        ]);
    }
}
