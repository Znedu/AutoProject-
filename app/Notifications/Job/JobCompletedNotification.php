<?php

namespace App\Notifications\Job;

use App\Enums\NotificationType;
use App\Models\JobOrder;
use App\Notifications\BaseNotification;

class JobCompletedNotification extends BaseNotification
{
    public function __construct(public JobOrder $jobOrder) {}

    public function toArray(mixed $notifiable): array
    {
        $isStaff = method_exists($notifiable, 'isStaff') && $notifiable->isStaff();

        $actionUrl = $isStaff
            ? route('staff.jobs.index')
            : route('customer.track', ['booking_id' => $this->jobOrder->booking_id]);

        return [
            'type'        => NotificationType::JOB_COMPLETED->value,
            'title'       => 'Service Completed',
            'message'     => "Job order #{$this->jobOrder->job_number} has been completed.",
            'action_url'  => $actionUrl,
            'icon'        => 'check-circle',
            'entity_type' => 'job',
            'entity_id'   => $this->jobOrder->id,
        ];
    }
}
