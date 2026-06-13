<?php

use Illuminate\Database\Migrations\Migration;

/**
 * Removed from approved schema.
 *
 * The report_exports table was deemed over-engineered for the capstone.
 * Admin Reports uses inline Chart.js charts; exports are handled as
 * synchronous controller streams (CSV/PDF) with no database table needed.
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
