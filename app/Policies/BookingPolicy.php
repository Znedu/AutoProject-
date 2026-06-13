<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('bookings.view')
            || $user->hasPermission('approvals.manage')
            || $user->hasPermission('bookings.queue.view');
    }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->hasPermission('approvals.manage') || $user->hasPermission('bookings.queue.view')) {
            return true;
        }

        return $user->hasPermission('bookings.view') && $booking->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('bookings.create');
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->hasPermission('bookings.cancel')
            && $booking->user_id === $user->id
            && $booking->status === Booking::STATUS_PENDING;
    }

    public function approve(User $user, Booking $booking): bool
    {
        return $user->hasPermission('approvals.manage')
            && $booking->status === Booking::STATUS_PENDING;
    }

    public function reject(User $user, Booking $booking): bool
    {
        return $user->hasPermission('approvals.manage')
            && $booking->status === Booking::STATUS_PENDING;
    }

    public function verifyPayment(User $user, Booking $booking): bool
    {
        return $user->hasPermission('approvals.manage')
            || $user->hasPermission('bookings.verify-payment');
    }
}
