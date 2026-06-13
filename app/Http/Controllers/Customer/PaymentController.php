<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BusinessSetting;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function show($bookingId)
    {
        $userId = auth()->id();
        $booking = Booking::forUser($userId)
            ->where('id', $bookingId)
            ->with(['services', 'vehicle', 'quotations' => fn($q) => $q->latestVersion()->limit(1)])
            ->firstOrFail();

        // Get reservation fee from business settings
        $fee = BusinessSetting::getValue('reservation_fee', 200.00);
        $gcashNumber = BusinessSetting::getValue('gcash_account_number', '0912-345-6789');
        $mayaNumber = BusinessSetting::getValue('maya_account_number', '0917-888-9999');

        // Estimate total
        $quotation = $booking->quotations->first();
        $totalEstimate = 'To be computed';
        if ($quotation) {
            $totalEstimate = $quotation->total_range_display;
        } else {
            // Compute range from booking services
            $min = $booking->services->sum('min_cost');
            $max = $booking->services->sum('max_cost');
            if ($min > 0) {
                $totalEstimate = '₱' . number_format($min) . ' - ₱' . number_format($max);
            }
        }

        $bookingDetails = [
            'id' => $booking->id,
            'booking_number' => $booking->booking_number,
            'service' => $booking->services->first()?->name ?? 'Custom Customization',
            'vehicle' => $booking->vehicle ? "{$booking->vehicle->make} {$booking->vehicle->model} {$booking->vehicle->year}" : 'Unknown',
            'reservationFee' => '₱' . number_format($fee, 2),
            'totalEstimate' => $totalEstimate,
            'gcashNumber' => $gcashNumber,
            'mayaNumber' => $mayaNumber,
        ];

        return view('customer.payment', [
            'bookingId' => $bookingId,
            'bookingDetails' => $bookingDetails,
        ]);
    }

    public function submit(Request $request, $bookingId)
    {
        // Handle screenshot upload and payment verification request
        // In prototype, it redirects. We will support standard redirect or JSON response
        return response()->json([
            'success' => true,
            'message' => 'Payment submitted successfully! Your booking is now awaiting verification.',
        ]);
    }
}
