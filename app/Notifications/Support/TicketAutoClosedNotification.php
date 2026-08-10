<?php

namespace App\Notifications\Support;

use App\Enums\NotificationType;
use App\Models\SupportTicket;
use App\Notifications\BaseNotification;

class TicketAutoClosedNotification extends BaseNotification
{
    public function __construct(public SupportTicket $ticket) {}

    public function toArray(mixed $notifiable): array
    {
        return [
            'type'        => NotificationType::TICKET_AUTO_CLOSED->value,
            'title'       => 'Support Ticket Auto-Closed',
            'message'     => "Support ticket #{$this->ticket->ticket_number} was automatically closed after 3 days of inactivity.",
            'action_url'  => route('customer.support.index'),
            'icon'        => 'info',
            'entity_type' => 'ticket',
            'entity_id'   => $this->ticket->id,
        ];
    }
}
