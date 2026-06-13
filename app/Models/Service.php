<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'service_category_id',
        'code',
        'name',
        'description',
        'min_cost',
        'max_cost',
        'duration_label',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function brands(): HasMany
    {
        return $this->hasMany(ServiceBrand::class);
    }

    public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_services')
            ->withPivot('preferred_brand')
            ->withTimestamps();
    }

    public function quotationLineItems(): HasMany
    {
        return $this->hasMany(QuotationLineItem::class);
    }

    protected function costRangeDisplay(): Attribute
    {
        return Attribute::get(fn (): string => sprintf(
            '₱%s - ₱%s',
            number_format((float) $this->min_cost),
            number_format((float) $this->max_cost),
        ));
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('service_category_id', $categoryId);
    }

    protected function casts(): array
    {
        return [
            'min_cost' => 'decimal:2',
            'max_cost' => 'decimal:2',
        ];
    }
}
