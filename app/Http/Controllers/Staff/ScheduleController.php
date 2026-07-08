<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\Booking\ScheduleAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleAvailabilityService $availability
    ) {}

    public function index(Request $request): View
    {
        // Week offset allows navigating weeks (e.g. ?week=-1 for previous week)
        $offset = (int) $request->query('week', 0);
        $startOfWeek = Carbon::now()->startOfWeek()->addWeeks($offset);
        $endOfWeek   = $startOfWeek->copy()->endOfWeek();

        // Build a 7-day calendar array
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $date     = $startOfWeek->copy()->addDays($i);
            $dateStr  = $date->toDateString();
            $isToday  = $date->isToday();
            $isSunday = $date->isSunday();

            if ($isSunday) {
                $days[] = [
                    'date'        => $dateStr,
                    'label'       => $date->format('D, M d'),
                    'is_today'    => $isToday,
                    'is_sunday'   => true,
                    'is_closed'   => true,
                    'slots'       => [],
                    'bookings'    => [],
                ];
                continue;
            }

            $avail   = $this->availability->availabilityForDate($dateStr);
            $allSlots = $this->availability->slotsForDate($dateStr);

            // Bookings for this day
            $dayBookings = Booking::whereNotIn('status', ['cancelled', 'rejected'])
                ->where(function ($q) use ($dateStr) {
                    $q->whereDate('preferred_date', $dateStr)
                      ->orWhereDate('scheduled_date', $dateStr);
                })
                ->with(['services', 'user', 'vehicle'])
                ->orderBy('preferred_time')
                ->get()
                ->map(fn ($b) => [
                    'id'       => $b->id,
                    'customer' => $b->customer_name ?? ($b->user?->name ?? 'Unknown'),
                    'service'  => $b->services->first()?->name ?? 'Custom Service',
                    'vehicle'  => $b->vehicle
                        ? "{$b->vehicle->make} {$b->vehicle->model}"
                        : 'Unknown',
                    'time'     => $b->preferred_time ? $b->preferred_time->format('g:i A') : 'N/A',
                    'status'   => $b->status,
                    'is_walk_in' => $b->is_walk_in,
                ])->values()->toArray();

            // Slot status mapping
            $formattedSlots = collect($allSlots)->map(function ($slot) use ($dateStr, $avail) {
                $label = $this->availability->formatSlotLabel($slot);
                $isAvailable = in_array($label, $avail['available_slots']);
                return [
                    'time'      => $label,
                    'raw_time'  => $slot,
                    'available' => $isAvailable,
                    'booked'    => !$isAvailable,
                ];
            })->values()->toArray();

            $days[] = [
                'date'          => $dateStr,
                'label'         => $date->format('D, M d'),
                'is_today'      => $isToday,
                'is_sunday'     => false,
                'is_closed'     => $avail['is_fully_booked'] && count($avail['available_slots']) === 0,
                'is_fully_booked' => $avail['is_fully_booked'],
                'slots'         => $formattedSlots,
                'bookings'      => $dayBookings,
                'booking_count' => count($dayBookings),
            ];
        }

        $prevWeek = $offset - 1;
        $nextWeek = $offset + 1;

        return view('staff.schedule', [
            'days'         => $days,
            'weekLabel'    => $startOfWeek->format('M d') . ' – ' . $endOfWeek->format('M d, Y'),
            'prevWeekUrl'  => route('staff.schedule', ['week' => $prevWeek]),
            'nextWeekUrl'  => route('staff.schedule', ['week' => $nextWeek]),
            'currentWeekUrl' => route('staff.schedule'),
            'isCurrentWeek'  => $offset === 0,
        ]);
    }
}
