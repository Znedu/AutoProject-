<?php

namespace App\Notifications\Job;

use App\Enums\NotificationType;
use App\Models\ServiceUpdate;
use App\Notifications\BaseNotification;

class ServiceUpdateNotification extends BaseNotification
{
    public function __construct(public ServiceUpdate $update) {}

    public function toArray(mixed $notifiable): array
    {
        $bookingId = $this->update->jobOrder?->booking_id;

        return [
            'type'        => NotificationType::SERVICE_UPDATE->value,
            'title'       => 'Service Update',
            'message'     => "New service update posted for your vehicle: {$this->update->message}",
            'action_url'  => route('customer.track', ['booking_id' => $bookingId]),
            'icon'        => 'file-text',
            'entity_type' => 'job',
            'entity_id'   => $this->update->job_order_id,
        ];
    }
}
