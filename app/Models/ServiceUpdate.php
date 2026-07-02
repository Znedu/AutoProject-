<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceUpdate extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'job_order_id',
        'user_id',
        'message',
        'is_visible_to_customer',
    ];

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ServiceUpdatePhoto::class);
    }

    public function scopeVisibleToCustomer(Builder $query): Builder
    {
        return $query->where('is_visible_to_customer', true);
    }

    public function scopeForJobOrder(Builder $query, int $jobOrderId): Builder
    {
        return $query->where('job_order_id', $jobOrderId);
    }

    protected function casts(): array
    {
        return [
            'is_visible_to_customer' => 'boolean',
        ];
    }
}
