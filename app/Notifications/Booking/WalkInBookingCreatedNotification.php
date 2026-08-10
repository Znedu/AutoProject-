<?php

namespace App\Notifications\Booking;

use App\Enums\NotificationType;
use App\Models\Booking;
use App\Notifications\BaseNotification;

class WalkInBookingCreatedNotification extends BaseNotification
{
    public function __construct(public Booking $booking) {}

    public function toArray(mixed $notifiable): array
    {
        $actionUrl = method_exists($notifiable, 'isAdmin') && $notifiable->isAdmin()
            ? route('admin.approvals.index')
            : route('customer.track', ['booking_id' => $this->booking->id]);

        return [
            'type'        => NotificationType::WALKIN_BOOKING_CREATED->value,
            'title'       => 'Walk-In Booking Created',
            'message'     => "Walk-in booking #{$this->booking->booking_number} was created for {$this->booking->customer_name}.",
            'action_url'  => $actionUrl,
            'icon'        => 'user-plus',
            'entity_type' => 'booking',
            'entity_id'   => $this->booking->id,
        ];
    }
}
