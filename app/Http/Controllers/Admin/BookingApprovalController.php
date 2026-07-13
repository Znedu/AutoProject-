<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\ApproveBookingRequest;
use App\Http\Requests\Booking\RejectBookingRequest;
use App\Http\Requests\Payment\RejectPaymentRequest;
use App\Models\Booking;
use App\Services\Booking\BookingApprovalService;
use App\Services\Booking\PaymentVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingApprovalController extends Controller
{
    public function index(Request $request): View
    {
        // Default to the new primary workflow tab
        $status = $request->query('status', 'pending_payment_verification');

        $bookings = $this->bookingQuery()
            ->when($status !== 'all', fn ($query) => $query->status($status))
            ->when(
                $status === 'pending_payment_verification',
                fn ($q) => $q->pendingPaymentVerification()
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'pending_verification' => Booking::query()->pendingPaymentVerification()->count(),
            'requires_resubmission' => Booking::query()->paymentRequiresResubmission()->count(),
            'confirmed_today'      => Booking::query()
                ->status(Booking::STATUS_CONFIRMED)
                ->whereDate('approved_at', today())
                ->count(),
            'total_week'           => Booking::query()
                ->where('created_at', '>=', now()->startOfWeek())
                ->count(),
        ];

        return view('admin.approvals', [
            'bookings'       => $bookings,
            'stats'          => $stats,
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
            'bookings'       => $bookings,
            'selectedFilter' => $status,
        ]);
    }

    // -------------------------------------------------------------------------
    // New payment verification actions
    // -------------------------------------------------------------------------

    /**
     * Verify payment AND confirm booking in one action.
     */
    public function confirmPayment(Request $request, Booking $booking, PaymentVerificationService $verification): RedirectResponse
    {
        $this->authorize('verifyPayment', $booking);

        try {
            $verification->confirm($booking, $request->user());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return back()->with('error', 'No pending payment found for this booking.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Booking {$booking->booking_number} payment verified and confirmed.");
    }

    /**
     * Reject a customer's payment proof, with mandatory reason.
     */
    public function rejectPayment(RejectPaymentRequest $request, Booking $booking, PaymentVerificationService $verification): RedirectResponse
    {
        $this->authorize('verifyPayment', $booking);

        try {
            $updated = $verification->rejectPayment($booking, $request->user(), $request->validated('reason'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return back()->with('error', 'No pending payment found for this booking.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = $updated->status === Booking::STATUS_CANCELLED
            ? "Booking {$booking->booking_number} was cancelled after maximum payment attempts."
            : "Payment rejected for booking {$booking->booking_number}. Customer notified to resubmit.";

        return back()->with('success', $message);
    }

    // -------------------------------------------------------------------------
    // Legacy actions (kept for walk-in & backward compatibility)
    // -------------------------------------------------------------------------

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
            'quotations'  => fn ($query) => $query->latestVersion()->limit(1),
            'payments'    => fn ($query) => $query->reservationFees()->latest()->limit(1),
            'payments.proofs',
            'statusLogs'  => fn ($query) => $query->latest('created_at')->limit(10),
        ]);
    }
}
