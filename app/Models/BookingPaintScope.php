<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPaintScope extends Model
{
    public const MODE_TOUCH_UP = 'touch_up';
    public const MODE_CUSTOM_PAINT = 'custom_paint';
    public const MODE_BODY_REPAIR = 'body_repair';
    public const MODE_FULL_BUILD = 'full_build';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'scope_mode',
        'vehicle_size_class',
        'metadata',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
