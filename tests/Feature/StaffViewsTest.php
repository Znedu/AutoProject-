<?php

namespace Tests\Feature;

use Tests\TestCase;

class StaffViewsTest extends TestCase
{
    public function test_staff_views_do_not_render_invalid_alpine_bindings(): void
    {
        $bookingQueueHtml = view('staff.booking-queue', ['bookings' => []])->render();
        $jobsHtml = view('staff.jobs', ['jobs' => [], 'selectedFilter' => 'all', 'stats' => ['total' => 0, 'unassigned' => 0, 'in_progress' => 0, 'completed' => 0]])->render();
        $assistanceHtml = view('staff.assistance', ['tickets' => []])->render();

        foreach ([$bookingQueueHtml, $jobsHtml, $assistanceHtml] as $html) {
            $this->assertStringNotContainsString('::variant', $html);
            $this->assertStringNotContainsString('::class', $html);
            $this->assertStringNotContainsString('::status', $html);
            $this->assertStringNotContainsString('::value', $html);
        }
    }
}
