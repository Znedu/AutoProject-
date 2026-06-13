<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::all()->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description ?? '',
                'minCost' => (float) $service->min_cost,
                'maxCost' => (float) $service->max_cost,
                'duration' => $service->duration_label ?? '1 day',
                'status' => ucfirst($service->status),
            ];
        });

        return view('admin.services', [
            'services' => $services,
        ]);
    }
}
