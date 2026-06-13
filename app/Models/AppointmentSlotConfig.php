<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AppointmentSlotConfig extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'day_of_week',
        'starts_at',
        'ends_at',
        'slot_duration_minutes',
        'max_capacity',
        'is_active',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDay(Builder $query, int $dayOfWeek): Builder
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_duration_minutes' => 'integer',
            'max_capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
