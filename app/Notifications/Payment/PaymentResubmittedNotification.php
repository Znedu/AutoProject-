<?php

namespace App\Notifications\Payment;

use App\Enums\NotificationType;
use App\Models\Booking;
use App\Notifications\BaseNotification;

class PaymentResubmittedNotification extends BaseNotification
{
    public function __construct(public Booking $booking) {}

    public function toArray(mixed $notifiable): array
    {
        return [
            'type' => NotificationType::PAYMENT_RESUBMITTED->value,
            'title' => 'Proof of Payment Resubmitted',
            'message' => "New proof of payment resubmitted for booking #{$this->booking->booking_number}.",
            'action_url' => route('admin.approvals.index'),
            'icon' => 'dollar-sign',
            'entity_type' => 'payment',
            'entity_id' => $this->booking->id,
        ];
    }
}
