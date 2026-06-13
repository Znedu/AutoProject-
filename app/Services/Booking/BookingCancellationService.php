<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingCancellationService
{
    public function __construct(
        protected BookingStatusLogger $statusLogger,
    ) {}

    public function cancel(Booking $booking, User $customer, ?string $reason = null): Booking
    {
        if ($booking->user_id !== $customer->id) {
            throw new InvalidArgumentException('You may only cancel your own bookings.');
        }

        if ($booking->status !== Booking::STATUS_PENDING) {
            throw new InvalidArgumentException('Only pending bookings can be cancelled.');
        }

        return DB::transaction(function () use ($booking, $customer, $reason): Booking {
            $previousStatus = $booking->status;

            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancellation_reason' => $reason,
                'canceled_at' => now(),
            ]);

            $this->statusLogger->log(
                $booking,
                $previousStatus,
                Booking::STATUS_CANCELLED,
                $customer,
                $reason ?? 'Cancelled by customer.',
            );

            return $booking->fresh();
        });
    }
}
