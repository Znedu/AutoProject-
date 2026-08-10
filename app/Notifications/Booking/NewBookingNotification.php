<?php

namespace App\Notifications\Booking;

use App\Enums\NotificationType;
use App\Models\Booking;
use App\Notifications\BaseNotification;

class NewBookingNotification extends BaseNotification
{
    public function __construct(public Booking $booking) {}

    public function toArray(mixed $notifiable): array
    {
        return [
            'type'        => NotificationType::NEW_BOOKING->value,
            'title'       => 'New Booking Submitted',
            'message'     => "New booking #{$this->booking->booking_number} submitted by {$this->booking->customer_name}.",
            'action_url'  => route('admin.approvals.index'),
            'icon'        => 'clipboard-list',
            'entity_type' => 'booking',
            'entity_id'   => $this->booking->id,
        ];
    }
}
