<?php

namespace App\DataTransferObjects;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Services\Billing\BookingBillingCalculatorService;
use Illuminate\Support\Collection;

class BookingBillingSummary
{
    /**
     * @param  array<string, Collection<int, QuotationLineItem>>  $lineItemsGrouped
     * @param  Collection<int, Payment>  $paymentsBreakdown
     */
    public function __construct(
        public readonly float $servicesSubtotal,
        public readonly float $productsSubtotal,
        public readonly float $laborSubtotal,
        public readonly float $discountsTotal,
        public readonly float $feesSubtotal,
        public readonly float $finalTotal,
        public readonly float $reservationFeePaid,
        public readonly float $depositsPaid,
        public readonly float $finalPaymentsPaid,
        public readonly float $refundsTotal,
        public readonly float $totalPaid,
        public readonly float $balanceDue,
        public readonly string $currency,
        public readonly array $lineItemsGrouped,
        public readonly Collection $paymentsBreakdown,
        public readonly bool $isFinalized,
        public readonly bool $creditsReservationFee = true,
        public readonly ?Quotation $finalQuotation = null,
        public readonly ?Quotation $initialQuotation = null,
    ) {}

    public static function from(Booking $booking): self
    {
        return app(BookingBillingCalculatorService::class)->calculate($booking);
    }
}
