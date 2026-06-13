<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Removed from approved schema.
 *
 * The daily_metrics_snapshots table was deemed over-engineered for the
 * capstone. Admin dashboard queries bookings, payments, and users directly
 * with indexed date columns. Pre-aggregation is a scaling concern that
 * does not apply at this stage.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Intentionally empty — table removed from approved schema.
    }

    public function down(): void
    {
        // Nothing to reverse.
    }
};
