<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;

class AssistanceController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::with(['replies.user', 'user'])
            ->latest()
            ->get()
            ->map(function ($ticket) {
                $replies = $ticket->replies->map(function ($reply) use ($ticket) {
                    return [
                        'from'    => $reply->user_id === $ticket->user_id ? 'Customer' : 'Staff',
                        'message' => $reply->message,
                        'date'    => $reply->created_at->format('F d, Y - g:i A'),
                    ];
                })->toArray();

                return [
                    'id'       => $ticket->id,
                    'customer' => $ticket->user?->name ?? 'Unknown',
                    'subject'  => $ticket->subject,
                    'message'  => $ticket->message,
                    'status'   => $ticket->status,
                    'date'     => $ticket->created_at->format('F d, Y'),
                    'replies'  => $replies,
                ];
            });

        return view('staff.assistance', [
            'tickets' => $tickets,
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id'           => auth()->id(),
            'message'           => $request->message,
            'is_internal'       => false,
        ]);

        if ($ticket->status === SupportTicket::STATUS_OPEN) {
            $ticket->update(['status' => SupportTicket::STATUS_IN_PROGRESS]);
        }

        return response()->json([
            'success' => true,
            'reply'   => [
                'from'    => 'Staff',
                'message' => $reply->message,
                'date'    => $reply->created_at->format('F d, Y - g:i A'),
            ],
        ]);
    }

    public function resolve(SupportTicket $ticket)
    {
        $ticket->update([
            'status'      => SupportTicket::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Allow Staff to change a ticket status to any valid status:
     * open | in_progress | resolved | closed
     */
    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $data = ['status' => $request->status];

        if ($request->status === SupportTicket::STATUS_RESOLVED && $ticket->status !== SupportTicket::STATUS_RESOLVED) {
            $data['resolved_at'] = now();
            $data['resolved_by'] = auth()->id();
        }

        $ticket->update($data);

        return response()->json(['success' => true, 'status' => $request->status]);
    }
}
