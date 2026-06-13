<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $tickets = SupportTicket::where('user_id', $userId)
            ->with('replies.user')
            ->latest()
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'message' => $ticket->message,
                    'status' => $ticket->status,
                    'date' => $ticket->created_at->format('F d, Y'),
                    'replies' => $ticket->replies->count(),
                ];
            });

        $ticketReplies = [];
        $dbTickets = SupportTicket::where('user_id', $userId)->with('replies.user')->get();
        foreach ($dbTickets as $ticket) {
            $replies = [];
            foreach ($ticket->replies as $reply) {
                $replies[] = [
                    'id' => $reply->id,
                    'author' => $reply->user_id === $userId ? 'You' : ($reply->user?->name ?? 'Support Agent'),
                    'role' => $reply->user_id === $userId ? 'customer' : 'staff',
                    'message' => $reply->message,
                    'date' => $reply->created_at->format('F d, Y'),
                    'time' => $reply->created_at->format('g:i A'),
                ];
            }
            $ticketReplies[$ticket->id] = $replies;
        }

        return view('customer.support', [
            'tickets' => $tickets,
            'ticketReplies' => $ticketReplies,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => 'TKT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'open',
        ]);

        return response()->json([
            'success' => true,
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'message' => $ticket->message,
                'status' => $ticket->status,
                'date' => $ticket->created_at->format('F d, Y'),
                'replies' => 0,
            ]
        ]);
    }

    public function storeReply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        // Ensure ticket belongs to authenticated user
        if ($ticket->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $reply = SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_internal' => false,
        ]);

        return response()->json([
            'success' => true,
            'reply' => [
                'id' => $reply->id,
                'author' => 'You',
                'role' => 'customer',
                'message' => $reply->message,
                'date' => $reply->created_at->format('F d, Y'),
                'time' => $reply->created_at->format('g:i A'),
            ]
        ]);
    }
}
