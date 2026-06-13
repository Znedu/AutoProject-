<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class SupportTicketReply extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'message',
        'is_internal',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(SupportTicketAttachment::class, 'attachable');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_internal', false);
    }

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }
}
