<?php

namespace App\Console\Commands;

use App\Services\Inventory\InventoryExpirationService;
use Illuminate\Console\Command;

class CheckExpiringProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-expiring {--days=7 : Days in advance to check for expiring products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan inventory for products expiring within the advance threshold (default 1 week / 7 days) and send alerts';

    /**
     * Execute the console command.
     */
    public function handle(InventoryExpirationService $service): int
    {
        $days = (int) $this->option('days');
        $this->info("Scanning inventory for products expiring within {$days} days...");

        $notified = $service->notifyExpiringProducts($days);

        $count = $notified->count();
        $this->info("Successfully notified relevant users for {$count} expiring product(s).");

        foreach ($notified as $product) {
            $this->line(" - [{$product->sku}] {$product->name} (Expires: {$product->expiration_date?->format('Y-m-d')})");
        }

        return Command::SUCCESS;
    }
}
