<?php

namespace App\Notifications\Booking;

use App\Enums\NotificationType;
use App\Models\Booking;
use App\Notifications\BaseNotification;

class BookingCancelledNotification extends BaseNotification
{
    public function __construct(public Booking $booking) {}

    public function toArray(mixed $notifiable): array
    {
        return [
            'type'        => NotificationType::BOOKING_CANCELLED->value,
            'title'       => 'Booking Cancelled',
            'message'     => "Booking #{$this->booking->booking_number} was cancelled by the customer.",
            'action_url'  => route('admin.bookings.history'),
            'icon'        => 'close',
            'entity_type' => 'booking',
            'entity_id'   => $this->booking->id,
        ];
    }
}
