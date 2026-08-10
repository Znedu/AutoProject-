<?php

namespace App\Notifications\Support;

use App\Enums\NotificationType;
use App\Models\SupportTicket;
use App\Notifications\BaseNotification;

class TicketCreatedNotification extends BaseNotification
{
    public function __construct(public SupportTicket $ticket) {}

    public function toArray(mixed $notifiable): array
    {
        return [
            'type'        => NotificationType::TICKET_CREATED->value,
            'title'       => 'New Support Ticket',
            'message'     => "New support ticket #{$this->ticket->ticket_number} created: {$this->ticket->subject}",
            'action_url'  => route('staff.assistance'),
            'icon'        => 'message-square',
            'entity_type' => 'ticket',
            'entity_id'   => $this->ticket->id,
        ];
    }
}
