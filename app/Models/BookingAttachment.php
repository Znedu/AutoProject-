<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingAttachment extends Model
{
    public const TYPE_DAMAGE_PHOTO = 'damage_photo';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'type',
        'disk',
        'file_path',
        'original_name',
        'mime_type',
        'size_bytes',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->file_path
            ? asset('storage/'.$this->file_path)
            : null);
    }
}
