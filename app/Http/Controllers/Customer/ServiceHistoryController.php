<?php

namespace App\Http\Controllers\Customer;

use App\DataTransferObjects\BookingBillingSummary;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ServiceHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // 1. Customer's registered vehicles for filter dropdown
        $vehicles = Vehicle::forUser($user->id)->orderBy('make')->orderBy('model')->get();

        // 2. Query customer bookings
        $query = Booking::forUser($user->id)
            ->with([
                'vehicle',
                'services.category',
                'bookingServices.service.category',
                'quotations.lineItems.product',
                'quotations.lineItems.service',
                'payments.proofs',
                'paintScope',
                'paintPanels.panel',
                'damagePhotos',
                'jobOrder.mechanic',
                'jobOrder.serviceUpdates' => fn ($q) => $q->visibleToCustomer()->with('photos'),
                'jobOrder.stageProgress.serviceStage',
                'statusLogs',
            ]);

        // Vehicle Filter
        $selectedVehicleId = $request->input('vehicle_id');
        if ($selectedVehicleId && is_numeric($selectedVehicleId)) {
            $query->where('vehicle_id', (int) $selectedVehicleId);
        }

        // Status Filter (default to completed for official service history, allow switching to all)
        $selectedStatus = $request->query('status', 'completed');
        if ($selectedStatus === 'completed') {
            $query->where('status', Booking::STATUS_COMPLETED);
        } elseif ($selectedStatus === 'in_progress') {
            $query->where('status', Booking::STATUS_IN_PROGRESS);
        } elseif ($selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        // Search Filter
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('booking_number', 'like', "%{$search}%")
                    ->orWhereHas('services', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('vehicle', function ($vq) use ($search) {
                        $vq->where('make', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('plate_number', 'like', "%{$search}%");
                    });
            });
        }

        $records = $query->latest('completed_at')
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        // Attach billing summary to each record for seamless cost & payment breakdown
        $records->getCollection()->transform(function (Booking $booking) {
            $booking->billingSummary = BookingBillingSummary::from($booking);
            return $booking;
        });

        // 3. Lifetime Stats for Customer
        $completedCount = Booking::forUser($user->id)->status(Booking::STATUS_COMPLETED)->count();
        $totalSpent = (float) Payment::where('user_id', $user->id)->verified()->sum('amount');
        $servicedVehiclesCount = Booking::forUser($user->id)
            ->whereNotNull('vehicle_id')
            ->distinct('vehicle_id')
            ->count('vehicle_id');
        $latestCompleted = Booking::forUser($user->id)
            ->status(Booking::STATUS_COMPLETED)
            ->latest('completed_at')
            ->first();

        return view('customer.service-history', [
            'records' => $records,
            'vehicles' => $vehicles,
            'selectedVehicleId' => $selectedVehicleId,
            'selectedStatus' => $selectedStatus,
            'search' => $search,
            'stats' => [
                'completedCount' => $completedCount,
                'totalSpent' => $totalSpent,
                'servicedVehiclesCount' => $servicedVehiclesCount,
                'latestServiceDate' => $latestCompleted?->completed_at ?? $latestCompleted?->updated_at,
            ],
        ]);
    }

    public function show(Booking $booking): View
    {
        // Enforce ownership
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to service record.');
        }

        $booking->loadMissing([
            'vehicle',
            'user',
            'services.category',
            'bookingServices.service.category',
            'quotations.lineItems.product',
            'quotations.lineItems.service',
            'payments.proofs',
            'paintScope',
            'paintPanels.panel',
            'damagePhotos',
            'jobOrder.mechanic',
            'jobOrder.serviceUpdates' => fn ($q) => $q->visibleToCustomer()->with('photos'),
            'jobOrder.stageProgress.serviceStage',
        ]);

        $summary = BookingBillingSummary::from($booking);

        return view('customer.service-record-print', [
            'booking' => $booking,
            'summary' => $summary,
        ]);
    }
}
