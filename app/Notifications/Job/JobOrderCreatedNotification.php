<?php

namespace App\Notifications\Job;

use App\Enums\NotificationType;
use App\Models\JobOrder;
use App\Notifications\BaseNotification;

class JobOrderCreatedNotification extends BaseNotification
{
    public function __construct(public JobOrder $jobOrder) {}

    public function toArray(mixed $notifiable): array
    {
        $bookingNumber = $this->jobOrder->booking?->booking_number ?? 'N/A';

        return [
            'type'        => NotificationType::JOB_ORDER_CREATED->value,
            'title'       => 'Job Order Created',
            'message'     => "Job order #{$this->jobOrder->job_number} created for booking #{$bookingNumber}.",
            'action_url'  => route('admin.jobs.index'),
            'icon'        => 'wrench',
            'entity_type' => 'job',
            'entity_id'   => $this->jobOrder->id,
        ];
    }
}
