<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use Illuminate\Database\Seeder;

class BusinessSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'reservation_fee',
                'value' => '200.00',
                'type' => 'decimal',
                'group' => 'booking',
                'description' => 'Required fee to secure a slot reservation',
            ],
            [
                'key' => 'gcash_account_name',
                'value' => 'AutoProject-D Custom Garage',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'GCash account display name',
            ],
            [
                'key' => 'gcash_account_number',
                'value' => '0912-345-6789',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'GCash mobile number',
            ],
            [
                'key' => 'maya_account_name',
                'value' => 'AutoProject-D Custom Garage',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Maya account display name',
            ],
            [
                'key' => 'maya_account_number',
                'value' => '0917-888-9999',
                'type' => 'string',
                'group' => 'payment',
                'description' => 'Maya mobile number',
            ],
            [
                'key' => 'business_name',
                'value' => 'AutoProject-D Custom Garage',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Business Name',
            ],
            [
                'key' => 'business_phone',
                'value' => '+63 912 345 6789',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Business Contact Phone',
            ],
            [
                'key' => 'reservation_fee_credits_toward_total',
                'value' => 'true',
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
            BusinessSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
