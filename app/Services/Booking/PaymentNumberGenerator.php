<?php

namespace App\Services\Booking;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentNumberGenerator
{
    public function generate(): string
    {
        $year = now()->year;
        $prefix = sprintf('PAY-%d-', $year);

        return DB::transaction(function () use ($prefix): string {
            $latest = Payment::query()
                ->where('payment_number', 'like', $prefix.'%')
                ->lockForUpdate()
                ->orderByDesc('payment_number')
                ->value('payment_number');

            $sequence = 1;

            if ($latest !== null) {
                $sequence = (int) substr($latest, strlen($prefix)) + 1;
            }

            return sprintf('%s%06d', $prefix, $sequence);
        });
    }
}
