<?php

namespace App\Notifications\Job;

use App\Enums\NotificationType;
use App\Models\JobOrder;
use App\Notifications\BaseNotification;

class JobAssignedNotification extends BaseNotification
{
    public function __construct(public JobOrder $jobOrder) {}

    public function toArray(mixed $notifiable): array
    {
        $isMechanic = method_exists($notifiable, 'isMechanic') && $notifiable->isMechanic();

        $message = $isMechanic
            ? "You have been assigned to job order #{$this->jobOrder->job_number}."
            : "A mechanic has been assigned to your service job #{$this->jobOrder->job_number}.";

        $actionUrl = $isMechanic
            ? route('mechanic.jobs.index')
            : route('customer.track', ['booking_id' => $this->jobOrder->booking_id]);

        return [
            'type'        => NotificationType::JOB_ASSIGNED->value,
            'title'       => 'Mechanic Assigned',
            'message'     => $message,
            'action_url'  => $actionUrl,
            'icon'        => 'wrench',
            'entity_type' => 'job',
            'entity_id'   => $this->jobOrder->id,
        ];
    }
}
