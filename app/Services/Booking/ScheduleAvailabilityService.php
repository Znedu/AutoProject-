<?php

namespace App\Services\Booking;

use App\Models\AppointmentSlotConfig;
use App\Models\Booking;
use App\Models\BusinessClosureDate;
use Carbon\Carbon;

class ScheduleAvailabilityService
{
    /**
     * Default hourly slots when no slot configuration exists.
     *
     * @var list<string>
     */
    public const DEFAULT_SLOTS = [
        '08:00', '09:00', '10:00', '11:00', '12:00',
        '13:00', '14:00', '15:00', '16:00', '17:00',
    ];

    /**
     * Statuses that occupy a schedule slot.
     *
     * @var list<string>
     */
    public const OCCUPYING_STATUSES = [
        Booking::STATUS_PENDING,
        Booking::STATUS_PENDING_PAYMENT_VERIFICATION,
        Booking::STATUS_PAYMENT_REQUIRES_RESUBMISSION,
        Booking::STATUS_APPROVED,
        Booking::STATUS_WAITING_PAYMENT,
        Booking::STATUS_CONFIRMED,
        Booking::STATUS_SCHEDULED,
        Booking::STATUS_IN_PROGRESS,
    ];

    public function isDateBookable(string $date): bool
    {
        $carbon = Carbon::parse($date);

        if ($carbon->isSunday()) {
            return false;
        }

        return ! BusinessClosureDate::query()->onDate($date)->exists();
    }

    public function isSlotPast(string $date, string $time): bool
    {
        return Carbon::parse($date . ' ' . $time)->isPast();
    }

    public function isSlotAvailable(string $date, string $time, ?int $excludeBookingId = null): bool
    {
        if (! $this->isDateBookable($date)) {
            return false;
        }

        if ($this->isSlotPast($date, $time)) {
            return false;
        }

        $normalizedTime = $this->normalizeTime($time);
        $capacity = $this->capacityForDate($date);

        $bookedCount = $this->bookedCountForSlot($date, $normalizedTime, $excludeBookingId);

        return $bookedCount < $capacity;
    }

    /**
     * @return array{is_fully_booked: bool, available_slots: list<string>, booked_slots: list<string>, past_slots: list<string>}
     */
    public function availabilityForDate(string $date): array
    {
        if (! $this->isDateBookable($date)) {
            return [
                'is_fully_booked' => true,
                'available_slots' => [],
                'booked_slots' => [],
                'past_slots' => [],
            ];
        }

        $slots = $this->slotsForDate($date);
        $available = [];
        $booked = [];
        $past = [];

        foreach ($slots as $slot) {
            $label = $this->formatSlotLabel($slot);

            if ($this->isSlotPast($date, $slot)) {
                $past[] = $label;
            } elseif ($this->isSlotAvailable($date, $slot)) {
                $available[] = $label;
            } else {
                $booked[] = $label;
            }
        }

        return [
            'is_fully_booked' => $available === [],
            'available_slots' => $available,
            'booked_slots' => $booked,
            'past_slots' => $past,
        ];
    }

    public function normalizeTime(string $time): string
    {
        return Carbon::parse($time)->format('H:i:s');
    }

    public function formatSlotLabel(string $time): string
    {
        return Carbon::parse($time)->format('h:i A');
    }

    /**
     * @return list<string>
     */
    public function slotsForDate(string $date): array
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        $config = AppointmentSlotConfig::query()
            ->active()
            ->forDay($dayOfWeek)
            ->first();

        if ($config === null) {
            return self::DEFAULT_SLOTS;
        }

        $slots = collect();
        $cursor = Carbon::parse($config->starts_at);
        $end = Carbon::parse($config->ends_at);

        while ($cursor->lt($end)) {
            $slots->push($cursor->format('H:i'));
            $cursor->addMinutes($config->slot_duration_minutes);
        }

        return $slots->unique()->values()->all();
    }

    protected function capacityForDate(string $date): int
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        return (int) (AppointmentSlotConfig::query()
            ->active()
            ->forDay($dayOfWeek)
            ->value('max_capacity') ?? 1);
    }

    protected function bookedCountForSlot(string $date, string $normalizedTime, ?int $excludeBookingId = null): int
    {
        $shortTime = substr($normalizedTime, 0, 5); // e.g. "08:00"

        return Booking::query()
            ->when($excludeBookingId, fn ($query) => $query->where('id', '!=', $excludeBookingId))
            ->whereIn('status', self::OCCUPYING_STATUSES)
            ->where(function ($query) use ($date, $normalizedTime, $shortTime) {
                $query->where(function ($scheduled) use ($date, $normalizedTime, $shortTime) {
                    $scheduled->whereDate('scheduled_date', $date)
                        ->where(function ($q) use ($normalizedTime, $shortTime) {
                            $q->whereTime('scheduled_time', $normalizedTime)
                              ->orWhere('scheduled_time', $normalizedTime)
                              ->orWhere('scheduled_time', $shortTime)
                              ->orWhere('scheduled_time', 'like', $shortTime . '%');
                        });
                })->orWhere(function ($preferred) use ($date, $normalizedTime, $shortTime) {
                    $preferred->whereNull('scheduled_date')
                        ->whereDate('preferred_date', $date)
                        ->where(function ($q) use ($normalizedTime, $shortTime) {
                            $q->whereTime('preferred_time', $normalizedTime)
                              ->orWhere('preferred_time', $normalizedTime)
                              ->orWhere('preferred_time', $shortTime)
                              ->orWhere('preferred_time', 'like', $shortTime . '%');
                        });
                });
            })
            ->count();
    }
}
