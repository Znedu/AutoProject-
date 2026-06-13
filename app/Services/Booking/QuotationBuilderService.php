<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\Service;
use Illuminate\Support\Collection;

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
        $minTotal = $services->sum('min_cost');
        $maxTotal = $services->sum('max_cost');

        $quotation = Quotation::create([
            'booking_id' => $booking->id,
            'version' => 1,
            'type' => Quotation::TYPE_INITIAL_ESTIMATE,
            'status' => Quotation::STATUS_PENDING,
            'min_total' => $minTotal,
            'max_total' => $maxTotal,
            'currency' => 'PHP',
            'notes' => 'Auto-generated from customer service selection.',
        ]);

        foreach ($services->values() as $index => $service) {
            QuotationLineItem::create([
                'quotation_id' => $quotation->id,
                'service_id' => $service->id,
                'description' => $service->name,
                'brand_preference' => $brandPreferences[$service->id] ?? null,
                'quantity' => 1,
                'unit_min' => $service->min_cost,
                'unit_max' => $service->max_cost,
                'sort_order' => $index + 1,
            ]);
        }

        return $quotation->load('lineItems');
    }
}
