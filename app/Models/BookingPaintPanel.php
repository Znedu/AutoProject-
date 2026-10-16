<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingPaintPanel extends Model
{
    public const SEVERITY_LIGHT_SCRATCH = 'light_scratch';
    public const SEVERITY_PAINT_ONLY = 'paint_only';
    public const SEVERITY_DENT = 'dent';
    public const SEVERITY_BODY_WORK = 'body_work';
    public const SEVERITY_REPLACE_PANEL = 'replace_panel';

    public const SEVERITIES = [
        self::SEVERITY_LIGHT_SCRATCH => 'Light Scratch / Scuff',
        self::SEVERITY_PAINT_ONLY => 'Paint Only (No Dent)',
        self::SEVERITY_DENT => 'Dent / Ding Repair Needed',
        self::SEVERITY_BODY_WORK => 'Deep Dent / Major Body Work',
        self::SEVERITY_REPLACE_PANEL => 'Panel Replacement Recommended',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'booking_id',
        'panel_code',
        'severity',
        'note',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function panel(): BelongsTo
    {
        return $this->belongsTo(PaintPanel::class, 'panel_code', 'code');
    }

    public function getSeverityLabelAttribute(): ?string
    {
        return self::SEVERITIES[$this->severity] ?? ($this->severity ? ucfirst(str_replace('_', ' ', $this->severity)) : null);
    }
}
