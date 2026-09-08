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
     * GET /api/txtflow/health-check
     *
     * Used by the TxtFlow Android app to verify server availability.
     */
    public function healthCheck(): Response
    {
        return response('OK', Response::HTTP_OK)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * GET /api/txtflow/messages
     *
     * Returns all pending SMS messages queued for delivery via the Android app.
     */
    public function messages(): JsonResponse
    {
        $messages = $this->service->getPendingMessages()
            ->map(fn (SmsOutbox $item): array => [
                'id'   => (string) $item->id,
                'to'   => (string) $item->to,
                'body' => (string) $item->body,
            ]);

        return response()->json($messages);
    }

    /**
     * POST /api/txtflow/message
     *
     * Receives delivery reports or incoming SMS replies from the TxtFlow Android app.
     */
    public function receiveMessage(Request $request): JsonResponse
    {
        $from      = (string) ($request->input('from') ?? $request->input('sender') ?? '');
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

        return response()->json([
            'status'  => 'success',
            'message' => 'Processed successfully.',
        ]);
    }

    /**
     * POST /api/txtflow/cron/clean
     *
     * Purges sent messages older than 30 days.
     */
    public function clean(): JsonResponse
    {
        $deleted = $this->service->cleanSent();

        return response()->json([
            'status'  => 'success',
            'deleted' => $deleted,
        ]);
    }
}
