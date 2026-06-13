<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\JobOrder;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index()
    {
        $jobs = JobOrder::forMechanic(auth()->id())
            ->with(['booking.services', 'booking.vehicle', 'booking.user'])
            ->get()
            ->map(function ($job) {
                $booking = $job->booking;
                $vehicle = $booking?->vehicle;
                $user = $booking?->user;
                
                // Map DB status to prototype UI status filters (e.g. paused -> pending)
                $uiStatus = $job->status;
                if ($uiStatus === JobOrder::STATUS_PAUSED || $uiStatus === JobOrder::STATUS_ASSIGNED) {
                    $uiStatus = 'pending';
                }

                return [
                    'id' => $job->id,
                    'customer' => $booking?->customer_name ?? ($user?->name ?? 'Unknown'),
                    'contactNumber' => $booking?->contact_number ?? ($user?->phone ?? 'N/A'),
                    'service' => $booking?->services->first()?->name ?? 'Custom Service',
                    'vehicle' => $vehicle ? "{$vehicle->make} {$vehicle->model} {$vehicle->year}" : 'Unknown',
                    'plateNumber' => $vehicle?->plate_number ?? 'N/A',
                    'status' => $uiStatus,
                    'progress' => (int) $job->progress_percent,
                    'startDate' => $job->started_at ? $job->started_at->format('F d, Y') : 'Not Started',
                    'estimatedCompletion' => $job->estimated_completion_date ? $job->estimated_completion_date->format('F d, Y') : 'TBD',
                    'priority' => ucfirst($job->priority),
                ];
            });

        return view('mechanic.jobs', [
            'jobs' => $jobs,
        ]);
    }

    public function start(JobOrder $job)
    {
        // Ensure job is assigned to mechanic
        if ($job->mechanic_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $job->update([
            'status' => JobOrder::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'progress_percent' => max($job->progress_percent, 5),
        ]);

        return response()->json(['success' => true]);
    }

    public function pause(JobOrder $job)
    {
        if ($job->mechanic_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $job->update([
            'status' => JobOrder::STATUS_PAUSED,
            'paused_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function complete(JobOrder $job)
    {
        if ($job->mechanic_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $job->update([
            'status' => JobOrder::STATUS_COMPLETED,
            'completed_at' => now(),
            'progress_percent' => 100,
        ]);

        if ($job->booking) {
            $job->booking->update([
                'status' => Booking::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
