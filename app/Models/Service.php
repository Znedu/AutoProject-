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

    public const CODE_PAINT_TOUCH_UP = 'paint-001';
    public const CODE_CUSTOM_PAINT = 'paint-002';
    public const CODE_BODY_REPAIR = 'body-001';
    public const CODE_FULL_CUSTOM_BUILD = 'pkg-001';
    public const CODE_PERF_EXHAUST = 'perf-exhaust';
    public const CODE_PERF_INTAKE = 'perf-intake';
    public const CODE_PERF_INTERCOOLER = 'perf-intercooler';
    public const CODE_MAINT_GENERAL = 'maint-general';

    public const PAINT_SERVICE_CODES = [
        self::CODE_PAINT_TOUCH_UP,
        self::CODE_CUSTOM_PAINT,
        self::CODE_BODY_REPAIR,
        self::CODE_FULL_CUSTOM_BUILD,
    ];

    public const PRIMARY_PAINT_CODES = [
        self::CODE_PAINT_TOUCH_UP,
        self::CODE_CUSTOM_PAINT,
        self::CODE_FULL_CUSTOM_BUILD,
    ];

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

    public function isPaintRelated(): bool
    {
        return in_array($this->code, self::PAINT_SERVICE_CODES, true);
    }

    public function isPrimaryPaint(): bool
    {
        return in_array($this->code, self::PRIMARY_PAINT_CODES, true);
    }

    public function isPaintTouchUp(): bool
    {
        return $this->code === self::CODE_PAINT_TOUCH_UP;
    }

    public function isCustomPaint(): bool
    {
        return $this->code === self::CODE_CUSTOM_PAINT;
    }

    public function isBodyRepair(): bool
    {
        return $this->code === self::CODE_BODY_REPAIR;
    }

    public function isFullCustomBuild(): bool
    {
        return $this->code === self::CODE_FULL_CUSTOM_BUILD;
    }

    public function requiresDamagePhotos(): bool
    {
        return $this->isPaintRelated();
    }

    protected function casts(): array
    {
        return [
            'min_cost' => 'decimal:2',
            'max_cost' => 'decimal:2',
        ];
    }
}
