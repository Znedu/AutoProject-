<?php

namespace Database\Seeders;

use App\Models\PaintPanel;
use Illuminate\Database\Seeder;

class PaintPanelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $panels = [
            // Front
            [
                'code' => 'hood',
                'name' => 'Hood',
                'group' => 'front',
                'sort_order' => 1,
            ],
            [
                'code' => 'front_bumper',
                'name' => 'Front Bumper',
                'group' => 'front',
                'sort_order' => 2,
            ],
            [
                'code' => 'lf_fender',
                'name' => 'Left Front Fender',
                'group' => 'front',
                'sort_order' => 3,
            ],
            [
                'code' => 'rf_fender',
                'name' => 'Right Front Fender',
                'group' => 'front',
                'sort_order' => 4,
            ],

            // Doors
            [
                'code' => 'lf_door',
                'name' => 'Left Front Door',
                'group' => 'doors',
                'sort_order' => 5,
            ],
            [
                'code' => 'rf_door',
                'name' => 'Right Front Door',
                'group' => 'doors',
                'sort_order' => 6,
            ],
            [
                'code' => 'lr_door',
                'name' => 'Left Rear Door',
                'group' => 'doors',
                'sort_order' => 7,
            ],
            [
                'code' => 'rr_door',
                'name' => 'Right Rear Door',
                'group' => 'doors',
                'sort_order' => 8,
            ],

            // Rear / Side
            [
                'code' => 'l_quarter',
                'name' => 'Left Quarter Panel',
                'group' => 'rear_side',
                'sort_order' => 9,
            ],
            [
                'code' => 'r_quarter',
                'name' => 'Right Quarter Panel',
                'group' => 'rear_side',
                'sort_order' => 10,
            ],
            [
                'code' => 'trunk_lid',
                'name' => 'Trunk Lid / Tailgate',
                'group' => 'rear_side',
                'sort_order' => 11,
            ],
            [
                'code' => 'rear_bumper',
                'name' => 'Rear Bumper',
                'group' => 'rear_side',
                'sort_order' => 12,
            ],

            // Other
            [
                'code' => 'roof',
                'name' => 'Roof',
                'group' => 'other',
                'sort_order' => 13,
            ],
            [
                'code' => 'rocker_panels_l',
                'name' => 'Left Rocker Panel / Side Skirt',
                'group' => 'other',
                'sort_order' => 14,
            ],
            [
                'code' => 'rocker_panels_r',
                'name' => 'Right Rocker Panel / Side Skirt',
                'group' => 'other',
                'sort_order' => 15,
            ],
            [
                'code' => 'side_mirrors',
                'name' => 'Side Mirrors',
                'group' => 'other',
                'sort_order' => 16,
            ],
            [
                'code' => 'spoiler',
                'name' => 'Spoiler / Wing',
                'group' => 'other',
                'sort_order' => 17,
            ],
            [
                'code' => 'custom_other',
                'name' => 'Custom / Other Panel',
                'group' => 'other',
                'sort_order' => 18,
            ],
        ];

        foreach ($panels as $data) {
            PaintPanel::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'group' => $data['group'],
                    'sort_order' => $data['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
