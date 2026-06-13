<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ServiceStage;
use App\Models\ServiceStageProgress;
use App\Models\ServiceUpdate;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        // Get booking id from request, or get latest booking
        $bookingId = $request->query('booking_id');
        
        $booking = null;
        if ($bookingId) {
            $booking = Booking::forUser($userId)
                ->where('id', $bookingId)
                ->with(['services', 'vehicle', 'jobOrder.stageProgress.serviceStage', 'jobOrder.serviceUpdates.photos'])
                ->first();
        }

        if (!$booking) {
            // Fetch most recent active booking first, else any recent booking
            $booking = Booking::forUser($userId)
                ->active()
                ->with(['services', 'vehicle', 'jobOrder.stageProgress.serviceStage', 'jobOrder.serviceUpdates.photos'])
                ->latest()
                ->first();

            if (!$booking) {
                $booking = Booking::forUser($userId)
                    ->with(['services', 'vehicle', 'jobOrder.stageProgress.serviceStage', 'jobOrder.serviceUpdates.photos'])
                    ->latest()
                    ->first();
            }
        }

        // Build mock tracking data structure to match template if booking is not found (fallback)
        if (!$booking) {
            $trackingData = [
                'service' => 'No active service',
                'vehicle' => 'No vehicle registered',
                'bookingId' => 'N/A',
                'currentStage' => 0,
                'stages' => [
                    ['name' => 'Booking Confirmed', 'date' => 'Pending', 'completed' => false],
                    ['name' => 'Vehicle Received', 'date' => 'Pending', 'completed' => false],
                    ['name' => 'Service Ongoing', 'date' => 'Pending', 'completed' => false],
                    ['name' => 'Quality Inspection', 'date' => 'Pending', 'completed' => false],
                    ['name' => 'Completed', 'date' => 'Pending', 'completed' => false],
                ],
                'notes' => [],
                'estimated_completion' => 'N/A',
            ];
        } else {
            // Get stages
            $jobOrder = $booking->jobOrder;
            $dbStages = ServiceStage::orderBy('sort_order')->get();
            $stages = [];
            $currentStageIndex = 0;

            foreach ($dbStages as $index => $stage) {
                // Find if there is progress recorded
                $progress = $jobOrder 
                    ? $jobOrder->stageProgress->where('service_stage_id', $stage->id)->first()
                    : null;

                $completed = $progress ? (bool) $progress->is_completed : false;
                $isCurrent = $progress ? (bool) $progress->is_current : false;
                
                if ($isCurrent) {
                    $currentStageIndex = $index;
                }

                // Stage display names mapped to prototype names
                $stageNames = [
                    'received' => 'Booking Confirmed',
                    'inspection' => 'Vehicle Received',
                    'in-progress' => 'Service Ongoing',
                    'quality-check' => 'Quality Inspection',
                    'ready-for-pickup' => 'Completed'
                ];
                $displayName = $stageNames[$stage->slug] ?? $stage->name;

                $stages[] = [
                    'name' => $displayName,
                    'date' => $progress && $progress->completed_at 
                        ? $progress->completed_at->format('M d, Y - g:i A') 
                        : ($isCurrent ? 'In Progress' : 'Pending'),
                    'completed' => $completed || ($jobOrder && $jobOrder->status === JobOrder::STATUS_COMPLETED),
                ];
            }

            // If job is fully completed, ensure all stages show completed
            if ($jobOrder && $jobOrder->status === JobOrder::STATUS_COMPLETED) {
                $currentStageIndex = count($stages) - 1;
                foreach ($stages as &$stg) {
                    $stg['completed'] = true;
                }
            }

            // Get updates / notes
            $notes = [];
            if ($jobOrder) {
                $updates = ServiceUpdate::where('job_order_id', $jobOrder->id)
                    ->where('is_visible_to_customer', true)
                    ->with(['photos', 'user'])
                    ->latest()
                    ->get();

                foreach ($updates as $update) {
                    $photos = [];
                    foreach ($update->photos as $photo) {
                        $photos[] = [
                            'url' => asset($photo->file_path),
                            'caption' => $photo->caption ?? '',
                        ];
                    }

                    $notes[] = [
                        'date' => $update->created_at->format('F d, Y'),
                        'time' => $update->created_at->format('g:i A'),
                        'message' => $update->message,
                        'author' => 'Mechanic: ' . ($update->user->name ?? 'John Santos'),
                        'photos' => $photos,
                    ];
                }
            }

            $trackingData = [
                'service' => $booking->services->first()?->name ?? 'Custom Service',
                'vehicle' => $booking->vehicle ? "{$booking->vehicle->make} {$booking->vehicle->model} {$booking->vehicle->year}" : 'Unknown Vehicle',
                'bookingId' => $booking->booking_number,
                'currentStage' => $currentStageIndex,
                'stages' => $stages,
                'notes' => $notes,
                'estimated_completion' => $jobOrder && $jobOrder->estimated_completion_date 
                    ? $jobOrder->estimated_completion_date->format('F d, Y') 
                    : 'To be determined',
            ];
        }

        return view('customer.track', [
            'trackingData' => $trackingData,
        ]);
    }
}
