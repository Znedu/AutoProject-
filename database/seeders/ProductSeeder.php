<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'sku' => 'PRD-OIL-5W30',
                'name' => 'Fully Synthetic Engine Oil 5W-30 (4L)',
                'description' => 'Premium high-performance synthetic oil',
                'category' => Product::CATEGORY_MATERIAL,
                'unit_price' => 2800.00,
                'unit_label' => 'can',
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'sku' => 'PRD-OIL-FILT',
                'name' => 'OEM Oil Filter',
                'description' => 'High filtration efficiency oil filter',
                'category' => Product::CATEGORY_MATERIAL,
                'unit_price' => 450.00,
                'unit_label' => 'pc',
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'sku' => 'PRD-BRK-PADS-F',
                'name' => 'Ceramic Front Brake Pads',
                'description' => 'Low dust, high braking torque ceramic pads',
                'category' => Product::CATEGORY_PRODUCT,
                'unit_price' => 3200.00,
                'unit_label' => 'set',
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'sku' => 'PRD-SPK-IRID',
                'name' => 'Iridium Spark Plugs (Set of 4)',
                'description' => 'High ignitability long-life iridium plugs',
                'category' => Product::CATEGORY_PRODUCT,
                'unit_price' => 2200.00,
                'unit_label' => 'set',
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'sku' => 'PRD-AIR-FLTR',
                'name' => 'High Flow Performance Air Filter',
                'description' => 'Reusable drop-in performance air filter',
                'category' => Product::CATEGORY_PRODUCT,
                'unit_price' => 1850.00,
                'unit_label' => 'pc',
                'status' => Product::STATUS_ACTIVE,
            ],
            [
                'sku' => 'PRD-COOLANT',
                'name' => 'Long Life Engine Coolant 50/50 (4L)',
                'description' => 'Pre-mixed ethylene glycol coolant',
                'category' => Product::CATEGORY_MATERIAL,
                'unit_price' => 750.00,
                'unit_label' => 'gal',
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
