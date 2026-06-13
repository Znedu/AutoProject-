<?php

namespace Database\Seeders;

use App\Models\ServiceStage;
use Illuminate\Database\Seeder;

class JobStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeding service_stages as they drive the mechanic job status stages
        $stages = [
            [
                'name' => 'Received',
                'slug' => 'received',
                'sort_order' => 1,
            ],
            [
                'name' => 'Inspection',
                'slug' => 'inspection',
                'sort_order' => 2,
            ],
            [
                'name' => 'In Progress',
                'slug' => 'in-progress',
                'sort_order' => 3,
            ],
            [
                'name' => 'Quality Check',
                'slug' => 'quality-check',
                'sort_order' => 4,
            ],
            [
                'name' => 'Ready for Pickup',
                'slug' => 'ready-for-pickup',
                'sort_order' => 5,
            ],
        ];

        foreach ($stages as $stage) {
            ServiceStage::query()->updateOrCreate(
                ['slug' => $stage['slug']],
                [
                    'name' => $stage['name'],
                    'sort_order' => $stage['sort_order'],
                    'is_active' => true,
                ],
            );
        }

        $this->command->info('Job statuses (pending, assigned, etc.) are managed via model constants, while mechanic workflow stages have been seeded to the service_stages table.');
    }
}
