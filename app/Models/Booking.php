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
        'terms_accepted_at',
        'rejection_reason',
        'cancellation_reason',
        'canceled_at',
        'approved_by',
        'approved_at',
        'completed_at',
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

    protected function isCancellable(): Attribute
    {
        return Attribute::get(fn (): bool => $this->status === self::STATUS_PENDING);
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

    public function scopeScheduledOn(Builder $query, string $date): Builder
    {
        return $query->whereDate('scheduled_date', $date);
    }

    protected function casts(): array
    {
        return [
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
