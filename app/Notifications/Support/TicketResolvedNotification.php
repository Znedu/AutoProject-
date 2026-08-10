<?php

namespace App\Notifications\Support;

use App\Enums\NotificationType;
use App\Models\SupportTicket;
use App\Notifications\BaseNotification;

class TicketResolvedNotification extends BaseNotification
{
    public function __construct(public SupportTicket $ticket) {}

    public function toArray(mixed $notifiable): array
    {
        return [
            'type'        => NotificationType::TICKET_RESOLVED->value,
            'title'       => 'Support Ticket Resolved',
            'message'     => "Your support ticket #{$this->ticket->ticket_number} has been marked as resolved.",
            'action_url'  => route('customer.support.index'),
            'icon'        => 'check-circle',
            'entity_type' => 'ticket',
            'entity_id'   => $this->ticket->id,
        ];
    }
}
