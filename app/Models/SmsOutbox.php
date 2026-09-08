<?php

namespace App\Models;

use App\Enums\SmsStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsOutbox extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'sms_outbox';

    protected $fillable = [
        'to',
        'body',
        'status',
        'notifiable_type',
        'notifiable_id',
        'context',
        'sent_at',
    ];

    protected $casts = [
        'status'  => SmsStatus::class,
        'context' => 'array',
        'sent_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only pending messages.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', SmsStatus::PENDING);
    }

    /**
     * Scope a query to only sent messages.
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', SmsStatus::SENT);
    }
}
