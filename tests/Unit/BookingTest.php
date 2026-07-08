<?php

namespace Tests\Unit;

use App\Models\Booking;
use Tests\TestCase;

class BookingTest extends TestCase
{
    public function test_booking_badge_label_matches_status(): void
    {
        $pendingBooking = new Booking(['status' => Booking::STATUS_PENDING]);
        $approvedBooking = new Booking(['status' => Booking::STATUS_APPROVED]);

        $this->assertSame('Awaiting Approval', $pendingBooking->badge_label);
        $this->assertSame('Approved', $approvedBooking->badge_label);
    }
}
