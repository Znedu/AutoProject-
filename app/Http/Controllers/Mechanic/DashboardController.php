<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\JobOrder;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Stats
        $assignedJobsCount = JobOrder::forMechanic($userId)->assigned()->count();
        $inProgressCount = JobOrder::forMechanic($userId)->status(JobOrder::STATUS_IN_PROGRESS)->count();
        $completedTodayCount = JobOrder::forMechanic($userId)
            ->status(JobOrder::STATUS_COMPLETED)
            ->whereDate('completed_at', today())
            ->count();
        $pendingStartCount = JobOrder::forMechanic($userId)
            ->whereIn('status', [JobOrder::STATUS_ASSIGNED, JobOrder::STATUS_PAUSED])
            ->count();

        // Current Assigned Jobs
        $assignedJobs = JobOrder::forMechanic($userId)
            ->assigned()
            ->with(['booking.services', 'booking.vehicle', 'booking.user'])
            ->get()
            ->map(function ($job) {
                $booking = $job->booking;
                $vehicle = $booking?->vehicle;
                
                $uiStatus = $job->status;
                if ($uiStatus === JobOrder::STATUS_PAUSED || $uiStatus === JobOrder::STATUS_ASSIGNED) {
                    $uiStatus = 'pending';
                }

                return [
                    'id' => $job->id,
                    'customer' => $booking?->customer_name ?? ($booking->user?->name ?? 'Unknown'),
                    'service' => $booking?->services->first()?->name ?? 'Custom Service',
                    'vehicle' => $vehicle ? "{$vehicle->make} {$vehicle->model} {$vehicle->year}" : 'Unknown',
                    'status' => $uiStatus,
                    'priority' => ucfirst($job->priority),
                ];
            });

        return view('mechanic.dashboard', [
            'assignedJobsCount' => $assignedJobsCount,
            'inProgressCount' => $inProgressCount,
            'completedTodayCount' => $completedTodayCount,
            'pendingStartCount' => $pendingStartCount,
            'assignedJobs' => $assignedJobs,
        ]);
    }
}
