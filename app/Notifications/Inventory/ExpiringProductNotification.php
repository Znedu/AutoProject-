<?php

namespace App\Notifications\Inventory;

use App\Enums\NotificationType;
use App\Models\Product;
use App\Models\User;
use App\Notifications\BaseNotification;

class ExpiringProductNotification extends BaseNotification
{
    public function __construct(public Product $product, public ?int $daysRemaining = null)
    {
        $this->daysRemaining = $daysRemaining ?? $product->days_until_expiration;
    }

    public function toArray(mixed $notifiable): array
    {
        $actionUrl = match (true) {
            $notifiable instanceof User && $notifiable->isMechanic() => route('mechanic.inventory.index', ['search' => $this->product->sku], false),
            default => route('admin.inventory.index', ['search' => $this->product->sku, 'highlight' => $this->product->id], false),
        };

        $daysText = match (true) {
            $this->daysRemaining === 0 => 'today',
            $this->daysRemaining === 1 => 'tomorrow (in 1 day)',
            default => "in {$this->daysRemaining} days",
        };

        return [
            'type' => NotificationType::EXPIRING_PRODUCT->value,
            'title' => 'Product Expiring Soon (1-Week Warning)',
            'message' => "Product '{$this->product->name}' (SKU: {$this->product->sku}) will expire {$daysText} on {$this->product->expiration_date?->format('M d, Y')}.",
            'action_url' => $actionUrl,
            'icon' => 'alert-triangle',
            'entity_type' => 'product',
            'entity_id' => $this->product->id,
        ];
    }
}
