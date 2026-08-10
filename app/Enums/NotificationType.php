<?php

namespace App\Enums;

enum NotificationType: string
{
    case NEW_BOOKING = 'new_booking';
    case BOOKING_CONFIRMED = 'booking_confirmed';
    case BOOKING_APPROVED = 'booking_approved';
    case BOOKING_REJECTED = 'booking_rejected';
    case BOOKING_CANCELLED = 'booking_cancelled';
    case WALKIN_BOOKING_CREATED = 'walkin_booking_created';
    case PAYMENT_REJECTED = 'payment_rejected';
    case PAYMENT_RESUBMITTED = 'payment_resubmitted';
    case BOOKING_AUTO_CANCELLED = 'booking_auto_cancelled';
    case JOB_ORDER_CREATED = 'job_order_created';
    case JOB_ASSIGNED = 'job_assigned';
    case JOB_UNASSIGNED = 'job_unassigned';
    case JOB_STARTED = 'job_started';
    case JOB_COMPLETED = 'job_completed';
    case SERVICE_UPDATE = 'service_update';
    case TICKET_CREATED = 'ticket_created';
    case TICKET_REPLY = 'ticket_reply';
    case TICKET_RESOLVED = 'ticket_resolved';
    case TICKET_REOPENED = 'ticket_reopened';
    case TICKET_AUTO_CLOSED = 'ticket_auto_closed';
    case APPOINTMENT_SCHEDULED = 'appointment_scheduled';
}
