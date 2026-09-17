<?php

namespace App\Services\Inventory;

use App\Enums\NotificationType;
use App\Models\Product;
use App\Notifications\Inventory\ExpiringProductNotification;
use App\Services\Notification\NotificationDispatcherService;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class InventoryExpirationService
{
    public function __construct(
        protected NotificationDispatcherService $dispatcher
    ) {}

    /**
     * Scan inventory for products expiring within the specified days (default 7 days / 1 week)
     * and notify relevant staff, admins, and mechanics.
     *
     * @param int $daysAdvance
     * @return Collection<int, Product> Products for which notifications were dispatched
     */
    public function notifyExpiringProducts(int $daysAdvance = 7): Collection
    {
        $expiringProducts = Product::query()
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '>=', today())
            ->where('expiration_date', '<=', today()->addDays($daysAdvance))
            ->get();

        $notifiedProducts = collect();

        foreach ($expiringProducts as $product) {
            if ($this->shouldSendNotification($product)) {
                $this->dispatcher->notifyAdminsStaffAndMechanics(new ExpiringProductNotification($product));
                $notifiedProducts->push($product);
            }
        }

        return $notifiedProducts;
    }

    /**
     * Check single product and dispatch notification if expiring within 1 week and not already notified today.
     */
    public function checkAndNotifyProduct(Product $product, int $daysAdvance = 7): bool
    {
        if (! $product->expiration_date || $product->is_expired) {
            return false;
        }

        $daysRemaining = $product->days_until_expiration;

        if ($daysRemaining !== null && $daysRemaining >= 0 && $daysRemaining <= $daysAdvance) {
            if ($this->shouldSendNotification($product)) {
                $this->dispatcher->notifyAdminsStaffAndMechanics(new ExpiringProductNotification($product, $daysRemaining));
                return true;
            }
        }

        return false;
    }

    /**
     * Prevent duplicate notifications for the same product.
     * Do not send if an unread notification already exists or if notified today.
     */
    protected function shouldSendNotification(Product $product): bool
    {
        $hasUnread = DatabaseNotification::where('type', ExpiringProductNotification::class)
            ->where('data->entity_id', $product->id)
            ->whereNull('read_at')
            ->exists();

        if ($hasUnread) {
            return false;
        }

        return ! DatabaseNotification::where('type', ExpiringProductNotification::class)
            ->where('data->entity_id', $product->id)
            ->whereDate('created_at', today())
            ->exists();
    }
}
