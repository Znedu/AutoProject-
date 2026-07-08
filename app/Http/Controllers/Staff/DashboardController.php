<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\SupportTicket;

class DashboardController extends Controller
{
    public function index()
    {
        // ──────── Stats ────────
        $todayBookingsCount  = Booking::whereDate('preferred_date', today())->count();
        $walkInCount         = Booking::walkIns()->whereDate('preferred_date', today())->count();
        $scheduledTodayCount = Booking::scheduledOn(now()->toDateString())->count();
        $openTicketsCount    = SupportTicket::whereIn('status', ['open', 'in_progress'])->count();
        $resolvedTodayCount  = SupportTicket::where('status', 'resolved')
            ->whereDate('resolved_at', today())
            ->count();

        // ──────── Pending Bookings (awaiting schedule) ────────
        $pendingBookings = Booking::pending()
            ->with(['services', 'user'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($booking) => [
                'id'       => $booking->id,
                'service'  => $booking->services->first()?->name ?? 'Custom Service',
                'customer' => $booking->customer_name ?? ($booking->user?->name ?? 'Unknown'),
                'date'     => $booking->preferred_date ? $booking->preferred_date->format('F d, Y') : 'N/A',
                'status'   => $booking->status,
                'is_walk_in' => $booking->is_walk_in,
            ]);

        // ──────── Open Support Tickets ────────
        $openTickets = SupportTicket::whereIn('status', ['open', 'in_progress'])
            ->with('user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($ticket) => [
                'id'       => $ticket->id,
                'subject'  => $ticket->subject,
                'customer' => $ticket->user?->name ?? 'Unknown',
                'date'     => $ticket->created_at->format('F d, Y'),
                'status'   => $ticket->status,
            ]);

        // ──────── Upcoming Schedule (next 7 days) ────────
        $upcomingSchedule = Booking::whereNotIn('status', ['cancelled', 'rejected'])
            ->where(function ($q) {
                $q->whereDate('scheduled_date', '>=', today())
                  ->whereDate('scheduled_date', '<=', today()->addDays(7));
            })
            ->with(['services', 'vehicle', 'user'])
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->limit(8)
            ->get()
            ->map(fn ($booking) => [
                'time'     => $booking->scheduled_time ? $booking->scheduled_time->format('g:i A') : 'N/A',
                'date'     => $booking->scheduled_date ? $booking->scheduled_date->format('M d') : 'N/A',
                'service'  => $booking->services->first()?->name ?? 'Custom Service',
                'customer' => $booking->customer_name ?? ($booking->user?->name ?? 'Unknown'),
                'vehicle'  => $booking->vehicle
                    ? "{$booking->vehicle->make} {$booking->vehicle->model}"
                    : 'Unknown',
                'status'   => $booking->status,
            ]);

        // ──────── Today's Schedule ────────
        $todaySchedule = Booking::scheduledOn(now()->toDateString())
            ->with(['services', 'vehicle', 'user'])
            ->orderBy('scheduled_time')
            ->get()
            ->map(fn ($booking) => [
                'time'     => $booking->scheduled_time ? $booking->scheduled_time->format('g:i A') : 'N/A',
                'service'  => $booking->services->first()?->name ?? 'Custom Service',
                'customer' => $booking->customer_name ?? ($booking->user?->name ?? 'Unknown'),
                'vehicle'  => $booking->vehicle
                    ? "{$booking->vehicle->make} {$booking->vehicle->model}"
                    : 'Unknown',
                'status'   => $booking->status,
            ]);

        return view('staff.dashboard', [
            'todayBookingsCount'  => $todayBookingsCount,
            'walkInCount'         => $walkInCount,
            'scheduledTodayCount' => $scheduledTodayCount,
            'openTicketsCount'    => $openTicketsCount,
            'resolvedTodayCount'  => $resolvedTodayCount,
            'pendingBookings'     => $pendingBookings,
            'openTickets'         => $openTickets,
            'upcomingSchedule'    => $upcomingSchedule,
            'todaySchedule'       => $todaySchedule,
        ]);
    }
}
