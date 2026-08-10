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
            'type'        => NotificationType::PAYMENT_REJECTED->value,
            'title'       => 'Payment Proof Rejected',
            'message'     => "Payment proof for booking #{$this->booking->booking_number} was rejected: {$this->reason}. Please resubmit proof.",
            'action_url'  => route('customer.payment', $this->booking->id),
            'icon'        => 'info',
            'entity_type' => 'payment',
            'entity_id'   => $this->booking->id,
        ];
    }
}
