<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    public const TYPE_RESERVATION_FEE = 'reservation_fee';

    public const TYPE_DEPOSIT = 'deposit';

    public const TYPE_FINAL_PAYMENT = 'final_payment';

    public const TYPE_REFUND = 'refund';

    public const METHOD_GCASH = 'gcash';

    public const METHOD_MAYA = 'maya';

    public const METHOD_CASH = 'cash';

    public const METHOD_BANK_TRANSFER = 'bank_transfer';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_REFUNDED = 'refunded';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'payment_number',
        'booking_id',
        'user_id',
        'type',
        'amount',
        'currency',
        'method',
        'reference_number',
        'status',
        'paid_at',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'notes',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }

    protected function formattedAmount(): Attribute
    {
        return Attribute::get(fn (): string => '₱'.number_format((float) $this->amount, 2));
    }

    protected function isVerified(): Attribute
    {
        return Attribute::get(fn (): bool => $this->status === self::STATUS_VERIFIED);
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopePendingVerification(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_SUBMITTED]);
    }

    public function scopeForBooking(Builder $query, int $bookingId): Builder
    {
        return $query->where('booking_id', $bookingId);
    }

    public function scopeReservationFees(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_RESERVATION_FEE);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }
}
