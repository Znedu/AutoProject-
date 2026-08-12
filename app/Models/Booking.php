<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_WAITING_PAYMENT = 'waiting_payment';

    /** Booking created & payment submitted; awaiting admin verification. */
    public const STATUS_PENDING_PAYMENT_VERIFICATION = 'pending_payment_verification';

    /** Admin rejected the payment; customer must resubmit proof. */
    public const STATUS_PAYMENT_REQUIRES_RESUBMISSION = 'payment_requires_resubmission';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'booking_number',
        'user_id',
        'vehicle_id',
        'status',
        'preferred_date',
        'preferred_time',
        'scheduled_date',
        'scheduled_time',
        'customer_name',
        'contact_number',
        'notes',
        'is_walk_in',
        'terms_accepted_at',
        'rejection_reason',
        'cancellation_reason',
        'canceled_at',
        'approved_by',
        'approved_at',
        'completed_at',
        'payment_attempts',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'booking_services')
            ->withPivot('preferred_brand')
            ->withTimestamps();
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function jobOrder(): HasOne
    {
        return $this->hasOne(JobOrder::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class);
    }

    protected function displayStatus(): Attribute
    {
        return Attribute::get(fn (): string => str($this->status)->replace('_', ' ')->title()->toString());
    }

    protected function badgeLabel(): Attribute
    {
        return Attribute::get(fn (): string => match ($this->status) {
            self::STATUS_PENDING                       => 'Awaiting Approval',
            self::STATUS_APPROVED                      => 'Approved',
            self::STATUS_REJECTED                      => 'Rejected',
            self::STATUS_WAITING_PAYMENT               => 'Waiting Payment',
            self::STATUS_PENDING_PAYMENT_VERIFICATION  => 'Pending Verification',
            self::STATUS_PAYMENT_REQUIRES_RESUBMISSION => 'Payment Rejected',
            self::STATUS_CONFIRMED                     => 'Confirmed',
            self::STATUS_SCHEDULED                     => 'Scheduled',
            self::STATUS_IN_PROGRESS                   => 'In Progress',
            self::STATUS_COMPLETED                     => 'Completed',
            self::STATUS_CANCELLED                     => 'Cancelled',
            default                                    => $this->display_status,
        });
    }

    protected function isCancellable(): Attribute
    {
        return Attribute::get(fn (): bool => !in_array($this->status, [
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_REJECTED,
        ]));
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotIn('status', [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_REJECTED,
        ]);
    }

    public function scopePendingPaymentVerification(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_PAYMENT_VERIFICATION);
    }

    public function scopePaymentRequiresResubmission(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAYMENT_REQUIRES_RESUBMISSION);
    }

    public function scopeScheduledOn(Builder $query, string $date): Builder
    {
        return $query->whereDate('scheduled_date', $date);
    }

    public function scopeWalkIns(Builder $query): Builder
    {
        return $query->where('is_walk_in', true);
    }

    protected function casts(): array
    {
        return [
            'is_walk_in' => 'boolean',
            'preferred_date' => 'date',
            'preferred_time' => 'datetime:H:i',
            'scheduled_date' => 'date',
            'scheduled_time' => 'datetime:H:i',
            'terms_accepted_at' => 'datetime',
            'canceled_at' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
