<?php

namespace App\Notifications\Booking;

use App\Enums\NotificationType;
use App\Models\Booking;
use App\Notifications\BaseNotification;

class BookingRejectedNotification extends BaseNotification
{
    public function __construct(public Booking $booking) {}

    public function toArray(mixed $notifiable): array
    {
        $reason = $this->booking->rejection_reason ? " Reason: {$this->booking->rejection_reason}" : '';

        return [
            'type'        => NotificationType::BOOKING_REJECTED->value,
            'title'       => 'Booking Rejected',
            'message'     => "Your booking #{$this->booking->booking_number} was rejected.{$reason}",
            'action_url'  => route('customer.bookings.index'),
            'icon'        => 'close',
            'entity_type' => 'booking',
            'entity_id'   => $this->booking->id,
        ];
    }
}
