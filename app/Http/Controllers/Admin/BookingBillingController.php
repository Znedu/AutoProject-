<?php

namespace App\Http\Controllers\Admin;

use App\DataTransferObjects\BookingBillingSummary;
use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\FinalizeBillingRequest;
use App\Http\Requests\Billing\RecordFinalPaymentRequest;
use App\Http\Requests\Billing\StoreBillingLineItemRequest;
use App\Http\Requests\Billing\UpdateBillingLineItemRequest;
use App\Models\Booking;
use App\Models\QuotationLineItem;
use App\Services\Billing\BookingBillingService;
use App\Services\Billing\BookingFinalPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BookingBillingController extends Controller
{
    public function show(Booking $booking, BookingBillingService $billingService): View
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

        $allowedAutoDraftStatuses = [
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_SCHEDULED,
            Booking::STATUS_IN_PROGRESS,
            Booking::STATUS_COMPLETED,
        ];

        if (! $booking->finalQuotation && in_array($booking->status, $allowedAutoDraftStatuses, true)) {
            $billingService->createFinalDraft($booking, auth()->user());
            $booking->load(['quotations.lineItems.product', 'quotations.lineItems.service']);
        }

        $summary = BookingBillingSummary::from($booking);

        return view('admin.bookings.billing', [
            'booking' => $booking,
            'summary' => $summary,
        ]);
    }

    public function storeLine(
        StoreBillingLineItemRequest $request,
        Booking $booking,
        BookingBillingService $billingService
    ): RedirectResponse {
        $finalQuotation = $booking->finalQuotation;
        if (! $finalQuotation) {
            $finalQuotation = $billingService->createFinalDraft($booking, $request->user());
        }

        $billingService->addLineItem($finalQuotation, $request->validated(), $request->user());

        return back()->with('success', 'Line item added successfully.');
    }

    public function updateLine(
        UpdateBillingLineItemRequest $request,
        Booking $booking,
        QuotationLineItem $lineItem,
        BookingBillingService $billingService
    ): RedirectResponse {
        if ($lineItem->quotation->booking_id !== $booking->id) {
            abort(404, 'Line item not found on this booking.');
        }

        $billingService->updateLineItem($lineItem, $request->validated());

        return back()->with('success', 'Line item updated successfully.');
    }

    public function destroyLine(
        Request $request,
        Booking $booking,
        QuotationLineItem $lineItem,
        BookingBillingService $billingService
    ): RedirectResponse {
        Gate::authorize('billing.manage', $booking);

        if ($lineItem->quotation->booking_id !== $booking->id) {
            abort(404, 'Line item not found on this booking.');
        }

        $billingService->removeLineItem($lineItem);

        return back()->with('success', 'Line item removed successfully.');
    }

    public function finalize(
        FinalizeBillingRequest $request,
        Booking $booking,
        BookingBillingService $billingService
    ): RedirectResponse {
        try {
            $billingService->finalize($booking, $request->user(), $request->input('notes'));

            return back()->with('success', 'Final billing has been calculated and locked successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function recordPayment(
        RecordFinalPaymentRequest $request,
        Booking $booking,
        BookingFinalPaymentService $paymentService
    ): RedirectResponse {
        try {
            $paymentService->record(
                $booking,
                $request->user(),
                $request->validated(),
                $request->file('payment_proof')
            );

            return back()->with('success', 'Payment recorded successfully.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
