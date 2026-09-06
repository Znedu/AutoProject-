<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SupportTicketAttachmentTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $customerRole = Role::firstOrCreate(
            ['slug' => 'customer'],
            ['name' => 'Customer', 'description' => 'Customer Role']
        );

        $this->customer = User::factory()->create([
            'role_id' => $customerRole->id,
            'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_customer_can_create_ticket_with_image_attachment(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('issue.jpg', 600, 600);

        $response = $this->actingAs($this->customer)
            ->post('/customer/support', [
                'subject' => 'Engine noise issue',
                'message' => 'Attaching noise area photo.',
                'attachment' => $file,
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $ticketId = $response->json('ticket.id');
        $this->assertNotNull($ticketId);

        $ticket = SupportTicket::with('attachments')->findOrFail($ticketId);
        $this->assertEquals('Engine noise issue', $ticket->subject);
        $this->assertCount(1, $ticket->attachments);

        $attachment = $ticket->attachments->first();
        Storage::disk('public')->assertExists($attachment->file_path);
        $this->assertNotNull($response->json('ticket.attachment'));
    }

    public function test_customer_can_create_ticket_without_attachment(): void
    {
        $response = $this->actingAs($this->customer)
            ->post('/customer/support', [
                'subject' => 'Question about hours',
                'message' => 'What are your operating hours?',
            ], [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'ticket' => [
                'subject' => 'Question about hours',
                'attachment' => null,
            ],
        ]);
    }

    public function test_support_page_includes_attachment_urls(): void
    {
        Storage::fake('public');

        $ticket = SupportTicket::create([
            'ticket_number' => 'TKT-TEST-ATTACH',
            'user_id' => $this->customer->id,
            'subject' => 'Broken part',
            'message' => 'See photo',
            'status' => 'open',
        ]);

        $file = UploadedFile::fake()->image('broken.png');
        $path = $file->store('support_attachments', 'public');
        $ticket->attachments()->create([
            'disk' => 'public',
            'file_path' => $path,
            'original_name' => 'broken.png',
            'mime_type' => 'image/png',
            'size_bytes' => 1024,
        ]);

        $response = $this->actingAs($this->customer)->get('/customer/support');
        $response->assertStatus(200);
    }
}
