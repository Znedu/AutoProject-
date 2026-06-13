<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\ApproveBookingRequest;
use App\Http\Requests\Booking\RejectBookingRequest;
use App\Models\Booking;
use App\Services\Booking\BookingApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'pending');

        $bookings = $this->bookingQuery()
            ->when($status !== 'all', fn ($query) => $query->status($status))
            ->when($status === 'pending', fn ($query) => $query->pending())
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'pending' => Booking::query()->pending()->count(),
            'approved_today' => Booking::query()
                ->status(Booking::STATUS_APPROVED)
                ->whereDate('approved_at', today())
                ->count(),
            'rejected' => Booking::query()->status(Booking::STATUS_REJECTED)->count(),
            'total_week' => Booking::query()
                ->where('created_at', '>=', now()->startOfWeek())
                ->count(),
        ];

        return view('admin.approvals', [
            'bookings' => $bookings,
            'stats' => $stats,
            'selectedFilter' => $status,
        ]);
    }

    public function history(Request $request): View
    {
        $status = $request->query('status', 'all');

        $bookings = $this->bookingQuery()
            ->when($status !== 'all', fn ($query) => $query->status($status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.bookings.history', [
            'bookings' => $bookings,
            'selectedFilter' => $status,
        ]);
    }

    public function approve(ApproveBookingRequest $request, Booking $booking, BookingApprovalService $approval): RedirectResponse
    {
        try {
            $approval->approve($booking, $request->user());
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Booking '.$booking->booking_number.' approved successfully.');
    }

    public function reject(RejectBookingRequest $request, Booking $booking, BookingApprovalService $approval): RedirectResponse
    {
        try {
            $approval->reject($booking, $request->user(), $request->validated('reason'));
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Booking '.$booking->booking_number.' rejected.');
    }

    public function verifyPayment(Request $request, Booking $booking, BookingApprovalService $approval): RedirectResponse
    {
        $this->authorize('verifyPayment', $booking);

        try {
            $approval->verifyReservationPayment($booking, $request->user());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return back()->with('error', 'No pending reservation payment found for this booking.');
        }

        return back()->with('success', 'Reservation fee verified for booking '.$booking->booking_number.'.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Booking>
     */
    protected function bookingQuery()
    {
        return Booking::query()->with([
            'user',
            'vehicle',
            'bookingServices.service',
            'quotations' => fn ($query) => $query->latestVersion()->limit(1),
            'payments' => fn ($query) => $query->reservationFees()->latest()->limit(1),
            'statusLogs' => fn ($query) => $query->latest('created_at')->limit(10),
        ]);
    }
}
