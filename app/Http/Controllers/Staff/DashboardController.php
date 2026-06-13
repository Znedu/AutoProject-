<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\SupportTicket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Stats
        $pendingBookingsCount = Booking::pending()->count();
        $scheduledTodayCount = Booking::scheduledOn(now()->toDateString())->count();
        $openTicketsCount = SupportTicket::whereIn('status', ['open', 'in-progress'])->count();
        $resolvedTodayCount = SupportTicket::where('status', 'resolved')
            ->whereDate('resolved_at', today())
            ->count();

        // Pending Bookings List
        $pendingBookings = Booking::pending()
            ->with(['services', 'user'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'service' => $booking->services->first()?->name ?? 'Custom Service',
                    'customer' => $booking->customer_name ?? ($booking->user?->name ?? 'Unknown'),
                    'date' => $booking->preferred_date ? $booking->preferred_date->format('F d, Y') : 'N/A',
                    'status' => $booking->status,
                ];
            });

        // Open Support Tickets
        $openTickets = SupportTicket::whereIn('status', ['open', 'in-progress'])
            ->with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'customer' => $ticket->user?->name ?? 'Unknown',
                    'date' => $ticket->created_at->format('F d, Y'),
                    'status' => $ticket->status,
                ];
            });

        // Today's Service Schedule
        $todaySchedule = Booking::scheduledOn(now()->toDateString())
            ->with(['services', 'vehicle', 'user'])
            ->orderBy('scheduled_time')
            ->get()
            ->map(function ($booking) {
                return [
                    'time' => $booking->scheduled_time ? $booking->scheduled_time->format('g:i A') : 'N/A',
                    'service' => $booking->services->first()?->name ?? 'Custom Customization',
                    'customer' => $booking->customer_name ?? ($booking->user?->name ?? 'Unknown'),
                    'vehicle' => $booking->vehicle ? "{$booking->vehicle->make} {$booking->vehicle->model}" : 'Unknown',
                    'status' => $booking->status,
                ];
            });

        return view('staff.dashboard', [
            'pendingBookingsCount' => $pendingBookingsCount,
            'scheduledTodayCount' => $scheduledTodayCount,
            'openTicketsCount' => $openTicketsCount,
            'resolvedTodayCount' => $resolvedTodayCount,
            'pendingBookings' => $pendingBookings,
            'openTickets' => $openTickets,
            'todaySchedule' => $todaySchedule,
        ]);
    }
}
