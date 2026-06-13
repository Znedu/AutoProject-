<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicket extends Model
{
    use SoftDeletes;

    public const STATUS_OPEN = 'open';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_RESOLVED = 'resolved';

    public const STATUS_CLOSED = 'closed';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_number',
        'user_id',
        'booking_id',
        'assigned_to',
        'subject',
        'message',
        'status',
        'priority',
        'resolved_at',
        'resolved_by',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(SupportTicketReply::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(SupportTicketAttachment::class, 'attachable');
    }

    protected function displayStatus(): Attribute
    {
        return Attribute::get(fn (): string => str($this->status)->replace('_', ' ')->title()->toString());
    }

    protected function isOpen(): Attribute
    {
        return Attribute::get(fn (): bool => in_array($this->status, [
            self::STATUS_OPEN,
            self::STATUS_IN_PROGRESS,
        ], true));
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeUnresolved(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_OPEN, self::STATUS_IN_PROGRESS]);
    }

    public function scopeAssignedTo(Builder $query, int $staffId): Builder
    {
        return $query->where('assigned_to', $staffId);
    }

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }
}
