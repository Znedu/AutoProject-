<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\Service;
use App\Models\User;
use App\Services\Billing\BookingBillingService;
use Illuminate\Support\Collection;

use App\Models\ServiceBrand;

class QuotationBuilderService
{
    /**
     * @param  Collection<int, Service>  $services
     * @param  array<int, string|null>  $brandPreferences  keyed by service id
     */
    public function createInitialEstimate(
        Booking $booking,
        Collection $services,
        array $brandPreferences = [],
    ): Quotation {
        $services->loadMissing('brands');

        $minTotal = 0;
        $maxTotal = 0;
        $lineData = [];

        foreach ($services->values() as $index => $service) {
            $brandName = $brandPreferences[$service->id] ?? null;
            $selectedBrand = null;

            if ($brandName) {
                $selectedBrand = $service->brands->first(fn (ServiceBrand $b) => strtolower(trim($b->name)) === strtolower(trim($brandName)));
            }

            if ($selectedBrand && (float) $selectedBrand->price > 0) {
                $unitMin = (float) $selectedBrand->price;
                $unitMax = (float) $selectedBrand->price;
                $unitFinal = (float) $selectedBrand->price;
            } else {
                $unitMin = (float) $service->min_cost;
                $unitMax = (float) $service->max_cost;
                $unitFinal = null;
            }

            $minTotal += $unitMin;
            $maxTotal += $unitMax;

            $lineData[] = [
                'service_id' => $service->id,
                'description' => $service->name,
                'brand_preference' => $brandName,
                'quantity' => 1,
                'unit_min' => $unitMin,
                'unit_max' => $unitMax,
                'unit_final' => $unitFinal,
                'sort_order' => $index + 1,
            ];
        }

        $quotation = Quotation::create([
            'booking_id' => $booking->id,
            'version' => 1,
            'type' => Quotation::TYPE_INITIAL_ESTIMATE,
            'status' => Quotation::STATUS_PENDING,
            'min_total' => round($minTotal, 2),
            'max_total' => round($maxTotal, 2),
            'currency' => 'PHP',
            'notes' => 'Auto-generated from customer service & brand selection.',
        ]);

        foreach ($lineData as $item) {
            QuotationLineItem::create(array_merge($item, [
                'quotation_id' => $quotation->id,
            ]));
        }

        return $quotation->load('lineItems');
    }

    public function createFinalDraftFromBooking(Booking $booking, ?User $preparedBy = null): Quotation
    {
        return app(BookingBillingService::class)->createFinalDraft($booking, $preparedBy);
    }
}
