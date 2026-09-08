<?php

namespace App\Notifications\Inventory;

use App\Enums\NotificationType;
use App\Models\Product;
use App\Models\User;
use App\Notifications\BaseNotification;

class LowStockNotification extends BaseNotification
{
    public function __construct(public Product $product) {}

    public function toArray(mixed $notifiable): array
    {
        $actionUrl = match (true) {
            $notifiable instanceof User && $notifiable->isMechanic() => route('mechanic.inventory.index'),
            default => route('admin.inventory.index'),
        };

        return [
            'type' => NotificationType::LOW_STOCK->value,
            'title' => 'Low Stock Alert',
            'message' => "Product '{$this->product->name}' (SKU: {$this->product->sku}) is running low on stock ({$this->product->stock_quantity} {$this->product->unit_label} remaining, min threshold: {$this->product->min_stock_threshold}).",
            'action_url' => $actionUrl,
            'icon' => 'alert-triangle',
            'entity_type' => 'product',
            'entity_id' => $this->product->id,
        ];
    }
}
