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
        'expiration_date',
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

    public function scopeHasExpirationDate(Builder $query): Builder
    {
        return $query->whereNotNull('expiration_date');
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expiration_date')
            ->where('expiration_date', '<', today());
    }

    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('expiration_date')
            ->where('expiration_date', '>=', today())
            ->where('expiration_date', '<=', today()->addDays($days));
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

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiration_date !== null && $this->expiration_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiration_date !== null && ! $this->is_expired && $this->expiration_date->diffInDays(now()) <= 30;
    }

    public function getExpirationStatusAttribute(): ?string
    {
        if ($this->expiration_date === null) {
            return null;
        }

        if ($this->is_expired) {
            return 'expired';
        }

        if ($this->is_expiring_soon) {
            return 'expiring_soon';
        }

        return 'valid';
    }

    public function getExpirationStatusLabelAttribute(): string
    {
        return match ($this->expiration_status) {
            'expired' => 'Expired',
            'expiring_soon' => 'Expiring Soon',
            'valid' => 'Valid',
            default => 'N/A',
        };
    }

    /**
     * Resolve the corresponding service for this product.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Service>  $services
     */
    public function getTargetService(\Illuminate\Support\Collection $services): ?Service
    {
        $name = strtolower($this->name);
        $cat = strtolower($this->category);

        foreach ($services as $service) {
            $svcName = strtolower($service->name);
            $svcCat = strtolower($service->category?->name ?? '');

            if (str_contains($name, 'oil') || str_contains($cat, 'oil')) {
                if (str_contains($svcName, 'oil')) return $service;
            }
            if (str_contains($name, 'brake') || str_contains($name, 'pad') || str_contains($name, 'coil') || str_contains($cat, 'brake')) {
                if (str_contains($svcName, 'brake')) return $service;
            }
            if (str_contains($name, 'tire') || str_contains($name, 'wheel') || str_contains($cat, 'tire')) {
                if (str_contains($svcName, 'tire') || str_contains($svcName, 'wheel')) return $service;
            }
            if (str_contains($name, 'paint') || str_contains($name, 'coat') || str_contains($cat, 'paint')) {
                if (str_contains($svcName, 'paint') || str_contains($svcName, 'repaint')) return $service;
            }
            if (str_contains($name, 'wing') || str_contains($name, 'spoiler') || str_contains($name, 'body') || str_contains($cat, 'accessory')) {
                if (str_contains($name, 'exhaust') || str_contains($name, 'catback')) {
                    if (str_contains($svcName, 'exhaust')) return $service;
                }
                if (str_contains($svcName, 'body kit')) return $service;
            }
            if (str_contains($name, 'exhaust') || str_contains($name, 'muffler')) {
                if (str_contains($svcName, 'exhaust')) return $service;
            }
            if (str_contains($name, 'spark') || str_contains($name, 'plug') || str_contains($cat, 'electrical')) {
                if (str_contains($svcName, 'electrical') || str_contains($svcName, 'ignition') || str_contains($svcName, 'engine customization')) return $service;
            }
        }

        return $services->first();
    }

    /**
     * Resolve matching brand name for this product within a target service.
     */
    public function resolveMatchingBrandName(?Service $service): ?string
    {
        if (! $service || $service->brands->isEmpty()) {
            return null;
        }

        $prodName = strtolower($this->name);

        foreach ($service->brands as $brand) {
            $brandNameLower = strtolower($brand->name);
            // Clean brand name (remove parentheses like '(OE replacement)')
            $cleanBrandName = preg_replace('/\s*\(.*?\)/', '', $brandNameLower);
            if ($cleanBrandName && (str_contains($prodName, trim($cleanBrandName)) || str_contains(trim($cleanBrandName), explode(' ', $prodName)[0]))) {
                return $brand->name;
            }
        }

        return null;
    }

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'min_stock_threshold' => 'integer',
            'expiration_date' => 'date:Y-m-d',
        ];
    }
}

