<?php

namespace App\Channels;

use App\Services\Notification\SmsGateService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SmsChannel
{
    public function __construct(private readonly SmsGateService $service) {}

    /**
     * Send the given notification via SMS Gateway for Android.
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        // Guard: feature must be enabled in config.
        if (! config('services.smsgate.enabled', false)) {
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

        // Guard: notification must implement toSms().
        if (! method_exists($notification, 'toSms')) {
            return;
        }

        try {
            $body = $notification->toSms($notifiable);

            if (empty($body)) {
                return;
            }

            $this->service->send(
                $phone,
                $body,
                $notifiable instanceof \Illuminate\Database\Eloquent\Model ? $notifiable : null
            );
        } catch (\Throwable $e) {
            Log::channel('stack')->error('[SmsGate] Failed to send SMS via channel', [
                'phone'      => $phone,
                'error'      => $e->getMessage(),
                'notifiable' => is_object($notifiable) ? get_class($notifiable) : gettype($notifiable),
            ]);
        }
    }
}
