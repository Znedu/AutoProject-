<?php

namespace App\Services\Billing;

use App\DataTransferObjects\BookingBillingSummary;
use App\Models\Booking;
use App\Models\BusinessSetting;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\QuotationLineItem;

class BookingBillingCalculatorService
{
    public function calculate(Booking $booking): BookingBillingSummary
    {
        // Use eager-loaded relations if available, or query
        $quotations = $booking->relationLoaded('quotations')
            ? $booking->quotations
            : $booking->quotations()->with(['lineItems.product', 'lineItems.service'])->get();

        $finalQuotation = $quotations
            ->where('type', Quotation::TYPE_FINAL)
            ->sortByDesc('version')
            ->first();

        $initialQuotation = $quotations
            ->where('type', Quotation::TYPE_INITIAL_ESTIMATE)
            ->sortByDesc('version')
            ->first();

        $lineItems = $finalQuotation ? $finalQuotation->lineItems : collect();

        // Group line items
        $grouped = [
            QuotationLineItem::ITEM_TYPE_SERVICE => $lineItems->where('item_type', QuotationLineItem::ITEM_TYPE_SERVICE)->values(),
            QuotationLineItem::ITEM_TYPE_ADDITIONAL_SERVICE => $lineItems->where('item_type', QuotationLineItem::ITEM_TYPE_ADDITIONAL_SERVICE)->values(),
            QuotationLineItem::ITEM_TYPE_PRODUCT => $lineItems->where('item_type', QuotationLineItem::ITEM_TYPE_PRODUCT)->values(),
            QuotationLineItem::ITEM_TYPE_MATERIAL => $lineItems->where('item_type', QuotationLineItem::ITEM_TYPE_MATERIAL)->values(),
            QuotationLineItem::ITEM_TYPE_LABOR => $lineItems->where('item_type', QuotationLineItem::ITEM_TYPE_LABOR)->values(),
            QuotationLineItem::ITEM_TYPE_DISCOUNT => $lineItems->where('item_type', QuotationLineItem::ITEM_TYPE_DISCOUNT)->values(),
            QuotationLineItem::ITEM_TYPE_FEE => $lineItems->where('item_type', QuotationLineItem::ITEM_TYPE_FEE)->values(),
        ];

        // Subtotals calculation
        $servicesSubtotal = (float) $lineItems
            ->whereIn('item_type', [
                QuotationLineItem::ITEM_TYPE_SERVICE,
                QuotationLineItem::ITEM_TYPE_ADDITIONAL_SERVICE,
            ])
            ->sum(fn (QuotationLineItem $item) => $item->line_total_computed ?? 0);

        $productsSubtotal = (float) $lineItems
            ->whereIn('item_type', [
                QuotationLineItem::ITEM_TYPE_PRODUCT,
                QuotationLineItem::ITEM_TYPE_MATERIAL,
            ])
            ->sum(fn (QuotationLineItem $item) => $item->line_total_computed ?? 0);

        $laborSubtotal = (float) $lineItems
            ->where('item_type', QuotationLineItem::ITEM_TYPE_LABOR)
            ->sum(fn (QuotationLineItem $item) => $item->line_total_computed ?? 0);

        $discountsTotal = (float) abs($lineItems
            ->where('item_type', QuotationLineItem::ITEM_TYPE_DISCOUNT)
            ->sum(fn (QuotationLineItem $item) => $item->line_total_computed ?? 0));

        $feesSubtotal = (float) $lineItems
            ->where('item_type', QuotationLineItem::ITEM_TYPE_FEE)
            ->sum(fn (QuotationLineItem $item) => $item->line_total_computed ?? 0);

        // Payments
        $payments = $booking->relationLoaded('payments')
            ? $booking->payments
            : $booking->payments()->get();

        $verifiedPayments = $payments->where('status', Payment::STATUS_VERIFIED);

        $reservationFeePaid = (float) $verifiedPayments
            ->where('type', Payment::TYPE_RESERVATION_FEE)
            ->sum('amount');

        // Check if explicit fee line item exists in quotation line items
        $hasFeeLineItem = $lineItems->contains(function (QuotationLineItem $item) {
            $desc = strtolower($item->description);
            return $item->item_type === QuotationLineItem::ITEM_TYPE_FEE
                || str_contains($desc, 'convenience')
                || str_contains($desc, 'reservation');
        });

        if (! $hasFeeLineItem && $reservationFeePaid > 0) {
            $feesSubtotal += $reservationFeePaid;
        }

        $finalTotal = round(max(0, $servicesSubtotal + $productsSubtotal + $laborSubtotal + $feesSubtotal - $discountsTotal), 2);

        $depositsPaid = (float) $verifiedPayments
            ->where('type', Payment::TYPE_DEPOSIT)
            ->sum('amount');

        $finalPaymentsPaid = (float) $verifiedPayments
            ->where('type', Payment::TYPE_FINAL_PAYMENT)
            ->sum('amount');

        $refundsTotal = (float) $verifiedPayments
            ->where('type', Payment::TYPE_REFUND)
            ->sum('amount');

        $creditsReservationFee = (bool) filter_var(BusinessSetting::getValue('reservation_fee_credits_toward_total', true), FILTER_VALIDATE_BOOLEAN);

        $creditablePaid = round(max(0, ($creditsReservationFee ? $reservationFeePaid : 0) + $depositsPaid + $finalPaymentsPaid - $refundsTotal), 2);
        $balanceDue = round(max(0, $finalTotal - $creditablePaid), 2);

        $currency = $finalQuotation?->currency ?? 'PHP';
        $isFinalized = $finalQuotation?->isFinalized() ?? false;

        $sortedVerifiedPayments = $verifiedPayments
            ->sortByDesc(fn (Payment $p) => $p->paid_at?->timestamp ?? $p->created_at?->timestamp ?? $p->id)
            ->values();

        return new BookingBillingSummary(
            servicesSubtotal: round($servicesSubtotal, 2),
            productsSubtotal: round($productsSubtotal, 2),
            laborSubtotal: round($laborSubtotal, 2),
            discountsTotal: round($discountsTotal, 2),
            feesSubtotal: round($feesSubtotal, 2),
            finalTotal: $finalTotal,
            reservationFeePaid: round($reservationFeePaid, 2),
            depositsPaid: round($depositsPaid, 2),
            finalPaymentsPaid: round($finalPaymentsPaid, 2),
            refundsTotal: round($refundsTotal, 2),
            totalPaid: $creditablePaid,
            balanceDue: $balanceDue,
            currency: $currency,
            lineItemsGrouped: $grouped,
            paymentsBreakdown: $sortedVerifiedPayments,
            isFinalized: $isFinalized,
            creditsReservationFee: $creditsReservationFee,
            finalQuotation: $finalQuotation,
            initialQuotation: $initialQuotation,
        );
    }
}
