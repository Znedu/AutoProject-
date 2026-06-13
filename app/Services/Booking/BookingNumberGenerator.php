<?php

namespace App\Services\Booking;

use App\Models\Booking;
use Illuminate\Support\Facades\DB;

class BookingNumberGenerator
{
    public function generate(): string
    {
        $year = now()->year;
        $prefix = sprintf('BK-%d-', $year);

        return DB::transaction(function () use ($prefix, $year): string {
            $latest = Booking::withTrashed()
                ->where('booking_number', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByDesc('booking_number')
                ->value('booking_number');

            $sequence = 1;

            if ($latest !== null) {
                $sequence = (int) substr($latest, strlen($prefix)) + 1;
            }

            return sprintf('%s%06d', $prefix, $sequence);
        });
    }
}
