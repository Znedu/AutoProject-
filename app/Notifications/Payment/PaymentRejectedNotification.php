<?php

namespace App\Notifications\Payment;

use App\Enums\NotificationType;
use App\Models\Booking;
use App\Notifications\BaseNotification;

class PaymentRejectedNotification extends BaseNotification
{
    public function __construct(public Booking $booking, public string $reason) {}

    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => NotificationType::PAYMENT_REJECTED->value,
            'title' => 'Proof of Payment Rejected',
            'message' => "Proof of payment for booking #{$this->booking->booking_number} was rejected: {$this->reason}. Please resubmit proof.",
            'action_url' => route('customer.payment', $this->booking->id),
            'icon' => 'info',
            'entity_type' => 'payment',
            'entity_id' => $this->booking->id,
        ];
    }

    public function toTxtFlow(mixed $notifiable): ?string
    {
        return "[AutoProject+] Payment Issue: Payment proof for booking #{$this->booking->booking_number} was rejected: {$this->reason}. Please resubmit proof via your portal.";
    }
}
