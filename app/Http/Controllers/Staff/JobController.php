<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobController extends Controller
{
    /**
     * Read-only view of all job orders.
     * Staff cannot assign mechanics, edit assignments, or approve/reject bookings.
     */
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'all');

        $jobs = JobOrder::with([
            'booking.user',
            'booking.vehicle',
            'booking.services',
            'mechanic',
            'stageProgress.serviceStage',
            'serviceUpdates',
        ])
            ->when($filter === 'unassigned', fn ($q) => $q->where('status', JobOrder::STATUS_PENDING))
            ->when($filter === 'assigned',   fn ($q) => $q->where('status', JobOrder::STATUS_ASSIGNED))
            ->when($filter === 'in_progress', fn ($q) => $q->where('status', JobOrder::STATUS_IN_PROGRESS))
            ->when($filter === 'completed',  fn ($q) => $q->where('status', JobOrder::STATUS_COMPLETED))
            ->latest()
            ->get()
            ->map(function ($job) {
                $booking = $job->booking;
                $vehicle = $booking?->vehicle;
                $user    = $booking?->user;

                return [
                    'id'           => $job->id,
                    'job_number'   => $job->job_number,
                    'customer'     => $booking?->customer_name ?? ($user?->name ?? 'Unknown'),
                    'contact'      => $booking?->contact_number ?? ($user?->phone ?? 'N/A'),
                    'service'      => $booking?->services->first()?->name ?? 'Custom Service',
                    'vehicle'      => $vehicle
                        ? "{$vehicle->make} {$vehicle->model} {$vehicle->year}"
                        : 'Unknown',
                    'plate_number' => $vehicle?->plate_number ?? 'N/A',
                    'mechanic'     => $job->mechanic?->name ?? 'Unassigned',
                    'status'       => $job->status,
                    'priority'     => ucfirst($job->priority),
                    'progress'     => (int) $job->progress_percent,
                    'started_at'   => $job->started_at?->format('F d, Y') ?? 'Not Started',
                    'estimated_completion' => $job->estimated_completion_date?->format('F d, Y') ?? 'TBD',
                    'is_walk_in'   => $booking?->is_walk_in ?? false,
                ];
            });

        $stats = [
            'total'       => JobOrder::count(),
            'unassigned'  => JobOrder::where('status', JobOrder::STATUS_PENDING)->count(),
            'in_progress' => JobOrder::where('status', JobOrder::STATUS_IN_PROGRESS)->count(),
            'completed'   => JobOrder::where('status', JobOrder::STATUS_COMPLETED)->count(),
        ];

        return view('staff.jobs', [
            'jobs'           => $jobs,
            'stats'          => $stats,
            'selectedFilter' => $filter,
        ]);
    }
}
