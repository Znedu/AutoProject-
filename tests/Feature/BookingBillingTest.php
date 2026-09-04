<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Booking;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingBillingTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;

    protected User $otherCustomer;

    protected User $admin;

    protected User $staff;

    protected Role $customerRole;

    protected Role $adminRole;

    protected Role $staffRole;

    protected Vehicle $vehicle;

    protected Booking $booking;

    protected Service $service;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Customer->value],
            ['name' => 'Customer', 'description' => 'Customer Role']
        );

        $this->adminRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Administrator->value],
            ['name' => 'Administrator', 'description' => 'Admin Role']
        );

        $this->staffRole = Role::firstOrCreate(
            ['slug' => RoleSlug::Staff->value],
            ['name' => 'Staff', 'description' => 'Staff Role']
        );

        // Permissions
        $approvalsPerm = Permission::firstOrCreate(['slug' => 'approvals.manage'], ['name' => 'Approvals Manage']);
        $adjustCostPerm = Permission::firstOrCreate(['slug' => 'approvals.adjust-cost'], ['name' => 'Adjust Cost']);
        $billingViewPerm = Permission::firstOrCreate(['slug' => 'billing.view'], ['name' => 'Billing View']);
        $billingManagePerm = Permission::firstOrCreate(['slug' => 'billing.manage'], ['name' => 'Billing Manage']);

        $this->adminRole->permissions()->syncWithoutDetaching([
            $approvalsPerm->id,
            $adjustCostPerm->id,
            $billingViewPerm->id,
            $billingManagePerm->id,
        ]);

        $this->staffRole->permissions()->syncWithoutDetaching([
            $billingViewPerm->id,
            $billingManagePerm->id,
        ]);

        $this->customerRole->permissions()->syncWithoutDetaching([
            $billingViewPerm->id,
        ]);

        $this->customer = User::factory()->create([
            'role_id' => $this->customerRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->otherCustomer = User::factory()->create([
            'role_id' => $this->customerRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->staff = User::factory()->create([
            'role_id' => $this->staffRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->vehicle = Vehicle::create([
            'user_id' => $this->customer->id,
            'make' => 'Toyota',
            'model' => 'Vios',
            'year' => 2020,
            'plate_number' => 'ABC9999',
        ]);

        $category = ServiceCategory::create([
            'name' => 'Brakes & Suspension',
            'slug' => 'brakes-suspension',
            'is_active' => true,
        ]);

        $this->service = Service::create([
            'service_category_id' => $category->id,
            'code' => 'SVC-BRK-001',
            'name' => 'Brake Service',
            'description' => 'Complete brake overhaul',
            'min_cost' => 2000.00,
            'max_cost' => 3500.00,
            'status' => 'active',
        ]);

        $this->product = Product::create([
            'sku' => 'PRD-BRK-001',
            'name' => 'Brake Fluid DOT4',
            'category' => Product::CATEGORY_MATERIAL,
            'unit_price' => 350.00,
            'status' => Product::STATUS_ACTIVE,
        ]);

        $this->booking = Booking::create([
            'booking_number' => 'BK-BILL-TEST',
            'user_id' => $this->customer->id,
            'vehicle_id' => $this->vehicle->id,
            'status' => Booking::STATUS_CONFIRMED,
            'preferred_date' => today()->addDays(3),
            'preferred_time' => '14:00:00',
            'customer_name' => 'John Doe',
            'contact_number' => '09123456780',
            'terms_accepted_at' => now(),
        ]);

        $this->booking->bookingServices()->create([
            'service_id' => $this->service->id,
            'unit_min_snapshot' => 2000.00,
            'unit_max_snapshot' => 3500.00,
        ]);
    }

    public function test_admin_can_view_billing_and_draft_is_auto_created(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.bookings.billing.show', $this->booking));

        $response->assertOk();
        $response->assertViewIs('admin.bookings.billing');
        $response->assertSee('BK-BILL-TEST');

        // Quotation draft should be created
        $draft = $this->booking->quotations()->where('type', Quotation::TYPE_FINAL)->first();
        $this->assertNotNull($draft);
        $this->assertEquals(Quotation::STATUS_DRAFT, $draft->status);
    }

    public function test_admin_can_add_product_labor_and_discount_lines(): void
    {
        // View to ensure draft exists
        $this->actingAs($this->admin)
            ->get(route('admin.bookings.billing.show', $this->booking));

        // Add Product Line
        $resProduct = $this->actingAs($this->admin)
            ->post(route('admin.bookings.billing.lines.store', $this->booking), [
                'item_type' => 'product',
                'product_id' => $this->product->id,
                'description' => 'Brake Fluid DOT4',
                'quantity' => 2,
                'unit_final' => 350.00,
            ]);

        $resProduct->assertRedirect();
        $resProduct->assertSessionHas('success');

        // Add Labor Line
        $resLabor = $this->actingAs($this->admin)
            ->post(route('admin.bookings.billing.lines.store', $this->booking), [
                'item_type' => 'labor',
                'description' => 'Brake Bleeding Labor',
                'quantity' => 1,
                'unit_final' => 500.00,
            ]);

        $resLabor->assertRedirect();

        // Add Discount Line
        $resDiscount = $this->actingAs($this->admin)
            ->post(route('admin.bookings.billing.lines.store', $this->booking), [
                'item_type' => 'discount',
                'description' => 'Senior Citizen Discount',
                'quantity' => 1,
                'unit_final' => 200.00,
            ]);

        $resDiscount->assertRedirect();

        $draft = $this->booking->finalQuotation;
        $this->assertCount(4, $draft->lineItems); // 1 initial service line + 3 added
    }

    public function test_admin_can_add_decimal_labor_hours_and_percentage_discount_lines(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.bookings.billing.show', $this->booking));

        // Add Labor Line with decimal hours (2.5 hrs @ 500/hr)
        $resLabor = $this->actingAs($this->admin)
            ->post(route('admin.bookings.billing.lines.store', $this->booking), [
                'item_type' => 'labor',
                'description' => 'Surface Prep & Sanding',
                'quantity' => 2.5,
                'unit_final' => 500.00,
            ]);

        $resLabor->assertRedirect();
        $resLabor->assertSessionHas('success');

        // Add Percentage Discount Line (Senior Citizen Discount, -5% in notes)
        $resDiscount = $this->actingAs($this->admin)
            ->post(route('admin.bookings.billing.lines.store', $this->booking), [
                'item_type' => 'discount',
                'description' => 'Senior Citizen Discount',
                'quantity' => 1,
                'unit_final' => 2750.00,
                'notes' => '-5%',
            ]);

        $resDiscount->assertRedirect();
        $resDiscount->assertSessionHas('success');

        $laborItem = $this->booking->finalQuotation->lineItems()->where('item_type', 'labor')->first();
        $this->assertNotNull($laborItem);
        $this->assertEquals(2.5, (float) $laborItem->quantity);
        $this->assertEquals(500.00, (float) $laborItem->unit_final);
        $this->assertEquals(1250.00, (float) $laborItem->line_total_computed);

        $discountItem = $this->booking->finalQuotation->lineItems()->where('item_type', 'discount')->first();
        $this->assertNotNull($discountItem);
        $this->assertEquals('-5%', $discountItem->notes);
        $this->assertEquals(2750.00, (float) $discountItem->unit_final);
    }

    public function test_admin_can_finalize_billing_with_unit_finals_set(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.bookings.billing.show', $this->booking));

        $draft = $this->booking->finalQuotation;

        // Set unit_final on the initial service line item
        $serviceLine = $draft->lineItems()->first();
        $serviceLine->update([
            'unit_final' => 2500.00,
            'line_total' => 2500.00,
        ]);

        // Add a part line with unit_final
        $this->actingAs($this->admin)
            ->post(route('admin.bookings.billing.lines.store', $this->booking), [
                'item_type' => 'product',
                'description' => 'Brake Pads',
                'quantity' => 1,
                'unit_final' => 1500.00,
            ]);

        // Finalize
        $response = $this->actingAs($this->admin)
            ->post(route('admin.bookings.billing.finalize', $this->booking), [
                'notes' => 'Official final billing invoice.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $draft->refresh();
        $this->assertTrue($draft->isFinalized());
        $this->assertEquals(Quotation::STATUS_APPROVED, $draft->status);
        $this->assertEquals(4000.00, (float) $draft->final_total);
        $this->assertEquals(2500.00, (float) $draft->services_subtotal);
        $this->assertEquals(1500.00, (float) $draft->products_subtotal);
        $this->assertEquals(4000.00, (float) $draft->balance_due_snapshot);
    }

    public function test_customer_can_view_own_billing_but_cannot_view_others(): void
    {
        // Own booking
        $responseOwn = $this->actingAs($this->customer)
            ->get(route('customer.bookings.billing', $this->booking));

        $responseOwn->assertOk();
        $responseOwn->assertViewIs('customer.booking-billing');

        // Other customer's booking
        $responseOther = $this->actingAs($this->otherCustomer)
            ->get(route('customer.bookings.billing', $this->booking));

        $responseOther->assertForbidden();
    }

    public function test_staff_can_add_lines_to_draft(): void
    {
        $response = $this->actingAs($this->staff)
            ->post(route('staff.bookings.billing.lines.store', $this->booking), [
                'item_type' => 'additional_service',
                'description' => 'Rotor Resurfacing',
                'quantity' => 2,
                'unit_final' => 600.00,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_finalized_billing_rejects_further_line_edits(): void
    {
        // Finalize draft
        $this->actingAs($this->admin)
            ->get(route('admin.bookings.billing.show', $this->booking));

        $draft = $this->booking->finalQuotation;
        $draft->lineItems()->update(['unit_final' => 2000.00, 'line_total' => 2000.00]);

        $this->actingAs($this->admin)
            ->post(route('admin.bookings.billing.finalize', $this->booking));

        $draft->refresh();
        $this->assertTrue($draft->isFinalized());

        // Attempt to add new line item
        $response = $this->actingAs($this->admin)
            ->post(route('admin.bookings.billing.lines.store', $this->booking), [
                'item_type' => 'product',
                'description' => 'Unauthorized Add',
                'quantity' => 1,
                'unit_final' => 100.00,
            ]);

        $response->assertForbidden();
    }
}
