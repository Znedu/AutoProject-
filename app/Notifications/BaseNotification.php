<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{
    /**
     * Whether this notification should also be sent as an SMS via SMS Gateway.
     * Subclasses can set this to false for staff/admin-only notifications.
     */
    protected bool $sendSms = true;

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        $channels = ['database'];

        if ($this->sendSms && config('services.smsgate.enabled', false)) {
            $channels[] = 'sms';
        }

        return $channels;
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        return $this->toArray($notifiable);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    abstract public function toArray(mixed $notifiable): array;

    /**
     * Get the SMS body for SMS Gateway delivery.
     *
     * Subclasses should override this method to provide a concise,
     * SMS-friendly message (max ~160 characters recommended).
     *
     * Returns null to skip SMS delivery for this notification instance.
     */
    public function toSms(mixed $notifiable): ?string
    {
        $payload = $this->toArray($notifiable);

        $title   = $payload['title']   ?? '';
        $message = $payload['message'] ?? '';

        if (empty($title) && empty($message)) {
            return null;
        }

        return "[AutoProject+] {$title}: {$message}";
    }

    /*
    |--------------------------------------------------------------------------
    | Future Mail Channel Extension
    |--------------------------------------------------------------------------
    | To enable email notifications in future phases:
    | 1. Update via() to include 'mail' in the channels array
    | 2. Implement toMail():
    |
    | public function toMail(mixed $notifiable): \Illuminate\Notifications\Messages\MailMessage
    | {
    |     $payload = $this->toArray($notifiable);
    |     return (new \Illuminate\Notifications\Messages\MailMessage)
    |         ->subject($payload['title'])
    |         ->line($payload['message'])
    |         ->action('View', url($payload['action_url']));
    | }
    |
    */
}
