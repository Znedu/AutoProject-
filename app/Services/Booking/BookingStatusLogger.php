<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\User;

class BookingStatusLogger
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function log(
        Booking $booking,
        ?string $fromStatus,
        string $toStatus,
        ?User $actor = null,
        ?string $reason = null,
        ?array $metadata = null,
    ): BookingStatusLog {
        return BookingStatusLog::create([
            'booking_id' => $booking->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => $actor?->id,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }
}
