<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobOrder extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ASSIGNED = 'assigned';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const PRIORITY_LOW = 'low';

    public const PRIORITY_MEDIUM = 'medium';

    public const PRIORITY_HIGH = 'high';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'job_number',
        'booking_id',
        'mechanic_id',
        'assigned_by',
        'assigned_at',
        'status',
        'priority',
        'progress_percent',
        'estimated_completion_date',
        'started_at',
        'paused_at',
        'completed_at',
        'internal_notes',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function mechanic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mechanic_id');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function stageProgress(): HasMany
    {
        return $this->hasMany(ServiceStageProgress::class);
    }

    public function serviceUpdates(): HasMany
    {
        return $this->hasMany(ServiceUpdate::class);
    }

    protected function displayStatus(): Attribute
    {
        return Attribute::get(fn (): string => str($this->status)->replace('_', ' ')->title()->toString());
    }

    public function scopeForMechanic(Builder $query, int $mechanicId): Builder
    {
        return $query->where('mechanic_id', $mechanicId);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeAssigned(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_ASSIGNED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_PAUSED,
        ]);
    }

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'estimated_completion_date' => 'date',
            'started_at' => 'datetime',
            'paused_at' => 'datetime',
            'completed_at' => 'datetime',
            'progress_percent' => 'integer',
        ];
    }
}
