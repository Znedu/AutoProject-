<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    public const CATEGORY_PRODUCT = 'product';

    public const CATEGORY_MATERIAL = 'material';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sku',
        'name',
        'description',
        'category',
        'unit_price',
        'unit_label',
        'status',
    ];

    public function lineItems(): HasMany
    {
        return $this->hasMany(QuotationLineItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
        ];
    }
}
