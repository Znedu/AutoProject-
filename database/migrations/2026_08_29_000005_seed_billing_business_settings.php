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
        $settings = [
            [
                'key' => 'reservation_fee_credits_toward_total',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'booking',
                'description' => 'Whether reservation fee is credited toward the final billing total',
            ],
            [
                'key' => 'allow_staff_billing_edits',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'booking',
                'description' => 'Whether staff members can add or edit line items on draft final billing',
            ],
        ];

        foreach ($settings as $setting) {
            BusinessSetting::query()->updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        BusinessSetting::query()->whereIn('key', [
            'reservation_fee_credits_toward_total',
            'allow_staff_billing_edits',
        ])->delete();
    }
};
