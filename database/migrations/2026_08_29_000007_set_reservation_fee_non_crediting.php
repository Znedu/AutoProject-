<?php

use App\Models\BusinessSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        BusinessSetting::query()->updateOrCreate(
            ['key' => 'reservation_fee_credits_toward_total'],
            [
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'booking',
                'description' => 'Whether reservation fee is credited toward the final billing total',
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
