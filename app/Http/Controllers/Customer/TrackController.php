<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\JobOrder;
use App\Models\ServiceStage;
use App\Models\ServiceUpdate;
use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        // Get all trackable bookings for the selector (active + completed, ordered newest first)
        $allBookings = Booking::forUser($userId)
            ->with(['services', 'vehicle'])
            ->latest()
            ->get()
            ->filter(fn ($b) => $b->status !== Booking::STATUS_REJECTED && $b->status !== Booking::STATUS_CANCELLED)
            ->values();

        // Determine which booking to show
        $bookingId = $request->query('booking_id');

        $booking = null;
        if ($bookingId) {
            $booking = Booking::forUser($userId)
                ->where('id', $bookingId)
                ->with(['services', 'vehicle', 'jobOrder.stageProgress.serviceStage', 'jobOrder.serviceUpdates.photos', 'jobOrder.serviceUpdates.user'])
                ->first();
        }

        if (!$booking) {
            // Prefer the most recent active booking
            $booking = Booking::forUser($userId)
                ->active()
                ->with(['services', 'vehicle', 'jobOrder.stageProgress.serviceStage', 'jobOrder.serviceUpdates.photos', 'jobOrder.serviceUpdates.user'])
                ->latest()
                ->first();

            // Fallback to any recent booking
            if (!$booking) {
                $booking = Booking::forUser($userId)
                    ->with(['services', 'vehicle', 'jobOrder.stageProgress.serviceStage', 'jobOrder.serviceUpdates.photos', 'jobOrder.serviceUpdates.user'])
                    ->latest()
                    ->first();
            }
        }

        // Build the booking selector list
        $bookingSelector = $allBookings->map(fn ($b) => [
            'id'             => $b->id,
            'booking_number' => $b->booking_number,
            'service'        => $b->services->first()?->name ?? 'Custom Service',
            'vehicle'        => $b->vehicle ? "{$b->vehicle->make} {$b->vehicle->model} {$b->vehicle->year}" : 'Unknown',
            'status'         => $b->status,
        ])->toArray();

        // Build empty fallback if no booking found at all
        if (!$booking) {
            $trackingData = [
                'service'             => null,
                'vehicle'             => null,
                'bookingId'           => null,
                'currentStage'        => 0,
                'progress'            => 0,
                'stages'              => [
                    ['name' => 'Booking Confirmed', 'date' => 'Confirmed',  'completed' => false],
                    ['name' => 'Received',          'date' => 'Pending',    'completed' => false],
                    ['name' => 'Inspection',        'date' => 'Pending',    'completed' => false],
                    ['name' => 'In Progress',       'date' => 'Pending',    'completed' => false],
                    ['name' => 'Quality Check',     'date' => 'Pending',    'completed' => false],
                    ['name' => 'Ready for Pickup',  'date' => 'Pending',    'completed' => false],
                ],
                'notes'               => [],
                'estimated_completion' => null,
                'selected_booking_id' => null,
            ];
        } else {
            $jobOrder        = $booking->jobOrder;
            $dbStages        = ServiceStage::orderBy('sort_order')->get();

            // "Booking Confirmed" is a static pre-mechanic stage — always completed
            // once the customer can see the tracking page (booking is active/confirmed).
            $confirmedAt = $booking->updated_at ?? $booking->created_at;
            $stages = [
                [
                    'name'      => 'Booking Confirmed',
                    'date'      => $confirmedAt->format('M d, Y - g:i A'),
                    'completed' => true,
                ],
            ];
            // DB mechanic stages start at index 1
            $currentStageIndex = 0;

            foreach ($dbStages as $stage) {
                $progress = $jobOrder
                    ? $jobOrder->stageProgress->where('service_stage_id', $stage->id)->first()
                    : null;

                $completed = $progress ? (bool) $progress->is_completed : false;
                $isCurrent = $progress ? (bool) $progress->is_current  : false;

                if ($isCurrent) {
                    // +1 because index 0 is the static 'Booking Confirmed' stage
                    $currentStageIndex = count($stages);
                }

                $isJobCompleted = $jobOrder && $jobOrder->status === JobOrder::STATUS_COMPLETED;

                $stages[] = [
                    'name'      => $stage->name,
                    'date'      => $progress && $progress->completed_at
                        ? $progress->completed_at->format('M d, Y - g:i A')
                        : ($isCurrent ? 'In Progress' : 'Pending'),
                    'completed' => $completed || $isJobCompleted,
                ];
            }

            // If fully completed, mark all stages + move indicator to last
            if ($jobOrder && $jobOrder->status === JobOrder::STATUS_COMPLETED) {
                $currentStageIndex = count($stages) - 1;
                foreach ($stages as &$stg) {
                    $stg['completed'] = true;
                }
                unset($stg);
            }

            // Build service updates feed
            $notes = [];
            if ($jobOrder) {
                $updates = ServiceUpdate::where('job_order_id', $jobOrder->id)
                    ->where('is_visible_to_customer', true)
                    ->with(['photos', 'user'])
                    ->latest()
                    ->get();

                foreach ($updates as $update) {
                    // Use model accessor for consistent URL resolution
                    $photos = $update->photos->map(fn ($photo) => [
                        'url'     => $photo->url ?? asset($photo->file_path),
                        'caption' => $photo->caption ?? '',
                    ])->toArray();

                    $notes[] = [
                        'date'    => $update->created_at->format('F d, Y'),
                        'time'    => $update->created_at->format('g:i A'),
                        'message' => $update->message,
                        'author'  => 'Mechanic: ' . ($update->user?->name ?? 'Team Mechanic'),
                        'photos'  => $photos,
                    ];
                }
            }

            $trackingData = [
                'service'             => $booking->services->first()?->name ?? 'Custom Service',
                'vehicle'             => $booking->vehicle
                    ? "{$booking->vehicle->make} {$booking->vehicle->model} {$booking->vehicle->year}"
                    : 'Unknown Vehicle',
                'bookingId'           => $booking->booking_number,
                'currentStage'        => $currentStageIndex,
                'progress'            => $jobOrder ? (int) $jobOrder->progress_percent : 0,
                'stages'              => $stages,
                'notes'               => $notes,
                'estimated_completion' => $jobOrder && $jobOrder->estimated_completion_date
                    ? $jobOrder->estimated_completion_date->format('F d, Y')
                    : null,
                'selected_booking_id' => $booking->id,
            ];
        }

        return view('customer.track', [
            'trackingData'    => $trackingData,
            'bookingSelector' => $bookingSelector,
        ]);
    }

    /**
     * JSON endpoint for live polling – returns the same trackingData structure as the
     * index method but as JSON so the frontend can refresh without a full page reload.
     */
    public function refresh(Request $request)
    {
        $userId    = auth()->id();
        $bookingId = $request->query('booking_id');

        $booking = null;
        if ($bookingId) {
            $booking = Booking::forUser($userId)
                ->where('id', $bookingId)
                ->with(['services', 'vehicle', 'jobOrder.stageProgress.serviceStage', 'jobOrder.serviceUpdates.photos', 'jobOrder.serviceUpdates.user'])
                ->first();
        }

        if (!$booking) {
            $booking = Booking::forUser($userId)
                ->active()
                ->with(['services', 'vehicle', 'jobOrder.stageProgress.serviceStage', 'jobOrder.serviceUpdates.photos', 'jobOrder.serviceUpdates.user'])
                ->latest()
                ->first();
        }

        if (!$booking) {
            return response()->json([
                'service'              => null,
                'vehicle'              => null,
                'bookingId'            => null,
                'currentStage'         => 0,
                'progress'             => 0,
                'stages'               => [],
                'notes'                => [],
                'estimated_completion' => null,
                'selected_booking_id'  => null,
            ]);
        }

        $jobOrder        = $booking->jobOrder;
        $dbStages        = ServiceStage::orderBy('sort_order')->get();

        // "Booking Confirmed" is a static pre-mechanic stage — always completed.
        $confirmedAt = $booking->updated_at ?? $booking->created_at;
        $stages = [
            [
                'name'      => 'Booking Confirmed',
                'date'      => $confirmedAt->format('M d, Y - g:i A'),
                'completed' => true,
            ],
        ];
        $currentStageIndex = 0;

        foreach ($dbStages as $stage) {
            $progress = $jobOrder
                ? $jobOrder->stageProgress->where('service_stage_id', $stage->id)->first()
                : null;

            $completed = $progress ? (bool) $progress->is_completed : false;
            $isCurrent = $progress ? (bool) $progress->is_current  : false;

            if ($isCurrent) {
                $currentStageIndex = count($stages);
            }

            $isJobCompleted = $jobOrder && $jobOrder->status === JobOrder::STATUS_COMPLETED;

            $stages[] = [
                'name'      => $stage->name,
                'date'      => $progress && $progress->completed_at
                    ? $progress->completed_at->format('M d, Y - g:i A')
                    : ($isCurrent ? 'In Progress' : 'Pending'),
                'completed' => $completed || $isJobCompleted,
            ];
        }

        if ($jobOrder && $jobOrder->status === JobOrder::STATUS_COMPLETED) {
            $currentStageIndex = count($stages) - 1;
            foreach ($stages as &$stg) {
                $stg['completed'] = true;
            }
            unset($stg);
        }

        $notes = [];
        if ($jobOrder) {
            $updates = ServiceUpdate::where('job_order_id', $jobOrder->id)
                ->where('is_visible_to_customer', true)
                ->with(['photos', 'user'])
                ->latest()
                ->get();

            foreach ($updates as $update) {
                $photos = $update->photos->map(fn ($photo) => [
                    'url'     => $photo->url ?? asset($photo->file_path),
                    'caption' => $photo->caption ?? '',
                ])->toArray();

                $notes[] = [
                    'date'    => $update->created_at->format('F d, Y'),
                    'time'    => $update->created_at->format('g:i A'),
                    'message' => $update->message,
                    'author'  => 'Mechanic: ' . ($update->user?->name ?? 'Team Mechanic'),
                    'photos'  => $photos,
                ];
            }
        }

        return response()->json([
            'service'              => $booking->services->first()?->name ?? 'Custom Service',
            'vehicle'              => $booking->vehicle
                ? "{$booking->vehicle->make} {$booking->vehicle->model} {$booking->vehicle->year}"
                : 'Unknown Vehicle',
            'bookingId'            => $booking->booking_number,
            'currentStage'         => $currentStageIndex,
            'progress'             => $jobOrder ? (int) $jobOrder->progress_percent : 0,
            'stages'               => $stages,
            'notes'                => $notes,
            'estimated_completion' => $jobOrder && $jobOrder->estimated_completion_date
                ? $jobOrder->estimated_completion_date->format('F d, Y')
                : null,
            'selected_booking_id'  => $booking->id,
        ]);
    }
}
