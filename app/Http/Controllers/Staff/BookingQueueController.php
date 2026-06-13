<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\Booking\BookingApprovalService;
use Illuminate\Http\Request;

class BookingQueueController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['user', 'vehicle', 'services', 'payments'])
            ->latest()
            ->get()
            ->map(function ($booking) {
                $payment = $booking->payments->where('type', Payment::TYPE_RESERVATION_FEE)->first();
                
                $min = $booking->services->sum('min_cost');
                $max = $booking->services->sum('max_cost');
                $estimatedCost = $min > 0 ? '₱' . number_format($min) . ' - ₱' . number_format($max) : 'TBD';

                return [
                    'id' => $booking->id,
                    'customer' => $booking->customer_name ?? ($booking->user?->name ?? 'Unknown'),
                    'contact' => $booking->contact_number ?? ($booking->user?->phone ?? 'N/A'),
                    'service' => $booking->services->first()?->name ?? 'Custom Service',
                    'vehicle' => $booking->vehicle ? "{$booking->vehicle->make} {$booking->vehicle->model} {$booking->vehicle->year}" : 'Unknown',
                    'plateNumber' => $booking->vehicle?->plate_number ?? 'N/A',
                    'preferredDate' => $booking->preferred_date ? $booking->preferred_date->format('F d, Y') : 'N/A',
                    'preferredTime' => $booking->preferred_time ? $booking->preferred_time->format('g:i A') : 'N/A',
                    'status' => $booking->status,
                    'estimatedCost' => $estimatedCost,
                    'notes' => $booking->notes ?? '',
                    'reservationFee' => [
                        'amount' => $payment ? (float)$payment->amount : 200.00,
                        'paid' => $payment ? in_array($payment->status, [Payment::STATUS_SUBMITTED, Payment::STATUS_VERIFIED]) : false,
                        'paymentMethod' => $payment ? strtoupper($payment->method) : 'N/A',
                        'referenceNumber' => $payment?->reference_number ?? 'N/A',
                        'paymentDate' => $payment && $payment->paid_at ? $payment->paid_at->format('F d, Y') : 'N/A',
                        'paymentTime' => $payment && $payment->paid_at ? $payment->paid_at->format('g:i A') : 'N/A',
                        'status' => $payment?->status ?? 'pending',
                    ]
                ];
            });

        $verifiedPayments = [];
        foreach ($bookings as $b) {
            if ($b['reservationFee']['status'] === Payment::STATUS_VERIFIED) {
                $verifiedPayments[] = $b['id'];
            }
        }

        return view('staff.booking-queue', [
            'bookings' => $bookings,
            'verifiedPayments' => $verifiedPayments,
        ]);
    }

    public function verifyPayment(Booking $booking, BookingApprovalService $approval)
    {
        try {
            $approval->verifyReservationPayment($booking, auth()->user());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function approve(Booking $booking, BookingApprovalService $approval)
    {
        try {
            $approval->approve($booking, auth()->user());
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function reject(Request $request, Booking $booking, BookingApprovalService $approval)
    {
        try {
            $reason = $request->input('reason', 'Rejected by staff');
            $approval->reject($booking, auth()->user(), $reason);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function schedule(Request $request, Booking $booking)
    {
        try {
            $booking->update([
                'status' => Booking::STATUS_SCHEDULED,
                'scheduled_date' => $request->input('scheduled_date', $booking->preferred_date),
                'scheduled_time' => $request->input('scheduled_time', $booking->preferred_time),
            ]);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }
}
