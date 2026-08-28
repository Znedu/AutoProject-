<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\BusinessSetting;
use App\Models\User;

class BookingBillingPolicy
{
    public function view(User $user, Booking $booking): bool
    {
        if ($user->hasPermission('approvals.manage')
            || $user->hasPermission('bookings.queue.view')
            || $user->hasPermission('jobs.view')) {
            return true;
        }

        if ($user->hasPermission('billing.view')) {
            // Customer can only view their own booking
            if ($booking->user_id === $user->id) {
                return true;
            }

            // Staff or mechanic can view any booking
            if ($user->hasPermission('billing.manage') || $user->hasPermission('jobs.view')) {
                return true;
            }
        }

        return false;
    }

    public function manage(User $user, Booking $booking): bool
    {
        $hasPerm = $user->hasPermission('billing.manage') || $user->hasPermission('approvals.manage');
        if (! $hasPerm) {
            return false;
        }

        $allowedStatuses = [
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_SCHEDULED,
            Booking::STATUS_IN_PROGRESS,
            Booking::STATUS_COMPLETED,
        ];

        if (! in_array($booking->status, $allowedStatuses, true)) {
            return false;
        }

        // Staff check setting
        if (! $user->hasPermission('approvals.manage')) {
            $allowStaff = (bool) BusinessSetting::getValue('allow_staff_billing_edits', true);
            if (! $allowStaff) {
                return false;
            }
        }

        $finalQuotation = $booking->finalQuotation;
        if ($finalQuotation && ! $finalQuotation->isEditable()) {
            return false;
        }

        return true;
    }

    public function finalize(User $user, Booking $booking): bool
    {
        $hasPerm = $user->hasPermission('approvals.adjust-cost') || $user->hasPermission('approvals.manage');
        if (! $hasPerm) {
            return false;
        }

        $allowedStatuses = [
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_SCHEDULED,
            Booking::STATUS_IN_PROGRESS,
            Booking::STATUS_COMPLETED,
        ];

        return in_array($booking->status, $allowedStatuses, true);
    }

    public function recordPayment(User $user, Booking $booking): bool
    {
        return $user->hasPermission('approvals.manage') || $user->hasPermission('billing.manage');
    }
}
