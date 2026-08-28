<?php

namespace App\Http\Controllers\Customer;

use App\DataTransferObjects\BookingBillingSummary;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookingBillingController extends Controller
{
    public function show(Booking $booking): View
    {
        Gate::authorize('billing.view', $booking);

        $booking->loadMissing([
            'vehicle',
            'user',
            'bookingServices.service',
            'quotations.lineItems.product',
            'quotations.lineItems.service',
            'payments.proofs',
        ]);

        $summary = BookingBillingSummary::from($booking);

        return view('customer.booking-billing', [
            'booking' => $booking,
            'summary' => $summary,
        ]);
    }
}
