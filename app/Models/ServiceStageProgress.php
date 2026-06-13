<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceStageProgress extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'job_order_id',
        'service_stage_id',
        'is_completed',
        'is_current',
        'completed_at',
        'completed_by',
    ];

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function serviceStage(): BelongsTo
    {
        return $this->belongsTo(ServiceStage::class);
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('is_completed', true);
    }

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'is_current' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }
}
