<?php

namespace Tests\Feature;

use App\Enums\RoleSlug;
use App\Models\Booking;
use App\Models\PaintPanel;
use App\Models\Quotation;
use App\Models\Role;
use App\Models\Service;
use App\Models\User;
use App\Services\Booking\QuotationBuilderService;
use Database\Seeders\PaintPanelSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingPaintScopeTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected Service $touchUpService;
    protected Service $customPaintService;
    protected Service $bodyRepairService;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(RoleSeeder::class);
        $this->seed(PaintPanelSeeder::class);
        $this->seed(ServiceCatalogSeeder::class);

        $customerRole = Role::where('slug', RoleSlug::Customer->value)->firstOrFail();
        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->touchUpService = Service::where('code', 'paint-001')->firstOrFail();
        $this->customPaintService = Service::where('code', 'paint-002')->firstOrFail();
        $this->bodyRepairService = Service::where('code', 'body-001')->firstOrFail();
    }

    /**
     * Test 1: Feature test: booking with custom paint + 3 panels + 2 photos
     * -> booking has attachments, paint panels, quotation min/max 15000–20000.
     */
    public function test_booking_with_custom_paint_persists_attachments_panels_and_correct_quotation_range(): void
    {
        $payload = [
            'customer_name' => 'John Custom',
            'contact_number' => '09123456789',
            'vehicle_make' => 'Toyota',
            'vehicle_model' => 'GR Yaris',
            'vehicle_year' => 2022,
            'plate_number' => 'XYZ9876',
            'preferred_date' => today()->nextWeekday()->format('Y-m-d'),
            'preferred_time' => '10:00:00',
            'payment_method' => 'gcash',
            'reference_number' => 'GCASH-PAINT-001',
            'payment_screenshot' => UploadedFile::fake()->image('payment.png'),
            'agreed_to_terms' => '1',
            'service_ids' => [$this->customPaintService->id],
            'paint_scope' => 'custom_paint',
            'panels' => ['hood', 'front_bumper', 'roof'],
            'panel_severity' => [
                'hood' => 'paint_only',
                'front_bumper' => 'paint_only',
                'roof' => 'paint_only',
            ],
            'damage_photos' => [
                UploadedFile::fake()->image('front.png'),
                UploadedFile::fake()->image('hood.png'),
            ],
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('customer.bookings.store'), $payload);

        $response->assertRedirect(route('customer.bookings.index'));

        $booking = Booking::with(['paintScope', 'paintPanels', 'attachments', 'quotations.lineItems'])->latest('id')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('custom_paint', $booking->paintScope?->scope_mode);
        $this->assertCount(3, $booking->paintPanels);
        $this->assertCount(2, $booking->attachments);
        $this->assertEquals('damage_photo', $booking->attachments->first()->type);

        $quotation = $booking->quotations->first();
        $this->assertNotNull($quotation);
        $this->assertEquals(15000, (float) $quotation->min_total);
        $this->assertEquals(20000, (float) $quotation->max_total);
    }

    /**
     * Test 2: Feature test: touch up with 3 panels -> validation error.
     */
    public function test_touch_up_with_more_than_two_panels_fails_validation(): void
    {
        $payload = [
            'customer_name' => 'John TouchUp',
            'contact_number' => '09123456789',
            'vehicle_make' => 'Honda',
            'vehicle_model' => 'Civic',
            'vehicle_year' => 2020,
            'plate_number' => 'ABC1234',
            'preferred_date' => today()->nextWeekday()->format('Y-m-d'),
            'preferred_time' => '10:00:00',
            'payment_method' => 'gcash',
            'reference_number' => 'GCASH-TOUCHUP-001',
            'payment_screenshot' => UploadedFile::fake()->image('payment.png'),
            'agreed_to_terms' => '1',
            'service_ids' => [$this->touchUpService->id],
            'paint_scope' => 'touch_up',
            'panels' => ['hood', 'front_bumper', 'roof'], // 3 panels: invalid for touch up!
            'damage_photos' => [
                UploadedFile::fake()->image('damage.png'),
            ],
        ];

        $response = $this->actingAs($this->customer)
            ->from(route('customer.book-service'))
            ->post(route('customer.bookings.store'), $payload);

        $response->assertRedirect(route('customer.book-service'));
        $response->assertSessionHasErrors(['panels']);
    }

    /**
     * Test 3: Feature test: custom paint without photos -> validation error.
     */
    public function test_custom_paint_without_photos_fails_validation(): void
    {
        $payload = [
            'customer_name' => 'John NoPhoto',
            'contact_number' => '09123456789',
            'vehicle_make' => 'Toyota',
            'vehicle_model' => 'Corolla',
            'vehicle_year' => 2019,
            'plate_number' => 'DEF5678',
            'preferred_date' => today()->nextWeekday()->format('Y-m-d'),
            'preferred_time' => '10:00:00',
            'payment_method' => 'gcash',
            'reference_number' => 'GCASH-NOPHOTO-001',
            'payment_screenshot' => UploadedFile::fake()->image('payment.png'),
            'agreed_to_terms' => '1',
            'service_ids' => [$this->customPaintService->id],
            'paint_scope' => 'custom_paint',
            'panels' => ['hood'],
            // no damage_photos provided!
        ];

        $response = $this->actingAs($this->customer)
            ->from(route('customer.book-service'))
            ->post(route('customer.bookings.store'), $payload);

        $response->assertRedirect(route('customer.book-service'));
        $response->assertSessionHasErrors(['damage_photos']);
    }

    /**
     * Test 4: Feature test: custom paint + panel with `dent`
     * -> quotation includes body repair line 5000–7000 (total range 20000–27000).
     */
    public function test_custom_paint_with_dent_triggers_body_repair_quotation_line(): void
    {
        $payload = [
            'customer_name' => 'John Dent',
            'contact_number' => '09123456789',
            'vehicle_make' => 'Subaru',
            'vehicle_model' => 'WRX',
            'vehicle_year' => 2021,
            'plate_number' => 'WRX1111',
            'preferred_date' => today()->nextWeekday()->format('Y-m-d'),
            'preferred_time' => '10:00:00',
            'payment_method' => 'gcash',
            'reference_number' => 'GCASH-DENT-001',
            'payment_screenshot' => UploadedFile::fake()->image('payment.png'),
            'agreed_to_terms' => '1',
            'service_ids' => [$this->customPaintService->id],
            'paint_scope' => 'custom_paint',
            'panels' => ['hood', 'lf_door'],
            'panel_severity' => [
                'hood' => 'dent', // Triggers Body Repair (₱5k-₱7k)
                'lf_door' => 'paint_only',
            ],
            'damage_photos' => [
                UploadedFile::fake()->image('dent_hood.png'),
            ],
        ];

        $response = $this->actingAs($this->customer)
            ->post(route('customer.bookings.store'), $payload);

        $response->assertRedirect(route('customer.bookings.index'));

        $booking = Booking::with('quotations.lineItems')->latest('id')->first();
        $this->assertNotNull($booking);

        $quotation = $booking->quotations->first();
        $this->assertNotNull($quotation);

        // Custom Paint (15,000–20,000) + Body Repair (5,000–7,000) = 20,000–27,000
        $this->assertEquals(20000, (float) $quotation->min_total);
        $this->assertEquals(27000, (float) $quotation->max_total);

        // Check that a line item exists for Body Repair
        $bodyRepairLine = $quotation->lineItems->first(fn ($li) => str_contains(strtolower($li->description), 'body repair'));
        $this->assertNotNull($bodyRepairLine);
        $this->assertEquals(5000, (float) $bodyRepairLine->unit_min);
        $this->assertEquals(7000, (float) $bodyRepairLine->unit_max);
    }

    /**
     * Test 5: Feature test: paint estimate API matches quotation builder for same input.
     */
    public function test_paint_estimate_api_matches_quotation_builder_for_same_input(): void
    {
        $input = [
            'service_ids' => [$this->customPaintService->id],
            'brands' => [],
            'panels' => ['hood', 'front_bumper'],
            'panel_severity' => [
                'hood' => 'dent',
                'front_bumper' => 'paint_only',
            ],
        ];

        // Direct service calculation
        $quotationService = app(QuotationBuilderService::class);
        $serviceEstimate = $quotationService->calculateEstimateData(
            Service::whereIn('id', $input['service_ids'])->get(),
            $input['brands'],
            $input['panels'],
            $input['panel_severity']
        );

        // API endpoint calculation
        $response = $this->actingAs($this->customer)
            ->postJson(route('customer.booking.paint-estimate'), $input);

        $response->assertOk();
        $apiData = $response->json();

        $this->assertEquals($serviceEstimate['min'], $apiData['min']);
        $this->assertEquals($serviceEstimate['max'], $apiData['max']);
        $this->assertEquals($serviceEstimate['display'], $apiData['display']);
        $this->assertCount(count($serviceEstimate['breakdown']), $apiData['breakdown']);
    }
}
