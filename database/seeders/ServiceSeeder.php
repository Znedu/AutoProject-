<?php

namespace Database\Seeders;

use App\Models\AppointmentSlotConfig;
use App\Models\Service;
use App\Models\ServiceBrand;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Seed service categories, services, and default appointment slots.
     */
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'exterior',
                'name' => 'Exterior Customization',
                'icon' => 'paintbrush',
                'color' => '#E63946',
                'sort_order' => 1,
                'services' => [
                    ['code' => 'ext-001', 'name' => 'Full Body Paint / Repaint', 'description' => 'Complete vehicle paint job with premium quality automotive paint', 'min' => 25000, 'max' => 100000, 'duration' => '4-6 days', 'brands' => ['Anzhal', 'Nippon Paint', 'Boysen', 'Davies', 'Glasurit', 'Sikkens']],
                    ['code' => 'ext-002', 'name' => 'Custom Wrap Installation', 'description' => 'Professional vinyl wrap application with various design options', 'min' => 30000, 'max' => 80000, 'duration' => '2-3 days', 'brands' => ['3M (Series 2080)', 'Avery Dennison', 'TeckWrap', 'Oracal']],
                    ['code' => 'ext-003', 'name' => 'Body Kit Installation', 'description' => 'Installation of custom body kits including front/rear bumpers and side skirts', 'min' => 35000, 'max' => 150000, 'duration' => '3-5 days', 'brands' => ['Modellista', 'Mugen', 'TRD', 'Varis', 'Liberty Walk', 'Rocket Bunny (Pandes)', 'Amuse']],
                ],
            ],
            [
                'slug' => 'performance',
                'name' => 'Performance Upgrades',
                'icon' => 'gauge',
                'color' => '#457B9D',
                'sort_order' => 2,
                'services' => [
                    ['code' => 'perf-001', 'name' => 'Turbocharger Installation', 'description' => 'Complete turbo kit installation with tuning and supporting mods', 'min' => 60000, 'max' => 200000, 'duration' => '6-8 days', 'brands' => ['HKS', 'Garrett', 'GReddy', 'BorgWarner']],
                    ['code' => 'perf-002', 'name' => 'Intercooler Installation', 'description' => 'Front-mount intercooler installation for improved cooling', 'min' => 15000, 'max' => 50000, 'duration' => '2-3 days', 'brands' => ['HKS', 'Garrett', 'GReddy', 'Mishimoto']],
                    ['code' => 'perf-003', 'name' => 'Exhaust Fabrication', 'description' => 'Custom exhaust system design and fabrication with quality materials', 'min' => 15000, 'max' => 50000, 'duration' => '2-3 days', 'brands' => ['HKS', 'GReddy', 'MagnaFlow']],
                ],
            ],
            [
                'slug' => 'interior',
                'name' => 'Interior Customization',
                'icon' => 'armchair',
                'color' => '#F77F00',
                'sort_order' => 3,
                'services' => [
                    ['code' => 'int-001', 'name' => 'Custom Seat Upholstery', 'description' => 'Premium leather or fabric seat re-upholstery', 'min' => 15000, 'max' => 60000, 'duration' => '3-4 days', 'brands' => ['Seatmate', 'MG Square (Local)']],
                ],
            ],
            [
                'slug' => 'engine',
                'name' => 'Engine Maintenance',
                'icon' => 'settings',
                'color' => '#1F2937',
                'sort_order' => 4,
                'services' => [
                    ['code' => 'eng-001', 'name' => 'Engine Oil Change', 'description' => 'Complete engine oil and filter replacement', 'min' => 800, 'max' => 3000, 'duration' => '1 day', 'brands' => ['Motul', 'Shell Helix', 'Petron Blaze', 'Castrol', 'Mobil 1', 'Pertua']],
                    ['code' => 'eng-002', 'name' => 'Engine Customization', 'description' => 'Performance engine modifications including turbo, ECU tuning, and internal upgrades', 'min' => 50000, 'max' => 150000, 'duration' => '5-7 days', 'brands' => ['HKS', 'Garrett', 'GReddy']],
                ],
            ],
            [
                'slug' => 'cooling',
                'name' => 'Cooling System Maintenance',
                'icon' => 'wind',
                'color' => '#06AED5',
                'sort_order' => 5,
                'services' => [
                    ['code' => 'cool-001', 'name' => 'Radiator Flush', 'description' => 'Complete radiator flush and cleaning', 'min' => 1500, 'max' => 4000, 'duration' => '1 day', 'brands' => ['Prestone', 'Peak', "Wynn's"]],
                ],
            ],
            [
                'slug' => 'brake',
                'name' => 'Brake System Maintenance',
                'icon' => 'disc',
                'color' => '#D62828',
                'sort_order' => 6,
                'services' => [
                    ['code' => 'brake-001', 'name' => 'Brake Pad Replacement', 'description' => 'Front or rear brake pad replacement', 'min' => 3000, 'max' => 12000, 'duration' => '1 day', 'brands' => ['Akebono', 'Bendix', 'Ferodo', 'Brembo (OE replacement)']],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $category = ServiceCategory::query()->updateOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'name' => $categoryData['name'],
                    'icon' => $categoryData['icon'],
                    'color' => $categoryData['color'],
                    'sort_order' => $categoryData['sort_order'],
                    'is_active' => true,
                ],
            );

            foreach ($categoryData['services'] as $serviceData) {
                $service = Service::query()->updateOrCreate(
                    ['code' => $serviceData['code']],
                    [
                        'service_category_id' => $category->id,
                        'name' => $serviceData['name'],
                        'description' => $serviceData['description'],
                        'min_cost' => $serviceData['min'],
                        'max_cost' => $serviceData['max'],
                        'duration_label' => $serviceData['duration'],
                        'status' => Service::STATUS_ACTIVE,
                    ],
                );

                foreach ($serviceData['brands'] as $brandIndex => $brandName) {
                    ServiceBrand::query()->updateOrCreate(
                        [
                            'service_id' => $service->id,
                            'name' => $brandName,
                        ],
                        [
                            'sort_order' => $brandIndex + 1,
                            'is_active' => true,
                        ],
                    );
                }
            }
        }

        foreach (range(1, 6) as $dayOfWeek) {
            AppointmentSlotConfig::query()->updateOrCreate(
                [
                    'day_of_week' => $dayOfWeek,
                    'starts_at' => '08:00:00',
                    'ends_at' => '18:00:00',
                ],
                [
                    'slot_duration_minutes' => 60,
                    'max_capacity' => 2,
                    'is_active' => true,
                ],
            );
        }
    }
}
