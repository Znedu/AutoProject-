<?php

namespace App\Services\Notification;

use App\Enums\SmsStatus;
use App\Models\SmsOutbox;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsGateService
{
    /**
     * Send an SMS message using SMS Gateway for Android Cloud API.
     *
     * @param  array<string, mixed>  $context
     */
    public function send(
        string $phone,
        string $message,
        ?Model $notifiable = null,
        array $context = []
    ): SmsOutbox {
        // 1. Create audit trail record in sms_outbox
        $outbox = SmsOutbox::create([
            'to'              => $phone,
            'body'            => $message,
            'status'          => SmsStatus::PENDING,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id'   => $notifiable?->getKey(),
            'context'         => $context ?: null,
        ]);

        // Guard: check if gateway is enabled
        if (! config('services.smsgate.enabled', false)) {
            Log::channel('stack')->info('[SmsGate] SMS sending skipped because service is disabled', [
                'outbox_id' => $outbox->id,
                'phone'     => $phone,
            ]);

            return $outbox;
        }

        $baseUrl  = rtrim(config('services.smsgate.base_url', 'https://api.sms-gate.app/3rdparty/v1'), '/');
        $login    = config('services.smsgate.login', '');
        $password = config('services.smsgate.password', '');

        if (empty($login) || empty($password)) {
            $outbox->update([
                'status'        => SmsStatus::FAILED,
                'error_message' => 'SMS Gateway credentials (login or password) are not configured.',
            ]);

            Log::channel('stack')->error('[SmsGate] Missing SMS Gateway credentials in configuration');

            return $outbox;
        }

        try {
            $response = Http::withBasicAuth($login, $password)
                ->timeout(10)
                ->post("{$baseUrl}/message", [
                    'message'      => $message,
                    'phoneNumbers' => [$phone],
                ]);

            if ($response->successful()) {
                $gatewayId = $response->json('id');

                $outbox->update([
                    'status'     => SmsStatus::SENT,
                    'gateway_id' => $gatewayId,
                    'sent_at'    => Carbon::now(),
                ]);

                Log::channel('stack')->info('[SmsGate] SMS sent successfully', [
                    'outbox_id'   => $outbox->id,
                    'gateway_id'  => $gatewayId,
                    'phone'       => $phone,
                ]);
            } else {
                $errorMessage = "HTTP {$response->status()}: " . $response->body();

                $outbox->update([
                    'status'        => SmsStatus::FAILED,
                    'error_message' => $errorMessage,
                ]);

                Log::channel('stack')->error('[SmsGate] API request failed', [
                    'outbox_id' => $outbox->id,
                    'phone'     => $phone,
                    'status'    => $response->status(),
                    'response'  => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            $outbox->update([
                'status'        => SmsStatus::FAILED,
                'error_message' => $e->getMessage(),
            ]);

            Log::channel('stack')->error('[SmsGate] Exception while sending SMS', [
                'outbox_id' => $outbox->id,
                'phone'     => $phone,
                'error'     => $e->getMessage(),
            ]);
        }

        return $outbox;
    }

    /**
     * Broadcast an SMS message to multiple phone numbers.
     *
     * @param  array<int, string>  $numbers
     * @return array{success: int, failed: int}
     */
    public function broadcast(array $numbers, string $message): array
    {
        $success = 0;
        $failed  = 0;

        foreach ($numbers as $number) {
            $phone = preg_replace('/[^\d+]/', '', trim((string) $number));
            if (str_starts_with($phone, '09')) {
                $phone = '+63' . substr($phone, 1);
            }

            if (empty($phone)) {
                $failed++;
                continue;
            }

            $outbox = $this->send($phone, $message);
            if ($outbox->status === SmsStatus::SENT) {
                $success++;
            } else {
                $failed++;
            }
        }

        return [
            'success' => $success,
            'failed'  => $failed,
        ];
    }

    /**
     * Delete sent messages older than specified days.
     */
    public function cleanSent(int $days = 30): int
    {
        return SmsOutbox::sent()
            ->where('sent_at', '<', Carbon::now()->subDays($days))
            ->forceDelete();
    }
}
