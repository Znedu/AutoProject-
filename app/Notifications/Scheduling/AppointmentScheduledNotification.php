<?php

namespace App\Notifications\Scheduling;

use App\Enums\NotificationType;
use App\Models\Booking;
use App\Notifications\BaseNotification;

class AppointmentScheduledNotification extends BaseNotification
{
    public function __construct(public Booking $booking) {}

    public function toArray(mixed $notifiable): array
    {
        $isMechanic = method_exists($notifiable, 'isMechanic') && $notifiable->isMechanic();

        $dateStr = $this->booking->scheduled_date ? $this->booking->scheduled_date->format('M d, Y') : '';
        $timeStr = $this->booking->scheduled_time ? $this->booking->scheduled_time->format('g:i A') : '';
        $when = trim("{$dateStr} {$timeStr}");

        $message = $isMechanic
            ? "Assigned appointment #{$this->booking->booking_number} is scheduled for {$when}."
            : "Your appointment for booking #{$this->booking->booking_number} is scheduled for {$when}.";

        $actionUrl = $isMechanic
            ? route('mechanic.jobs.index')
            : route('customer.track', ['booking_id' => $this->booking->id]);

        return [
            'type'        => NotificationType::APPOINTMENT_SCHEDULED->value,
            'title'       => 'Appointment Scheduled',
            'message'     => $message,
            'action_url'  => $actionUrl,
            'icon'        => 'calendar',
            'entity_type' => 'booking',
            'entity_id'   => $this->booking->id,
        ];
    }
}
