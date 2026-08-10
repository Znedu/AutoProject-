<?php

namespace App\Notifications\Booking;

use App\Enums\NotificationType;
use App\Models\Booking;
use App\Notifications\BaseNotification;

class BookingApprovedNotification extends BaseNotification
{
    public function __construct(public Booking $booking) {}

    public function toArray(mixed $notifiable): array
    {
        return [
            'type'        => NotificationType::BOOKING_APPROVED->value,
            'title'       => 'Booking Approved',
            'message'     => "Your booking #{$this->booking->booking_number} has been approved.",
            'action_url'  => route('customer.track', ['booking_id' => $this->booking->id]),
            'icon'        => 'check-circle',
            'entity_type' => 'booking',
            'entity_id'   => $this->booking->id,
        ];
    }
}
