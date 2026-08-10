<?php

namespace App\Notifications\Support;

use App\Enums\NotificationType;
use App\Models\SupportTicket;
use App\Notifications\BaseNotification;

class TicketReopenedNotification extends BaseNotification
{
    public function __construct(public SupportTicket $ticket) {}

    public function toArray(mixed $notifiable): array
    {
        return [
            'type'        => NotificationType::TICKET_REOPENED->value,
            'title'       => 'Support Ticket Reopened',
            'message'     => "Support ticket #{$this->ticket->ticket_number} was reopened by customer.",
            'action_url'  => route('staff.assistance'),
            'icon'        => 'message-square',
            'entity_type' => 'ticket',
            'entity_id'   => $this->ticket->id,
        ];
    }
}
