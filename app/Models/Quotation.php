<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    public const TYPE_INITIAL_ESTIMATE = 'initial_estimate';

    public const TYPE_INSPECTION = 'inspection';

    public const TYPE_FINAL = 'final';

    public const TYPE_REVISION = 'revision';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_SUPERSEDED = 'superseded';

    public const STATUS_REJECTED = 'rejected';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'version',
        'type',
        'status',
        'min_total',
        'max_total',
        'final_total',
        'services_subtotal',
        'products_subtotal',
        'labor_subtotal',
        'discounts_total',
        'fees_subtotal',
        'amount_paid_snapshot',
        'balance_due_snapshot',
        'currency',
        'notes',
        'prepared_by',
        'approved_by',
        'approved_at',
        'finalized_at',
        'finalized_by',
        'valid_until',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(QuotationLineItem::class)->orderBy('sort_order');
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function isFinalized(): bool
    {
        return $this->status === self::STATUS_APPROVED && $this->finalized_at !== null;
    }

    public function isEditable(): bool
    {
        return $this->type === self::TYPE_FINAL && $this->status === self::STATUS_DRAFT;
    }

    protected function totalRangeDisplay(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->final_total !== null) {
                return '₱'.number_format((float) $this->final_total, 2);
            }

            return sprintf(
                '₱%s - ₱%s',
                number_format((float) $this->min_total),
                number_format((float) $this->max_total),
            );
        });
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeFinal(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_FINAL);
    }

    public function scopeForBooking(Builder $query, int $bookingId): Builder
    {
        return $query->where('booking_id', $bookingId);
    }

    public function scopeLatestVersion(Builder $query): Builder
    {
        return $query->orderByDesc('version');
    }

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'min_total' => 'decimal:2',
            'max_total' => 'decimal:2',
            'final_total' => 'decimal:2',
            'services_subtotal' => 'decimal:2',
            'products_subtotal' => 'decimal:2',
            'labor_subtotal' => 'decimal:2',
            'discounts_total' => 'decimal:2',
            'fees_subtotal' => 'decimal:2',
            'amount_paid_snapshot' => 'decimal:2',
            'balance_due_snapshot' => 'decimal:2',
            'approved_at' => 'datetime',
            'finalized_at' => 'datetime',
            'valid_until' => 'date',
        ];
    }
}
