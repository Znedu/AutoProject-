<?php

namespace Database\Seeders;

use App\Models\AppointmentSlotConfig;
use App\Models\Service;
use App\Models\ServiceBrand;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
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
                    [
                        'code' => 'ext-001',
                        'name' => 'Full Body Paint / Repaint',
                        'description' => 'Complete vehicle paint job with premium quality automotive paint',
                        'min' => 25000,
                        'max' => 100000,
                        'duration' => '4-6 days',
                        'brands' => [
                            ['name' => 'Anzhal', 'price' => 28000, 'short_description' => 'Standard urethane finish, smooth gloss & budget friendly'],
                            ['name' => 'Nippon Paint', 'price' => 35000, 'short_description' => 'High-durability Japanese automotive coating with UV protection'],
                            ['name' => 'Boysen', 'price' => 30000, 'short_description' => 'Reliable local enamel/polyurethane finish'],
                            ['name' => 'Davies', 'price' => 32000, 'short_description' => 'Vibrant color retention & anti-scratch coating'],
                            ['name' => 'Glasurit', 'price' => 55000, 'short_description' => 'Ultra-premium German clearcoat & OEM color match'],
                            ['name' => 'Sikkens', 'price' => 60000, 'short_description' => 'High-end European refinish system with deep mirror shine'],
                        ],
                    ],
                    [
                        'code' => 'ext-002',
                        'name' => 'Custom Wrap Installation',
                        'description' => 'Professional vinyl wrap application with various design options',
                        'min' => 30000,
                        'max' => 80000,
                        'duration' => '2-3 days',
                        'brands' => [
                            ['name' => '3M (Series 2080)', 'price' => 45000, 'short_description' => 'Dual-cast vinyl with air release & 5-year durability'],
                            ['name' => 'Avery Dennison', 'price' => 42000, 'short_description' => 'Easy-apply Supreme Wrapping film with rich satin/gloss finish'],
                            ['name' => 'TeckWrap', 'price' => 38000, 'short_description' => 'High-grade polymeric vinyl with unique metallic & chrome shades'],
                            ['name' => 'Oracal', 'price' => 35000, 'short_description' => 'Premium German cast wrap film for custom styling'],
                        ],
                    ],
                    [
                        'code' => 'ext-003',
                        'name' => 'Body Kit Installation',
                        'description' => 'Installation of custom body kits including front/rear bumpers and side skirts',
                        'min' => 35000,
                        'max' => 150000,
                        'duration' => '3-5 days',
                        'brands' => [
                            ['name' => 'Modellista', 'price' => 45000, 'short_description' => 'Sleek luxury styling & precision fitment'],
                            ['name' => 'Mugen', 'price' => 50000, 'short_description' => 'Track-tested Honda aero design & lightweight FRP'],
                            ['name' => 'TRD', 'price' => 48000, 'short_description' => 'Toyota Racing Development aggressive aero package'],
                            ['name' => 'Varis', 'price' => 75000, 'short_description' => 'Authentic Japanese carbon fiber high-downforce body kit'],
                            ['name' => 'Liberty Walk', 'price' => 120000, 'short_description' => 'Iconic widebody stance & custom aggressive fender kit'],
                            ['name' => 'Rocket Bunny (Pandes)', 'price' => 110000, 'short_description' => 'Signature bolt-on widebody aero transformation'],
                            ['name' => 'Amuse', 'price' => 85000, 'short_description' => 'Ultra-lightweight titanium/carbon performance body kit'],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'performance',
                'name' => 'Performance Upgrades',
                'icon' => 'gauge',
                'color' => '#457B9D',
                'sort_order' => 2,
                'services' => [
                    [
                        'code' => 'perf-001',
                        'name' => 'Turbocharger Installation',
                        'description' => 'Complete turbo kit installation with tuning and supporting mods',
                        'min' => 60000,
                        'max' => 200000,
                        'duration' => '6-8 days',
                        'brands' => [
                            ['name' => 'HKS', 'price' => 85000, 'short_description' => 'Japanese ball-bearing turbo with rapid spool & high PSI rating'],
                            ['name' => 'Garrett', 'price' => 95000, 'short_description' => 'Garrett GTX series twin-scroll turbocharger with ceramic bearings'],
                            ['name' => 'GReddy', 'price' => 80000, 'short_description' => 'T67/T88 series bolt-on turbo kit with wastegate & manifold'],
                            ['name' => 'BorgWarner', 'price' => 105000, 'short_description' => 'AirWerks EFR series turbocharger with integrated bypass valve'],
                        ],
                    ],
                    [
                        'code' => 'perf-002',
                        'name' => 'Intercooler Installation',
                        'description' => 'Front-mount intercooler installation for improved cooling',
                        'min' => 15000,
                        'max' => 50000,
                        'duration' => '2-3 days',
                        'brands' => [
                            ['name' => 'HKS', 'price' => 25000, 'short_description' => 'Bar & plate core for efficient intake air cooling'],
                            ['name' => 'Garrett', 'price' => 28000, 'short_description' => 'High-density intercooler core for maximum thermal heat dissipation'],
                            ['name' => 'GReddy', 'price' => 24000, 'short_description' => 'Front-mount aluminum intercooler kit'],
                            ['name' => 'Mishimoto', 'price' => 22000, 'short_description' => 'Direct-fit performance intercooler with lifetime warranty'],
                        ],
                    ],
                    [
                        'code' => 'perf-003',
                        'name' => 'Exhaust Fabrication',
                        'description' => 'Custom exhaust system design and fabrication with quality materials',
                        'min' => 15000,
                        'max' => 50000,
                        'duration' => '2-3 days',
                        'brands' => [
                            ['name' => 'HKS', 'price' => 22000, 'short_description' => 'Hi-Power stainless steel mandrel-bent cat-back exhaust'],
                            ['name' => 'GReddy', 'price' => 25000, 'short_description' => 'Supreme SP cat-back exhaust system with titanium tips'],
                            ['name' => 'MagnaFlow', 'price' => 20000, 'short_description' => 'Performance straight-through stainless muffler & tubing'],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'interior',
                'name' => 'Interior Customization',
                'icon' => 'armchair',
                'color' => '#F77F00',
                'sort_order' => 3,
                'services' => [
                    [
                        'code' => 'int-001',
                        'name' => 'Custom Seat Upholstery',
                        'description' => 'Premium leather or fabric seat re-upholstery',
                        'min' => 15000,
                        'max' => 60000,
                        'duration' => '3-4 days',
                        'brands' => [
                            ['name' => 'Seatmate', 'price' => 25000, 'short_description' => 'Premium imported automotive leather seat cover design'],
                            ['name' => 'MG Square (Local)', 'price' => 18000, 'short_description' => 'Custom tailored local leatherette & fabric upholstery'],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'engine',
                'name' => 'Engine Maintenance',
                'icon' => 'settings',
                'color' => '#1F2937',
                'sort_order' => 4,
                'services' => [
                    [
                        'code' => 'eng-001',
                        'name' => 'Engine Oil Change',
                        'description' => 'Complete engine oil and filter replacement',
                        'min' => 800,
                        'max' => 3000,
                        'duration' => '1 day',
                        'brands' => [
                            ['name' => 'Motul', 'price' => 2200, 'short_description' => '100% Synthetic 300V / 8100 ester-based performance oil'],
                            ['name' => 'Shell Helix', 'price' => 1800, 'short_description' => 'Fully synthetic PurePlus gas-to-liquid engine oil'],
                            ['name' => 'Petron Blaze', 'price' => 1400, 'short_description' => 'Local fully synthetic high-mileage formulation'],
                            ['name' => 'Castrol', 'price' => 1750, 'short_description' => 'Castrol EDGE fluid TITANIUM technology'],
                            ['name' => 'Mobil 1', 'price' => 2400, 'short_description' => 'Advanced full synthetic engine protection'],
                            ['name' => 'Pertua', 'price' => 1300, 'short_description' => 'DuraSyn technology anti-friction metal treatment oil'],
                        ],
                    ],
                    [
                        'code' => 'eng-002',
                        'name' => 'Engine Customization',
                        'description' => 'Performance engine modifications including turbo, ECU tuning, and internal upgrades',
                        'min' => 50000,
                        'max' => 150000,
                        'duration' => '5-7 days',
                        'brands' => [
                            ['name' => 'HKS', 'price' => 95000, 'short_description' => 'Japanese performance internal engine components & cam gears'],
                            ['name' => 'Garrett', 'price' => 110000, 'short_description' => 'High-flow turbo & manifold engine performance upgrade package'],
                            ['name' => 'GReddy', 'price' => 90000, 'short_description' => 'Heavy-duty forged engine internals & tuning package'],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'cooling',
                'name' => 'Cooling System Maintenance',
                'icon' => 'wind',
                'color' => '#06AED5',
                'sort_order' => 5,
                'services' => [
                    [
                        'code' => 'cool-001',
                        'name' => 'Radiator Flush',
                        'description' => 'Complete radiator flush and cleaning',
                        'min' => 1500,
                        'max' => 4000,
                        'duration' => '1 day',
                        'brands' => [
                            ['name' => 'Prestone', 'price' => 2000, 'short_description' => 'Extended life OAT corrosion protection coolant'],
                            ['name' => 'Peak', 'price' => 1800, 'short_description' => 'Global lifetime antifreeze & coolant'],
                            ['name' => "Wynn's", 'price' => 2200, 'short_description' => 'Professional cooling system flush & conditioner'],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'brake',
                'name' => 'Brake System Maintenance',
                'icon' => 'disc',
                'color' => '#D62828',
                'sort_order' => 6,
                'services' => [
                    [
                        'code' => 'brake-001',
                        'name' => 'Brake Pad Replacement',
                        'description' => 'Front or rear brake pad replacement',
                        'min' => 3000,
                        'max' => 12000,
                        'duration' => '1 day',
                        'brands' => [
                            ['name' => 'Akebono', 'price' => 4500, 'short_description' => 'Ultra-premium ceramic low-dust quiet braking pads'],
                            ['name' => 'Bendix', 'price' => 3800, 'short_description' => 'General CT ceramic brake pads with Stealth Technology'],
                            ['name' => 'Ferodo', 'price' => 5200, 'short_description' => 'European premier high-friction braking material'],
                            ['name' => 'Brembo (OE replacement)', 'price' => 6500, 'short_description' => 'OEM high-carbon steel & ceramic compound brake pads'],
                        ],
                    ],
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

            foreach ($categoryData['services'] as $index => $serviceData) {
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

                foreach ($serviceData['brands'] as $brandIndex => $brandItem) {
                    $brandName = is_array($brandItem) ? $brandItem['name'] : $brandItem;
                    $brandPrice = is_array($brandItem) ? ($brandItem['price'] ?? 0) : 0;
                    $brandDesc = is_array($brandItem) ? ($brandItem['short_description'] ?? null) : null;

                    ServiceBrand::query()->updateOrCreate(
                        [
                            'service_id' => $service->id,
                            'name' => $brandName,
                        ],
                        [
                            'price' => $brandPrice,
                            'short_description' => $brandDesc,
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
