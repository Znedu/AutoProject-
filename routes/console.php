<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use App\Notifications\Support\TicketAutoClosedNotification;
use App\Services\Notification\NotificationDispatcherService;
use Illuminate\Support\Facades\Schedule;
use App\Models\SupportTicket;

Schedule::call(function () {
    // Automatically transition support tickets from Resolved to Closed after 3 days of inactivity
    $tickets = SupportTicket::where('status', SupportTicket::STATUS_RESOLVED)
        ->where('resolved_at', '<=', now()->subDays(3))
        ->get();

    $dispatcher = app(NotificationDispatcherService::class);

    foreach ($tickets as $ticket) {
        $ticket->update(['status' => SupportTicket::STATUS_CLOSED]);
        if ($ticket->user) {
            $dispatcher->notifyUser($ticket->user, new TicketAutoClosedNotification($ticket));
        }
    }
})->daily();
