<?php

namespace App\Rules;

use App\Services\Booking\ScheduleAvailabilityService;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class ScheduleAvailable implements DataAwareRule, ValidationRule
{
    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    public function __construct(
        protected ScheduleAvailabilityService $scheduleAvailability,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $date = $this->data['preferred_date'] ?? null;

        if (! is_string($date) || $date === '') {
            return;
        }

        if (! $this->scheduleAvailability->isDateBookable($date)) {
            $fail('The garage is closed on the selected date. Please choose a weekday that is not blocked.');

            return;
        }

        if (! $this->scheduleAvailability->isSlotAvailable($date, (string) $value)) {
            $fail('The selected time slot is no longer available. Please choose another time.');
        }
    }
}
