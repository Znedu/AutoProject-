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
    public const CATEGORY_ENGINE_OIL = 'Engine Oils';
    public const CATEGORY_TIRES = 'Tires & Wheels';
    public const CATEGORY_BRAKES = 'Brakes & Suspension';
    public const CATEGORY_ACCESSORIES = 'Custom Accessories';
    public const CATEGORY_PAINT = 'Paint & Bodywork';
    public const CATEGORY_ELECTRICAL = 'Electrical & Lighting';
    public const CATEGORY_GENERAL = 'General Parts';

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
        'cost_price',
        'stock_quantity',
        'min_stock_threshold',
        'location',
        'unit_label',
        'status',
    ];

    public function lineItems(): HasMany
    {
        return $this->hasMany(QuotationLineItem::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InventoryAdjustment::class)->latest();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'min_stock_threshold');
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '<=', 0);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $sub) use ($term) {
            $sub->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('category', 'like', "%{$term}%")
                ->orWhere('location', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        }

        if ($this->stock_quantity <= $this->min_stock_threshold) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            default => 'In Stock',
        };
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= $this->min_stock_threshold;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->stock_quantity <= 0;
    }

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'min_stock_threshold' => 'integer',
        ];
    }
}

