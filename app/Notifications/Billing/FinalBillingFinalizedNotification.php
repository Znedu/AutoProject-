<?php

namespace App\Notifications\Billing;

use App\Enums\NotificationType;
use App\Models\Booking;
use App\Models\Quotation;
use App\Notifications\BaseNotification;

class FinalBillingFinalizedNotification extends BaseNotification
{
    public function __construct(
        public Booking $booking,
        public Quotation $quotation,
    ) {}

    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => NotificationType::FINAL_BILLING_FINALIZED->value,
            'title' => 'Final Billing Ready',
            'message' => "The final billing statement for booking #{$this->booking->booking_number} is ready. Total: ₱".number_format((float) $this->quotation->final_total, 2),
            'action_url' => route('customer.bookings.billing', $this->booking),
            'icon' => 'receipt',
            'entity_type' => 'booking',
            'entity_id' => $this->booking->id,
        ];
    }

    public function toTxtFlow(mixed $notifiable): ?string
    {
        $amount = number_format((float) $this->quotation->final_total, 2);
        return "[AutoProject+] Billing Statement Ready: The final billing for booking #{$this->booking->booking_number} is ₱{$amount}. Please check your account to review details.";
    }
}
