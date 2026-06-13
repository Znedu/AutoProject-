<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use App\Models\ServiceUpdate;
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

        return view('mechanic.notes', [
            'jobs' => $jobs,
            'notes' => $notes,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'jobId' => 'required|exists:job_orders,id',
            'note' => 'required|string',
        ]);

        $job = JobOrder::findOrFail($request->jobId);

        // Ensure job is assigned to mechanic
        if ($job->mechanic_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $update = ServiceUpdate::create([
            'job_order_id' => $job->id,
            'user_id' => auth()->id(),
            'message' => $request->note,
            'is_visible_to_customer' => true,
        ]);

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
