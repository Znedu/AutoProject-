<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\BookingPaintPanel;
use App\Models\PaintPanel;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\Service;
use App\Models\ServiceBrand;
use App\Models\User;
use App\Services\Billing\BookingBillingService;
use Illuminate\Support\Collection;

class QuotationBuilderService
{
    /**
     * @param  Collection<int, Service>  $services
     * @param  array<int, string|null>  $brandPreferences  keyed by service id
     * @param  array<int, string>  $panels  list of panel codes
     * @param  array<string, string>  $panelSeverities  keyed by panel code
     */
    public function createInitialEstimate(
        Booking $booking,
        Collection $services,
        array $brandPreferences = [],
        array $panels = [],
        array $panelSeverities = [],
    ): Quotation {
        $estimateData = $this->calculateEstimateData($services, $brandPreferences, $panels, $panelSeverities);

        $quotation = Quotation::create([
            'booking_id' => $booking->id,
            'version' => 1,
            'type' => Quotation::TYPE_INITIAL_ESTIMATE,
            'status' => Quotation::STATUS_PENDING,
            'min_total' => round($estimateData['min'], 2),
            'max_total' => round($estimateData['max'], 2),
            'currency' => 'PHP',
            'notes' => 'Auto-generated from customer service, scope, and damage review selection.',
        ]);

        foreach ($estimateData['line_data'] as $item) {
            QuotationLineItem::create(array_merge($item, [
                'quotation_id' => $quotation->id,
            ]));
        }

        return $quotation->load('lineItems');
    }

    /**
     * Pure estimate calculation used by both initial estimate generator and the live paint-estimate API.
     *
     * @param  Collection<int, Service>  $services
     * @param  array<int, string|null>  $brandPreferences
     * @param  array<int, string>  $panels
     * @param  array<string, string>  $panelSeverities
     * @return array{
     *     min: float,
     *     max: float,
     *     display: string,
     *     isExact: bool,
     *     breakdown: list<array<string, mixed>>,
     *     line_data: list<array<string, mixed>>,
     * }
     */
    public function calculateEstimateData(
        Collection $services,
        array $brandPreferences = [],
        array $panels = [],
        array $panelSeverities = [],
    ): array {
        $services->loadMissing('brands');

        // Resolve human-readable panel names
        $panelNamesMap = [];
        if (! empty($panels)) {
            $panelNamesMap = PaintPanel::query()
                ->whereIn('code', $panels)
                ->pluck('name', 'code')
                ->toArray();
        }

        $minTotal = 0.0;
        $maxTotal = 0.0;
        $lineData = [];
        $breakdown = [];

        foreach ($services->values() as $index => $service) {
            $isPaint = $service->isPaintRelated();

            $brandName = null;
            $selectedBrand = null;

            // Brand selection is disabled for paint / body / build package services
            if (! $isPaint) {
                $brandName = $brandPreferences[$service->id] ?? null;
                if ($brandName) {
                    $selectedBrand = $service->brands->first(
                        fn (ServiceBrand $b) => strtolower(trim($b->name)) === strtolower(trim($brandName))
                    );
                }
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

            // Build detailed description for paint scope if panels were selected
            $description = $service->name;
            $details = null;

            if ($isPaint && ! empty($panels)) {
                $formattedPanelNames = array_values(array_intersect_key($panelNamesMap, array_flip($panels)));
                if (! empty($formattedPanelNames)) {
                    $panelListStr = implode(', ', $formattedPanelNames);
                    if ($service->isPaintTouchUp()) {
                        $description = "{$service->name} ({$panelListStr})";
                        $details = count($formattedPanelNames) . ' panel(s): ' . $panelListStr;
                    } elseif ($service->isCustomPaint()) {
                        $description = "{$service->name} (" . count($formattedPanelNames) . ' panels: ' . $panelListStr . ')';
                        $details = count($formattedPanelNames) . ' panel(s) selected: ' . $panelListStr;
                    } elseif ($service->isBodyRepair()) {
                        $description = "{$service->name} ({$panelListStr})";
                        $details = 'Repairing: ' . $panelListStr;
                    } elseif ($service->isFullCustomBuild()) {
                        $description = "{$service->name} (Panels: {$panelListStr})";
                        $details = 'Custom build scope: ' . $panelListStr;
                    }
                }
            }

            $lineData[] = [
                'service_id' => $service->id,
                'item_type' => QuotationLineItem::ITEM_TYPE_SERVICE,
                'description' => $description,
                'brand_preference' => $brandName,
                'quantity' => 1,
                'unit_min' => $unitMin,
                'unit_max' => $unitMax,
                'unit_final' => $unitFinal,
                'sort_order' => $index + 1,
            ];

            $breakdown[] = [
                'service_id' => $service->id,
                'name' => $service->name,
                'min' => $unitMin,
                'max' => $unitMax,
                'display' => $unitMin === $unitMax
                    ? '₱' . number_format($unitMin, 2)
                    : '₱' . number_format($unitMin) . ' - ₱' . number_format($unitMax),
                'details' => $details,
            ];
        }

        // Automatic Body Repair Trigger rule:
        // Body repair (₱5k–₱7k): Once per booking when any panel has severity 'dent' or 'body_work'.
        $hasBodyRepairService = $services->contains(fn (Service $s) => $s->isBodyRepair());
        $dentPanels = [];

        foreach ($panels as $pCode) {
            $sev = $panelSeverities[$pCode] ?? null;
            if (in_array($sev, [
                BookingPaintPanel::SEVERITY_DENT,
                BookingPaintPanel::SEVERITY_BODY_WORK,
                'dent',
                'body_work',
            ], true)) {
                $dentPanels[] = $panelNamesMap[$pCode] ?? $pCode;
            }
        }

        if (! empty($dentPanels) && ! $hasBodyRepairService) {
            $bodyRepairService = Service::query()->where('code', Service::CODE_BODY_REPAIR)->first();
            $bodyMin = $bodyRepairService ? (float) $bodyRepairService->min_cost : 5000.00;
            $bodyMax = $bodyRepairService ? (float) $bodyRepairService->max_cost : 7000.00;

            $minTotal += $bodyMin;
            $maxTotal += $bodyMax;

            $bodyDescription = 'Body repair (dent / body work required on: ' . implode(', ', $dentPanels) . ')';

            $lineData[] = [
                'service_id' => $bodyRepairService?->id,
                'item_type' => QuotationLineItem::ITEM_TYPE_ADDITIONAL_SERVICE,
                'description' => $bodyDescription,
                'brand_preference' => null,
                'quantity' => 1,
                'unit_min' => $bodyMin,
                'unit_max' => $bodyMax,
                'unit_final' => null,
                'sort_order' => count($lineData) + 1,
            ];

            $breakdown[] = [
                'service_id' => $bodyRepairService?->id,
                'name' => 'Body repair (Additional)',
                'min' => $bodyMin,
                'max' => $bodyMax,
                'display' => '₱' . number_format($bodyMin) . ' - ₱' . number_format($bodyMax),
                'details' => 'Auto-added due to dent or body work on ' . implode(', ', $dentPanels),
            ];
        }

        $isExact = ($minTotal === $maxTotal);
        $display = $isExact
            ? '₱' . number_format($minTotal, 2)
            : '₱' . number_format($minTotal) . ' - ₱' . number_format($maxTotal);

        return [
            'min' => $minTotal,
            'max' => $maxTotal,
            'display' => $display,
            'isExact' => $isExact,
            'breakdown' => $breakdown,
            'line_data' => $lineData,
        ];
    }

    public function createFinalDraftFromBooking(Booking $booking, ?User $preparedBy = null): Quotation
    {
        return app(BookingBillingService::class)->createFinalDraft($booking, $preparedBy);
    }
}
