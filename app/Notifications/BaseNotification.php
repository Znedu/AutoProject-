<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toDatabase(mixed $notifiable): array
    {
        return $this->toArray($notifiable);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    abstract public function toArray(mixed $notifiable): array;

    /*
    |--------------------------------------------------------------------------
    | Future Mail Channel Extension
    |--------------------------------------------------------------------------
    | To enable email notifications in future phases:
    | 1. Update via() to return ['database', 'mail']
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
