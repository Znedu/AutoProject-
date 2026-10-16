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
                    ['code' => 'paint-001', 'name' => 'Paint touch up', 'description' => 'Targeted paint touch-up on chips, scratches, and minor panel blemishes (max 2 panels)', 'min' => 3000, 'max' => 4000, 'duration' => '1-2 days', 'brands' => []],
                    ['code' => 'paint-002', 'name' => 'Custom paint job', 'description' => 'Partial or multi-panel custom paint job with premium finish and UV clearcoat', 'min' => 15000, 'max' => 20000, 'duration' => '3-5 days', 'brands' => []],
                    ['code' => 'body-001', 'name' => 'Body repair', 'description' => 'Dent repair, panel realignment, and metal/fiberglass restoration', 'min' => 5000, 'max' => 7000, 'duration' => '2-4 days', 'brands' => []],
                    ['code' => 'pkg-001', 'name' => 'Full custom build package', 'description' => 'Comprehensive whole-car custom transformation, styling, and paint finish', 'min' => 35000, 'max' => 45000, 'duration' => '1-2 weeks', 'brands' => []],
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
                    ['code' => 'perf-exhaust', 'name' => 'Exhaust piping', 'description' => 'Custom exhaust piping design and mandrel-bent fabrication', 'min' => 7000, 'max' => 10000, 'duration' => '1-2 days', 'brands' => []],
                    ['code' => 'perf-intake', 'name' => 'Intake piping', 'description' => 'Performance cold-air / short-ram intake piping installation', 'min' => 6000, 'max' => 8000, 'duration' => '1 day', 'brands' => []],
                    ['code' => 'perf-intercooler', 'name' => 'Intercooler installation', 'description' => 'Front-mount intercooler and charge pipe installation', 'min' => 12000, 'max' => 15000, 'duration' => '2-3 days', 'brands' => []],
                    ['code' => 'perf-001', 'name' => 'Turbocharger Installation', 'description' => 'Complete turbo kit installation with tuning and supporting mods', 'min' => 60000, 'max' => 200000, 'duration' => '6-8 days', 'brands' => ['HKS', 'Garrett', 'GReddy', 'BorgWarner']],
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
                    ['code' => 'maint-general', 'name' => 'General maintenance', 'description' => 'Comprehensive multi-point vehicle inspection, fluid check, and scheduled maintenance', 'min' => 2000, 'max' => 3000, 'duration' => '1 day', 'brands' => []],
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

        // Deactivate old legacy full body repaint so it does not appear in customer catalog
        Service::query()->where('code', 'ext-001')->update(['status' => Service::STATUS_INACTIVE]);

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
