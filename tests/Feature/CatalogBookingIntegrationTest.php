<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogBookingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_selecting_catalog_item_redirects_and_preselects_service(): void
    {
        $user = User::factory()->create();

        $category = ServiceCategory::create([
            'name' => 'Brake System Maintenance',
            'slug' => 'brake',
            'icon' => 'disc',
            'color' => '#D62828',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'code' => 'brake-001',
            'name' => 'Brake Pad Replacement',
            'description' => 'Front or rear brake pad replacement',
            'min_cost' => 3000,
            'max_cost' => 12000,
            'status' => 'active',
        ]);

        $product = Product::create([
            'sku' => 'PRD-BRK-TEST',
            'name' => 'Brembo Ceramic Brake Pads',
            'description' => 'Low dust ceramic brake pads',
            'category' => 'Brakes & Suspension',
            'unit_price' => 3850.00,
            'cost_price' => 2600.00,
            'stock_quantity' => 10,
            'min_stock_threshold' => 2,
            'unit_label' => 'set',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(route('customer.book-service', ['product_id' => $product->id]));

        $response->assertStatus(200);
        $response->assertViewHas('preselectedServiceId', $service->id);
        $response->assertViewHas('selectedProduct', fn ($p) => $p->id === $product->id);
    }
}
