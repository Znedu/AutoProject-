<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Engine Oils
            [
                'sku' => 'PRD-OIL-5W30',
                'name' => 'Motul 300V Synthetic Engine Oil 5W-30 (4L)',
                'description' => 'Ester Core technology ultra high performance engine oil for street & track',
                'category' => Product::CATEGORY_ENGINE_OIL,
                'cost_price' => 2100.00,
                'unit_price' => 2850.00,
                'stock_quantity' => 18,
                'min_stock_threshold' => 5,
                'location' => 'Rack A1 - Shelf 2',
                'unit_label' => 'can',
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'sku' => 'PRD-OIL-FILT',
                'name' => 'K&N Performance Oil Filter HP-1008',
                'description' => 'Heavy duty filter housing with synthetic media for high oil flow rates',
                'category' => Product::CATEGORY_ENGINE_OIL,
                'cost_price' => 320.00,
                'unit_price' => 550.00,
                'stock_quantity' => 3, // LOW STOCK
                'min_stock_threshold' => 8,
                'location' => 'Rack A1 - Bin 04',
                'unit_label' => 'pc',
                'status' => Product::STATUS_ACTIVE,
            ],

            // Brakes & Suspension
            [
                'sku' => 'PRD-BRK-PADS-F',
                'name' => 'Brembo Sport Ceramic Front Brake Pads',
                'description' => 'Low dust, fade-resistant ceramic brake compound for sports cars',
                'category' => Product::CATEGORY_BRAKES,
                'cost_price' => 2600.00,
                'unit_price' => 3850.00,
                'stock_quantity' => 12,
                'min_stock_threshold' => 4,
                'location' => 'Rack B2 - Shelf 1',
                'unit_label' => 'set',
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'sku' => 'PRD-SUSP-COIL',
                'name' => 'Tein Flex Z Adjustable Coilovers',
                'description' => '16-level damping force adjustable coilover kit with twin-tube design',
                'category' => Product::CATEGORY_BRAKES,
                'cost_price' => 32000.00,
                'unit_price' => 45000.00,
                'stock_quantity' => 0, // OUT OF STOCK
                'min_stock_threshold' => 2,
                'location' => 'Bay 3 Heavy Goods Storage',
                'unit_label' => 'set',
                'status' => Product::STATUS_ACTIVE,
            ],

            // Tires & Wheels
            [
                'sku' => 'PRD-TIRE-2454018',
                'name' => 'Michelin Pilot Sport 5 (245/40 R18)',
                'description' => 'Max performance summer tire with Dynamic Response Technology',
                'category' => Product::CATEGORY_TIRES,
                'cost_price' => 7800.00,
                'unit_price' => 10500.00,
                'stock_quantity' => 2, // LOW STOCK
                'min_stock_threshold' => 6,
                'location' => 'Tire Rack T-01',
                'unit_label' => 'pc',
                'status' => Product::STATUS_ACTIVE,
            ],

            // Custom Accessories
            [
                'sku' => 'PRD-WING-CF',
                'name' => 'GT-Style Carbon Fiber Rear Wing Spoiler',
                'description' => 'Real 3K twill weave dry carbon wing with CNC machined aluminum legs',
                'category' => Product::CATEGORY_ACCESSORIES,
                'cost_price' => 14000.00,
                'unit_price' => 22000.00,
                'stock_quantity' => 4,
                'min_stock_threshold' => 2,
                'location' => 'Bodywork Vault V-2',
                'unit_label' => 'pc',
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'sku' => 'PRD-EXH-CATBACK',
                'name' => 'Titanium Valved Cat-Back Exhaust System',
                'description' => 'Dual electronic exhaust valves with remote control for adjustable tone',
                'category' => Product::CATEGORY_ACCESSORIES,
                'cost_price' => 38000.00,
                'unit_price' => 58000.00,
                'stock_quantity' => 0, // OUT OF STOCK
                'min_stock_threshold' => 1,
                'location' => 'Rack C4',
                'unit_label' => 'set',
                'status' => Product::STATUS_ACTIVE,
            ],

            // Paint & Bodywork
            [
                'sku' => 'PRD-PNT-RED',
                'name' => 'Soul Red Metallic Custom Automotive Basecoat (1L)',
                'description' => 'High opacity premium 2-stage metallic basecoat paint',
                'category' => Product::CATEGORY_PAINT,
                'cost_price' => 1800.00,
                'unit_price' => 2950.00,
                'stock_quantity' => 15,
                'min_stock_threshold' => 5,
                'location' => 'Mixing Room Cabinet 3',
                'unit_label' => 'can',
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'sku' => 'PRD-PNT-CLR',
                'name' => 'High Gloss Urethane Clear Coat Kit (5L)',
                'description' => '2:1 UV resistant anti-scratch high gloss clear coat with medium activator',
                'category' => Product::CATEGORY_PAINT,
                'cost_price' => 2500.00,
                'unit_price' => 3800.00,
                'stock_quantity' => 1, // LOW STOCK
                'min_stock_threshold' => 3,
                'location' => 'Mixing Room Cabinet 1',
                'unit_label' => 'set',
                'status' => Product::STATUS_ACTIVE,
            ],

            // Electrical & Lighting
            [
                'sku' => 'PRD-SPK-IRID',
                'name' => 'NGK Laser Iridium Spark Plugs (Set of 4)',
                'description' => 'Iridium tip with platinum disc on ground electrode for maximum spark',
                'category' => Product::CATEGORY_ELECTRICAL,
                'cost_price' => 1400.00,
                'unit_price' => 2400.00,
                'stock_quantity' => 25,
                'min_stock_threshold' => 10,
                'location' => 'Rack A2 - Bin 12',
                'unit_label' => 'set',
                'status' => Product::STATUS_ACTIVE,
            ],
        ];

        foreach ($products as $item) {
            Product::query()->updateOrCreate(
                ['sku' => $item['sku']],
                $item
            );
        }
    }
}
