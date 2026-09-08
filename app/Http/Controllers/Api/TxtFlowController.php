<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SmsOutbox;
use App\Services\Notification\TxtFlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TxtFlowController extends Controller
{
    public function __construct(private readonly TxtFlowService $service) {}

    /**
     * GET /health-check or /api/txtflow/health-check
     *
     * Used by the TxtFlow Android app to verify server availability.
     */
    public function healthCheck(): Response
    {
        return response('OK', Response::HTTP_OK)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * GET /messages or /api/txtflow/messages
     *
     * Returns all pending SMS messages queued for delivery via the Android app.
     * Note: TxtFlow Android app expects the recipient property to be named 'address'.
     */
    public function messages(): JsonResponse
    {
        $messages = $this->service->getPendingMessages()
            ->map(fn (SmsOutbox $item): array => [
                'id'      => (string) $item->id,
                'address' => (string) $item->to,
                'to'      => (string) $item->to,
                'body'    => (string) $item->body,
                'message' => (string) $item->body,
            ]);

        return response()->json($messages->values()->all());
    }

    /**
     * POST /message or /api/txtflow/message
     *
     * Receives delivery reports or incoming SMS replies from the TxtFlow Android app.
     * Responds with plain text 'Received' as expected by the TxtFlow mobile client.
     */
    public function receiveMessage(Request $request): Response
    {
        $from      = (string) ($request->input('from') ?? $request->input('sender') ?? $request->input('address') ?? '');
        $body      = (string) ($request->input('body') ?? $request->input('message') ?? '');
        $timestamp = (int) ($request->input('timestamp') ?? time());
        $outboxId  = $request->input('id') ?? $request->input('outbox_id');
        $type      = (string) ($request->input('type') ?? ($outboxId ? 'delivery_report' : 'incoming'));

        $this->service->handleIncoming(
            from: $from,
            body: $body,
            timestamp: $timestamp,
            outboxId: $outboxId ? (string) $outboxId : null,
            type: $type,
        );

        return response('Received', Response::HTTP_OK)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * POST /cron/clean or /api/txtflow/cron/clean
     *
     * Purges sent messages older than 30 days.
     * Responds with plain text 'Cleaned' as expected by the TxtFlow mobile client.
     */
    public function clean(): Response
    {
        $this->service->cleanSent();

        return response('Cleaned', Response::HTTP_OK)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * POST /broadcast or /api/txtflow/broadcast
     *
     * Convenience endpoint (from TxtFlow sample) allowing manual test SMS queuing.
     * Accepts: { "numbers": ["+639123456789"], "message": "Hello" }
     *      or: { "to": "+639123456789", "message": "Hello" }
     */
    public function broadcast(Request $request): JsonResponse
    {
        $numbers = $request->input('numbers');
        if (empty($numbers) && $request->filled('to')) {
            $numbers = [$request->input('to')];
        }

        $message = $request->input('message') ?? $request->input('body');

        if (! is_array($numbers) || empty($numbers) || empty($message)) {
            return response()->json([
                'error' => 'Invalid request. Expecting { "numbers": ["..."], "message": "..." }',
            ], Response::HTTP_BAD_REQUEST);
        }

        $queued = 0;
        foreach ($numbers as $number) {
            $cleanNumber = preg_replace('/[^\d+]/', '', trim((string) $number));
            if (str_starts_with($cleanNumber, '09')) {
                $cleanNumber = '+63' . substr($cleanNumber, 1);
            }

            $this->service->queue($cleanNumber, (string) $message);
            $queued++;
        }

        return response()->json([
            'success' => true,
            'queued'  => $queued,
        ]);
    }
}
