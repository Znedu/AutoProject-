<?php

namespace App\Services\Notification;

use App\Enums\SmsStatus;
use App\Models\SmsOutbox;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class TxtFlowService
{
    /**
     * Queue an outgoing SMS message into the sms_outbox table.
     *
     * @param  array<string, mixed>  $context
     */
    public function queue(string $phone, string $message, array $context = []): SmsOutbox
    {
        return SmsOutbox::create([
            'to'      => $phone,
            'body'    => $message,
            'status'  => SmsStatus::PENDING,
            'context' => $context ?: null,
        ]);
    }

    /**
     * Get all pending messages so the TxtFlow Android app can fetch and send them.
     *
     * @return Collection<int, SmsOutbox>
     */
    public function getPendingMessages(): Collection
    {
        return SmsOutbox::pending()->orderBy('created_at')->get();
    }

    /**
     * Mark a queued message as sent after the Android app confirms delivery.
     */
    public function markAsSent(string $id): void
    {
        SmsOutbox::where('id', $id)->update([
            'status'  => SmsStatus::SENT,
            'sent_at' => Carbon::now(),
        ]);
    }

    /**
     * Mark a queued message as failed.
     */
    public function markAsFailed(string $id): void
    {
        SmsOutbox::where('id', $id)->update([
            'status' => SmsStatus::FAILED,
        ]);
    }

    /**
     * Handle an incoming SMS or delivery report posted by the Android app.
     *
     * Delivery reports have a non-null $outboxId (the original message ID).
     * Incoming replies have a null $outboxId.
     */
    public function handleIncoming(
        string  $from,
        string  $body,
        int     $timestamp,
        ?string $outboxId = null,
        string  $type = 'incoming',
    ): void {
        if ($type === 'delivery_report' && $outboxId !== null) {
            // Mark the original outgoing message as sent.
            $this->markAsSent($outboxId);

            Log::channel('stack')->info('[TxtFlow] Delivery confirmed', [
                'outbox_id' => $outboxId,
                'from'      => $from,
                'timestamp' => $timestamp,
            ]);

            return;
        }

        // Log incoming replies for now. Extend this to create support tickets,
        // store in a dedicated table, or notify staff as needed.
        Log::channel('stack')->info('[TxtFlow] Incoming SMS', [
            'from'      => $from,
            'body'      => $body,
            'timestamp' => $timestamp,
        ]);
    }

    /**
     * Delete sent messages older than 30 days.
     * Called by the Android app's cron/clean endpoint.
     */
    public function cleanSent(): int
    {
        return SmsOutbox::sent()
            ->where('sent_at', '<', Carbon::now()->subDays(30))
            ->forceDelete();
    }
}
