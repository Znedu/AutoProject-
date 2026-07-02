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
            ->with(['booking.services', 'booking.vehicle', 'booking.user', 'stageProgress', 'serviceUpdates.photos', 'serviceUpdates.user'])
            ->get()
            ->map(function ($job) {
                $booking = $job->booking;
                $vehicle = $booking?->vehicle;
                $user = $booking?->user;
                
                // Find the active service stage ID
                $currentStageId = $job->stageProgress->where('is_current', true)->first()?->service_stage_id;

                // Map DB status to prototype UI status filters (e.g. paused -> pending)
                $uiStatus = $job->status;
                if ($uiStatus === JobOrder::STATUS_PAUSED || $uiStatus === JobOrder::STATUS_ASSIGNED) {
                    $uiStatus = 'pending';
                } elseif ($uiStatus === JobOrder::STATUS_IN_PROGRESS) {
                    $uiStatus = 'in-progress';
                }

                // Map service updates
                $serviceUpdates = $job->serviceUpdates->map(function ($update) {
                    $photos = $update->photos->map(fn ($p) => [
                        'url'     => $p->url ?? '/storage/' . $p->file_path,
                        'caption' => $p->caption ?? ''
                    ])->values()->toArray();

                    return [
                        'id'       => $update->id,
                        'message'  => $update->message,
                        'date'     => $update->created_at->format('M d, Y - g:i A'),
                        'mechanic' => $update->user?->name ?? 'Mechanic',
                        'photos'   => $photos
                    ];
                })->values()->toArray();

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
                    'currentStageId' => $currentStageId,
                    'serviceUpdates' => $serviceUpdates,
                ];
            });

        $stages = \App\Models\ServiceStage::orderBy('sort_order')->get();

        return view('mechanic.jobs', [
            'jobs' => $jobs,
            'stages' => $stages,
        ]);
    }

    public function start(JobOrder $job)
    {
        // Ensure job is assigned to mechanic
        if ($job->mechanic_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $newProgress = max($job->progress_percent, 5);
        $job->update([
            'status'           => JobOrder::STATUS_IN_PROGRESS,
            'started_at'       => now(),
            'progress_percent' => $newProgress,
        ]);

        return response()->json(['success' => true, 'progress' => $newProgress]);
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
