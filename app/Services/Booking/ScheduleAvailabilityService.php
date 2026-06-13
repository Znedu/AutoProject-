<?php

namespace App\Services\Booking;

use App\Models\AppointmentSlotConfig;
use App\Models\Booking;
use App\Models\BusinessClosureDate;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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

    public function isSlotAvailable(string $date, string $time, ?int $excludeBookingId = null): bool
    {
        if (! $this->isDateBookable($date)) {
            return false;
        }

        $normalizedTime = $this->normalizeTime($time);
        $capacity = $this->capacityForDate($date);

        $bookedCount = $this->bookedCountForSlot($date, $normalizedTime, $excludeBookingId);

        return $bookedCount < $capacity;
    }

    /**
     * @return array{is_fully_booked: bool, available_slots: list<string>, booked_slots: list<string>}
     */
    public function availabilityForDate(string $date): array
    {
        if (! $this->isDateBookable($date)) {
            return [
                'is_fully_booked' => true,
                'available_slots' => [],
                'booked_slots' => [],
            ];
        }

        $slots = $this->slotsForDate($date);
        $available = [];
        $booked = [];

        foreach ($slots as $slot) {
            if ($this->isSlotAvailable($date, $slot)) {
                $available[] = $this->formatSlotLabel($slot);
            } else {
                $booked[] = $this->formatSlotLabel($slot);
            }
        }

        return [
            'is_fully_booked' => $available === [],
            'available_slots' => $available,
            'booked_slots' => $booked,
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
        return Booking::query()
            ->when($excludeBookingId, fn ($query) => $query->where('id', '!=', $excludeBookingId))
            ->whereIn('status', self::OCCUPYING_STATUSES)
            ->where(function ($query) use ($date, $normalizedTime) {
                $query->where(function ($preferred) use ($date, $normalizedTime) {
                    $preferred->whereDate('preferred_date', $date)
                        ->whereTime('preferred_time', $normalizedTime);
                })->orWhere(function ($scheduled) use ($date, $normalizedTime) {
                    $scheduled->whereDate('scheduled_date', $date)
                        ->whereTime('scheduled_time', $normalizedTime);
                });
            })
            ->count();
    }
}
