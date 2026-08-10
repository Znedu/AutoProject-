<?php

namespace App\Notifications\Booking;

use App\Enums\NotificationType;
use App\Models\Booking;
use App\Notifications\BaseNotification;

class BookingConfirmedNotification extends BaseNotification
{
    public function __construct(public Booking $booking) {}

    public function toArray(mixed $notifiable): array
    {
        return [
            'type'        => NotificationType::BOOKING_CONFIRMED->value,
            'title'       => 'Booking Confirmed',
            'message'     => "Your booking #{$this->booking->booking_number} is confirmed.",
            'action_url'  => route('customer.track', ['booking_id' => $this->booking->id]),
            'icon'        => 'check-circle',
            'entity_type' => 'booking',
            'entity_id'   => $this->booking->id,
        ];
    }
}
