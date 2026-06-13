<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ServiceCategory;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Stats
        $upcomingCount = Booking::forUser($userId)
            ->whereIn('status', [
                Booking::STATUS_PENDING,
                Booking::STATUS_APPROVED,
                Booking::STATUS_WAITING_PAYMENT,
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_SCHEDULED
            ])
            ->count();

        $activeCount = Booking::forUser($userId)
            ->status(Booking::STATUS_IN_PROGRESS)
            ->count();

        $completedCount = Booking::forUser($userId)
            ->status(Booking::STATUS_COMPLETED)
            ->count();

        $supportCount = SupportTicket::where('user_id', $userId)
            ->where('status', '!=', 'closed')
            ->count();

        // Service categories data
        $serviceCategories = ServiceCategory::active()
            ->ordered()
            ->with(['services' => fn($q) => $q->active()])
            ->get()
            ->map(function ($category) {
                $services = $category->services;
                $minPrice = $services->min('min_cost') ?? 0;
                $maxPrice = $services->max('max_cost') ?? 0;

                return [
                    'id' => $category->slug,
                    'name' => $category->name,
                    'icon' => $category->icon ?? 'Wrench',
                    'color' => $category->color ?? '#E63946',
                    'services_count' => $services->count(),
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                ];
            });

        // Upcoming bookings
        $upcomingBookings = Booking::forUser($userId)
            ->whereIn('status', [
                Booking::STATUS_PENDING,
                Booking::STATUS_APPROVED,
                Booking::STATUS_WAITING_PAYMENT,
                Booking::STATUS_CONFIRMED,
                Booking::STATUS_SCHEDULED
            ])
            ->with(['services', 'vehicle'])
            ->latest()
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'service' => $booking->services->first()?->name ?? 'Custom Service',
                    'vehicle' => $booking->vehicle ? "{$booking->vehicle->make} {$booking->vehicle->model} {$booking->vehicle->year}" : 'N/A',
                    'date' => $booking->scheduled_date ? $booking->scheduled_date->format('F d, Y') : $booking->preferred_date->format('F d, Y'),
                    'status' => $booking->status,
                ];
            });

        // Active services
        $activeServices = Booking::forUser($userId)
            ->status(Booking::STATUS_IN_PROGRESS)
            ->with(['services', 'vehicle', 'jobOrder'])
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'service' => $booking->services->first()?->name ?? 'Custom Service',
                    'vehicle' => $booking->vehicle ? "{$booking->vehicle->make} {$booking->vehicle->model} {$booking->vehicle->year}" : 'N/A',
                    'progress' => $booking->jobOrder?->progress_percent ?? 0,
                    'status' => $booking->jobOrder?->display_status ?? 'Service Ongoing',
                ];
            });

        return view('customer.dashboard', [
            'upcomingCount' => $upcomingCount,
            'activeCount' => $activeCount,
            'completedCount' => $completedCount,
            'supportCount' => $supportCount,
            'serviceCategories' => $serviceCategories,
            'upcomingBookings' => $upcomingBookings,
            'activeServices' => $activeServices,
        ]);
    }
}
