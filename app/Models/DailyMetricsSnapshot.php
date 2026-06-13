<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class DailyMetricsSnapshot extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'metric_date',
        'total_bookings',
        'completed_bookings',
        'total_revenue',
        'new_customers',
        'metadata',
    ];

    protected function formattedRevenue(): Attribute
    {
        return Attribute::get(fn (): string => '₱'.number_format((float) $this->total_revenue, 2));
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('metric_date', $date);
    }

    public function scopeBetweenDates(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('metric_date', [$from, $to]);
    }

    protected function casts(): array
    {
        return [
            'metric_date' => 'date',
            'total_bookings' => 'integer',
            'completed_bookings' => 'integer',
            'total_revenue' => 'decimal:2',
            'new_customers' => 'integer',
            'metadata' => 'array',
        ];
    }
}
