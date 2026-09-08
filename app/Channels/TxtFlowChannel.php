<?php

namespace App\Channels;

use App\Services\Notification\TxtFlowService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class TxtFlowChannel
{
    public function __construct(private readonly TxtFlowService $service) {}

    /**
     * Send the given notification via TxtFlow SMS gateway.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        // Guard: feature must be enabled in config.
        if (! config('services.txtflow.enabled', false)) {
            return;
        }

        // 1. Resolve phone number: check notifiable->phone first
        $phone = $notifiable->phone ?? null;

        // Fallback to booking contact number if user profile has no phone
        if (empty($phone)) {
            if (isset($notification->booking) && ! empty($notification->booking->contact_number)) {
                $phone = $notification->booking->contact_number;
            } elseif (isset($notification->jobOrder?->booking) && ! empty($notification->jobOrder->booking->contact_number)) {
                $phone = $notification->jobOrder->booking->contact_number;
            }
        }

        if (empty($phone)) {
            return;
        }

        // Sanitize phone number (strip whitespace, dashes, parens)
        $phone = preg_replace('/[^\d+]/', '', trim((string) $phone));
        if (str_starts_with($phone, '09')) {
            $phone = '+63' . substr($phone, 1);
        }

        // Safety guard: only send SMS to customers (admins/staff/mechanics do not receive SMS)
        if ($notifiable instanceof \App\Models\User && ! $notifiable->isCustomer()) {
            return;
        }

        // Guard: notification must implement toTxtFlow().
        if (! method_exists($notification, 'toTxtFlow')) {
            return;
        }

        try {
            $body = $notification->toTxtFlow($notifiable);

            if (empty($body)) {
                return;
            }

            $this->service->queue($phone, $body);
        } catch (\Throwable $e) {
            Log::channel('stack')->error('[TxtFlow] Failed to queue SMS', [
                'phone'     => $phone,
                'error'     => $e->getMessage(),
                'notifiable'=> get_class($notifiable),
            ]);
        }
    }
}
