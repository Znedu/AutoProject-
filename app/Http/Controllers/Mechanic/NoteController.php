<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use App\Models\ServiceUpdate;
use App\Models\Booking;
use App\Models\ServiceStage;
use App\Models\ServiceStageProgress;
use App\Models\ServiceUpdatePhoto;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // Get mechanic active jobs for dropdown
        $jobs = JobOrder::forMechanic($userId)
            ->assigned()
            ->with(['booking.services', 'booking.vehicle'])
            ->get()
            ->map(function ($job) {
                $booking = $job->booking;
                $serviceName = $booking?->services->first()?->name ?? 'Custom Service';
                $vehicleName = $booking?->vehicle ? "{$booking->vehicle->make} {$booking->vehicle->model} {$booking->vehicle->year}" : 'Unknown';
                
                return [
                    'value' => (string) $job->id,
                    'label' => "{$serviceName} - {$vehicleName}",
                ];
            })
            ->toArray();

        array_unshift($jobs, ['value' => '', 'label' => 'Select a job...']);

        // Get notes history
        $notes = ServiceUpdate::whereHas('jobOrder', function ($q) use ($userId) {
                $q->where('mechanic_id', $userId);
            })
            ->with(['jobOrder.booking.services', 'jobOrder.booking.vehicle', 'user'])
            ->latest()
            ->get()
            ->map(function ($update) use ($userId) {
                $booking = $update->jobOrder?->booking;
                $serviceName = $booking?->services->first()?->name ?? 'Custom Service';
                $vehicleName = $booking?->vehicle ? "{$booking->vehicle->make} {$booking->vehicle->model} {$booking->vehicle->year}" : 'Unknown';

                return [
                    'id' => $update->id,
                    'job' => "{$serviceName} - {$vehicleName}",
                    'note' => $update->message,
                    'date' => $update->created_at->format('F d, Y - g:i A'),
                    'mechanic' => $update->user_id === $userId ? 'You' : ($update->user?->name ?? 'Mechanic'),
                ];
            });

        // Fetch all active service stages for the notes form
        $stages = ServiceStage::orderBy('sort_order')->get();

        return view('mechanic.notes', [
            'jobs' => $jobs,
            'notes' => $notes,
            'stages' => $stages,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jobId' => 'required|exists:job_orders,id',
            'note' => 'required|string',
            'progress' => 'nullable|integer|min:0|max:100',
            'stage_id' => 'nullable|exists:service_stages,id',
            'photos.*' => 'nullable|image|max:10240',
        ]);

        $job = JobOrder::findOrFail($request->jobId);

        // Ensure job is assigned to mechanic
        if ($job->mechanic_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Create the ServiceUpdate progress note
        $update = ServiceUpdate::create([
            'job_order_id' => $job->id,
            'user_id' => auth()->id(),
            'message' => $request->note,
            'is_visible_to_customer' => true,
        ]);

        // Process file uploads if files exist
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $index => $file) {
                $path = $file->store('service-updates', 'public');
                ServiceUpdatePhoto::create([
                    'service_update_id' => $update->id,
                    'disk' => 'public',
                    'file_path' => $path,
                    'caption' => $file->getClientOriginalName(),
                    'sort_order' => $index,
                ]);
            }
        }

        // Auto-calculate progress percentage based on active service stage
        if ($request->filled('stage_id')) {
            $selectedStageId = (int) $request->stage_id;
            $allStages = ServiceStage::orderBy('sort_order')->get();
            $stagesCount = $allStages->count();
            $selectedStageIndex = 0;
            foreach ($allStages as $index => $s) {
                if ($s->id === $selectedStageId) {
                    $selectedStageIndex = $index;
                    break;
                }
            }
            $job->progress_percent = (int) round((($selectedStageIndex + 1) / $stagesCount) * 100);
        } elseif ($request->filled('progress')) {
            $job->progress_percent = (int) $request->progress;
        }

        // Auto start the job if it hasn't been started yet
        if ($job->status === JobOrder::STATUS_PENDING || $job->status === JobOrder::STATUS_PAUSED) {
            $job->status = JobOrder::STATUS_IN_PROGRESS;
            $job->started_at = $job->started_at ?? now();
        }

        // Auto complete the job if progress is 100%
        if ($job->progress_percent >= 100) {
            $job->status = JobOrder::STATUS_COMPLETED;
            $job->completed_at = now();
            if ($job->booking) {
                $job->booking->update([
                    'status' => Booking::STATUS_COMPLETED,
                    'completed_at' => now(),
                ]);
            }
        }
        $job->save();

        // Update stage status pipeline
        if ($request->filled('stage_id')) {
            $selectedStageId = (int) $request->stage_id;
            $stages = ServiceStage::orderBy('sort_order')->get();
            $reachedSelected = false;

            foreach ($stages as $stage) {
                $progressRec = ServiceStageProgress::updateOrCreate(
                    ['job_order_id' => $job->id, 'service_stage_id' => $stage->id]
                );

                if ($stage->id === $selectedStageId) {
                    $progressRec->update([
                        'is_completed' => false,
                        'is_current' => true,
                        'completed_at' => null,
                    ]);
                    $reachedSelected = true;
                } elseif (!$reachedSelected) {
                    $progressRec->update([
                        'is_completed' => true,
                        'is_current' => false,
                        'completed_at' => $progressRec->completed_at ?? now(),
                    ]);
                } else {
                    $progressRec->update([
                        'is_completed' => false,
                        'is_current' => false,
                        'completed_at' => null,
                    ]);
                }
            }
        }

        $booking = $job->booking;
        $serviceName = $booking?->services->first()?->name ?? 'Custom Service';
        $vehicleName = $booking?->vehicle ? "{$booking->vehicle->make} {$booking->vehicle->model} {$booking->vehicle->year}" : 'Unknown';

        return response()->json([
            'success' => true,
            'note' => [
                'id' => $update->id,
                'job' => "{$serviceName} - {$vehicleName}",
                'note' => $update->message,
                'date' => $update->created_at->format('F d, Y - g:i A'),
                'mechanic' => 'You',
            ]
        ]);
    }
}
