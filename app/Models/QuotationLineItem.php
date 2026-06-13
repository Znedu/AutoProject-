<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationLineItem extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'quotation_id',
        'service_id',
        'description',
        'brand_preference',
        'quantity',
        'unit_min',
        'unit_max',
        'unit_final',
        'sort_order',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    protected function lineTotalDisplay(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->unit_final !== null) {
                return '₱'.number_format((float) $this->unit_final * (float) $this->quantity, 2);
            }

            return sprintf(
                '₱%s - ₱%s',
                number_format((float) $this->unit_min * (float) $this->quantity),
                number_format((float) $this->unit_max * (float) $this->quantity),
            );
        });
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_min' => 'decimal:2',
            'unit_max' => 'decimal:2',
            'unit_final' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }
}
