<?php

namespace App\Notifications\Payment;

use App\Enums\NotificationType;
use App\Models\Booking;
use App\Notifications\BaseNotification;

class BookingAutoCancelledNotification extends BaseNotification
{
    public function __construct(public Booking $booking) {}

    public function toArray(mixed $notifiable): array
    {
        $actionUrl = method_exists($notifiable, 'isAdmin') && $notifiable->isAdmin()
            ? route('admin.bookings.history')
            : route('customer.bookings.index');

        return [
            'type'        => NotificationType::BOOKING_AUTO_CANCELLED->value,
            'title'       => 'Booking Automatically Cancelled',
            'message'     => "Booking #{$this->booking->booking_number} was automatically cancelled after maximum failed payment verification attempts.",
            'action_url'  => $actionUrl,
            'icon'        => 'close',
            'entity_type' => 'booking',
            'entity_id'   => $this->booking->id,
        ];
    }
}
