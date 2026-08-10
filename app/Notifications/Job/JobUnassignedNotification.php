<?php

namespace App\Notifications\Job;

use App\Enums\NotificationType;
use App\Models\JobOrder;
use App\Notifications\BaseNotification;

class JobUnassignedNotification extends BaseNotification
{
    public function __construct(public JobOrder $jobOrder) {}

    public function toArray(mixed $notifiable): array
    {
        $isAdmin = method_exists($notifiable, 'isAdmin') && $notifiable->isAdmin();

        $message = $isAdmin
            ? "Job order #{$this->jobOrder->job_number} was unassigned."
            : "You were unassigned from job order #{$this->jobOrder->job_number}.";

        $actionUrl = $isAdmin
            ? route('admin.jobs.index')
            : route('mechanic.jobs.index');

        return [
            'type'        => NotificationType::JOB_UNASSIGNED->value,
            'title'       => 'Job Unassigned',
            'message'     => $message,
            'action_url'  => $actionUrl,
            'icon'        => 'wrench',
            'entity_type' => 'job',
            'entity_id'   => $this->jobOrder->id,
        ];
    }
}
