<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingService extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'service_id',
        'preferred_brand',
        'unit_min_snapshot',
        'unit_max_snapshot',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    protected function casts(): array
    {
        return [
            'unit_min_snapshot' => 'decimal:2',
            'unit_max_snapshot' => 'decimal:2',
        ];
    }
}
