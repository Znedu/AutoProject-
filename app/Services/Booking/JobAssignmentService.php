<?php

namespace App\Services\Booking;

use App\Enums\RoleSlug;
use App\Models\JobOrder;
use App\Models\User;
use InvalidArgumentException;

class JobAssignmentService
{
    /**
     * Assign a mechanic to a pending job order.
     *
     * @param  array{mechanic_id: int, priority?: string, estimated_completion_date?: string|null, internal_notes?: string|null}  $data
     */
    public function assign(JobOrder $job, User $admin, array $data): JobOrder
    {
        $mechanic = User::findOrFail($data['mechanic_id']);

        if (! $mechanic->isMechanic()) {
            throw new InvalidArgumentException('The selected user is not a mechanic.');
        }

        if (! in_array($job->status, [JobOrder::STATUS_PENDING, JobOrder::STATUS_ASSIGNED], true)) {
            throw new InvalidArgumentException(
                'Only pending or already-assigned jobs can be (re)assigned.'
            );
        }

        $job->update([
            'mechanic_id'               => $mechanic->id,
            'assigned_by'               => $admin->id,
            'assigned_at'               => now(),
            'status'                    => JobOrder::STATUS_ASSIGNED,
            'priority'                  => $data['priority'] ?? $job->priority,
            'estimated_completion_date' => $data['estimated_completion_date'] ?? $job->estimated_completion_date,
            'internal_notes'            => $data['internal_notes'] ?? $job->internal_notes,
        ]);

        return $job->fresh(['mechanic', 'booking']);
    }

    /**
     * Unassign the mechanic from a job (revert to pending).
     */
    public function unassign(JobOrder $job): JobOrder
    {
        if ($job->status !== JobOrder::STATUS_ASSIGNED) {
            throw new InvalidArgumentException('Only assigned jobs can be unassigned.');
        }

        $job->update([
            'mechanic_id' => null,
            'assigned_by' => null,
            'assigned_at' => null,
            'status'      => JobOrder::STATUS_PENDING,
        ]);

        return $job->fresh();
    }
}
