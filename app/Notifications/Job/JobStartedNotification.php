<?php

namespace App\Notifications\Job;

use App\Enums\NotificationType;
use App\Models\JobOrder;
use App\Notifications\BaseNotification;

class JobStartedNotification extends BaseNotification
{
    public function __construct(public JobOrder $jobOrder) {}

    public function toArray(mixed $notifiable): array
    {
        return [
            'type'        => NotificationType::JOB_STARTED->value,
            'title'       => 'Service Started',
            'message'     => "Work has started on your vehicle for job order #{$this->jobOrder->job_number}.",
            'action_url'  => route('customer.track', ['booking_id' => $this->jobOrder->booking_id]),
            'icon'        => 'wrench',
            'entity_type' => 'job',
            'entity_id'   => $this->jobOrder->id,
        ];
    }
}
