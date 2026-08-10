<?php

namespace App\Notifications\Support;

use App\Enums\NotificationType;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Notifications\BaseNotification;

class TicketReplyNotification extends BaseNotification
{
    public function __construct(
        public SupportTicket $ticket,
        public SupportTicketReply $reply
    ) {}

    public function toArray(mixed $notifiable): array
    {
        $isCustomer = method_exists($notifiable, 'isCustomer') && $notifiable->isCustomer();

        $actionUrl = $isCustomer
            ? route('customer.support.index')
            : route('staff.assistance');

        return [
            'type'        => NotificationType::TICKET_REPLY->value,
            'title'       => 'Support Ticket Reply',
            'message'     => "New reply on ticket #{$this->ticket->ticket_number}: {$this->reply->message}",
            'action_url'  => $actionUrl,
            'icon'        => 'message-circle',
            'entity_type' => 'ticket',
            'entity_id'   => $this->ticket->id,
        ];
    }
}
