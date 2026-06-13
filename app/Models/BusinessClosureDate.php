<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BusinessClosureDate extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'closure_date',
        'reason',
        'is_recurring_rule',
    ];

    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('closure_date', $date);
    }

    public function scopeRecurring(Builder $query): Builder
    {
        return $query->where('is_recurring_rule', true);
    }

    protected function casts(): array
    {
        return [
            'closure_date' => 'date',
            'is_recurring_rule' => 'boolean',
        ];
    }
}
