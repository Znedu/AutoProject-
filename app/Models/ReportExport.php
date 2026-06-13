<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExport extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const FORMAT_CSV = 'csv';

    public const FORMAT_XLSX = 'xlsx';

    public const FORMAT_PDF = 'pdf';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'requested_by',
        'report_type',
        'parameters',
        'format',
        'status',
        'file_path',
        'error_message',
        'completed_at',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeOfType(Builder $query, string $reportType): Builder
    {
        return $query->where('report_type', $reportType);
    }

    protected function casts(): array
    {
        return [
            'parameters' => 'array',
            'completed_at' => 'datetime',
        ];
    }
}
