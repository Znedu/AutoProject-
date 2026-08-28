<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationLineItem extends Model
{
    public const ITEM_TYPE_SERVICE = 'service';

    public const ITEM_TYPE_PRODUCT = 'product';

    public const ITEM_TYPE_MATERIAL = 'material';

    public const ITEM_TYPE_ADDITIONAL_SERVICE = 'additional_service';

    public const ITEM_TYPE_LABOR = 'labor';

    public const ITEM_TYPE_DISCOUNT = 'discount';

    public const ITEM_TYPE_FEE = 'fee';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'quotation_id',
        'service_id',
        'product_id',
        'item_type',
        'description',
        'brand_preference',
        'sku',
        'notes',
        'quantity',
        'unit_min',
        'unit_max',
        'unit_final',
        'line_total',
        'source',
        'added_by',
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    protected function lineTotalComputed(): Attribute
    {
        return Attribute::get(function (): ?float {
            if ($this->line_total !== null) {
                return (float) $this->line_total;
            }

            if ($this->unit_final !== null) {
                return (float) $this->quantity * (float) $this->unit_final;
            }

            return null;
        });
    }

    protected function lineTotalDisplay(): Attribute
    {
        return Attribute::get(function (): string {
            $computed = $this->line_total_computed;

            if ($computed !== null) {
                if ($this->item_type === self::ITEM_TYPE_DISCOUNT) {
                    return '-₱'.number_format(abs($computed), 2);
                }

                return '₱'.number_format($computed, 2);
            }

            if ($this->unit_min !== null && $this->unit_max !== null) {
                return sprintf(
                    '₱%s - ₱%s',
                    number_format((float) $this->unit_min * (float) $this->quantity),
                    number_format((float) $this->unit_max * (float) $this->quantity),
                );
            }

            return '—';
        });
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_min' => 'decimal:2',
            'unit_max' => 'decimal:2',
            'unit_final' => 'decimal:2',
            'line_total' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }
}
