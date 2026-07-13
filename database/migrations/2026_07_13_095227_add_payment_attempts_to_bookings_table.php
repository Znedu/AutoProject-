<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds the `payment_attempts` counter to `bookings`.
     * New status values introduced alongside this migration (stored as VARCHAR):
     *   - pending_payment_verification
     *   - payment_requires_resubmission
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Tracks how many times an admin has rejected a payment for this booking.
            // When payment_attempts reaches PaymentVerificationService::MAX_ATTEMPTS (3),
            // the booking is automatically cancelled.
            $table->unsignedSmallInteger('payment_attempts')
                  ->default(0)
                  ->after('cancellation_reason')
                  ->comment('Number of times payment was rejected by admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('payment_attempts');
        });
    }
};
