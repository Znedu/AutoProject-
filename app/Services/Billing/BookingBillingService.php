<?php

namespace App\Services\Billing;

use App\Models\Booking;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationLineItem;
use App\Models\User;
use App\Notifications\Billing\FinalBillingFinalizedNotification;
use App\Services\Notification\NotificationDispatcherService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BookingBillingService
{
    public function __construct(
        protected BookingBillingCalculatorService $calculator,
        protected NotificationDispatcherService $dispatcher,
    ) {}

    public function createFinalDraft(Booking $booking, ?User $preparedBy = null): Quotation
    {
        return DB::transaction(function () use ($booking, $preparedBy): Quotation {
            // Find latest version across all quotations for this booking
            $latestVersion = (int) $booking->quotations()->max('version');
            $newVersion = $latestVersion + 1;

            // Mark any prior draft final quotation as superseded
            $booking->quotations()
                ->where('type', Quotation::TYPE_FINAL)
                ->where('status', Quotation::STATUS_DRAFT)
                ->update(['status' => Quotation::STATUS_SUPERSEDED]);

            // Find latest initial estimate to copy items from
            $initialEstimate = $booking->quotations()
                ->where('type', Quotation::TYPE_INITIAL_ESTIMATE)
                ->latest('version')
                ->with('lineItems')
                ->first();

            $minTotal = $initialEstimate?->min_total ?? 0.00;
            $maxTotal = $initialEstimate?->max_total ?? 0.00;

            $quotation = Quotation::create([
                'booking_id' => $booking->id,
                'version' => $newVersion,
                'type' => Quotation::TYPE_FINAL,
                'status' => Quotation::STATUS_DRAFT,
                'min_total' => $minTotal,
                'max_total' => $maxTotal,
                'final_total' => null,
                'currency' => 'PHP',
                'notes' => 'Draft final billing breakdown.',
                'prepared_by' => $preparedBy?->id,
            ]);

            if ($initialEstimate && $initialEstimate->lineItems->isNotEmpty()) {
                foreach ($initialEstimate->lineItems as $item) {
                    QuotationLineItem::create([
                        'quotation_id' => $quotation->id,
                        'service_id' => $item->service_id,
                        'product_id' => null,
                        'item_type' => QuotationLineItem::ITEM_TYPE_SERVICE,
                        'description' => $item->description,
                        'brand_preference' => $item->brand_preference,
                        'quantity' => $item->quantity,
                        'unit_min' => $item->unit_min,
                        'unit_max' => $item->unit_max,
                        'unit_final' => null,
                        'line_total' => null,
                        'source' => 'initial_estimate',
                        'added_by' => $preparedBy?->id,
                        'sort_order' => $item->sort_order,
                    ]);
                }
            } elseif ($booking->bookingServices()->exists()) {
                $bookingServices = $booking->bookingServices()->with('service')->get();
                foreach ($bookingServices as $index => $bs) {
                    $svc = $bs->service;
                    QuotationLineItem::create([
                        'quotation_id' => $quotation->id,
                        'service_id' => $bs->service_id,
                        'product_id' => null,
                        'item_type' => QuotationLineItem::ITEM_TYPE_SERVICE,
                        'description' => $svc?->name ?? 'Service',
                        'brand_preference' => $bs->preferred_brand,
                        'quantity' => 1,
                        'unit_min' => $bs->unit_min_snapshot ?? $svc?->min_cost ?? 0,
                        'unit_max' => $bs->unit_max_snapshot ?? $svc?->max_cost ?? 0,
                        'unit_final' => null,
                        'line_total' => null,
                        'source' => 'initial_estimate',
                        'added_by' => $preparedBy?->id,
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            return $quotation->load(['lineItems.product', 'lineItems.service']);
        });
    }

    /**
     * @param  array{
     *     item_type: string,
     *     description?: string|null,
     *     service_id?: int|null,
     *     product_id?: int|null,
     *     sku?: string|null,
     *     quantity?: float|int|string,
     *     unit_final?: float|int|string|null,
     *     unit_min?: float|int|string|null,
     *     unit_max?: float|int|string|null,
     *     notes?: string|null,
     *     source?: string|null,
     * }  $data
     */
    public function addLineItem(Quotation $quotation, array $data, User $user): QuotationLineItem
    {
        if (! $quotation->isEditable()) {
            throw new InvalidArgumentException('Cannot add line items to a non-editable or finalized quotation.');
        }

        return DB::transaction(function () use ($quotation, $data, $user): QuotationLineItem {
            $itemType = $data['item_type'] ?? QuotationLineItem::ITEM_TYPE_SERVICE;
            $productId = ! empty($data['product_id']) ? (int) $data['product_id'] : null;
            $product = $productId ? Product::find($productId) : null;

            $description = $data['description'] ?? $product?->name ?? 'Custom Item';
            $sku = $data['sku'] ?? $product?->sku ?? null;
            $quantity = isset($data['quantity']) ? (float) $data['quantity'] : 1.0;

            $unitFinal = isset($data['unit_final']) && $data['unit_final'] !== '' && $data['unit_final'] !== null
                ? (float) $data['unit_final']
                : ($product ? (float) $product->unit_price : null);

            $unitMin = isset($data['unit_min']) ? (float) $data['unit_min'] : ($unitFinal ?? 0.0);
            $unitMax = isset($data['unit_max']) ? (float) $data['unit_max'] : ($unitFinal ?? 0.0);

            $lineTotal = $unitFinal !== null ? round($quantity * $unitFinal, 2) : null;

            $maxSortOrder = (int) $quotation->lineItems()->max('sort_order');

            $lineItem = QuotationLineItem::create([
                'quotation_id' => $quotation->id,
                'service_id' => ! empty($data['service_id']) ? (int) $data['service_id'] : null,
                'product_id' => $productId,
                'item_type' => $itemType,
                'description' => $description,
                'brand_preference' => $data['brand_preference'] ?? null,
                'sku' => $sku,
                'notes' => $data['notes'] ?? null,
                'quantity' => $quantity,
                'unit_min' => $unitMin,
                'unit_max' => $unitMax,
                'unit_final' => $unitFinal,
                'line_total' => $lineTotal,
                'source' => $data['source'] ?? ($product ? 'catalog' : 'manual'),
                'added_by' => $user->id,
                'sort_order' => $maxSortOrder + 1,
            ]);

            $this->recalculateDraft($quotation);

            return $lineItem->load(['product', 'service']);
        });
    }

    /**
     * @param  array{
     *     description?: string,
     *     quantity?: float|int|string,
     *     unit_final?: float|int|string|null,
     *     unit_min?: float|int|string|null,
     *     unit_max?: float|int|string|null,
     *     notes?: string|null,
     *     item_type?: string,
     * }  $data
     */
    public function updateLineItem(QuotationLineItem $item, array $data): QuotationLineItem
    {
        $quotation = $item->quotation;
        if (! $quotation || ! $quotation->isEditable()) {
            throw new InvalidArgumentException('Cannot update line items on a non-editable or finalized quotation.');
        }

        return DB::transaction(function () use ($item, $data, $quotation): QuotationLineItem {
            $quantity = isset($data['quantity']) ? (float) $data['quantity'] : (float) $item->quantity;
            $unitFinal = array_key_exists('unit_final', $data)
                ? ($data['unit_final'] !== null && $data['unit_final'] !== '' ? (float) $data['unit_final'] : null)
                : $item->unit_final;

            $lineTotal = $unitFinal !== null ? round($quantity * (float) $unitFinal, 2) : null;

            $updatePayload = [
                'quantity' => $quantity,
                'unit_final' => $unitFinal,
                'line_total' => $lineTotal,
            ];

            if (isset($data['description'])) {
                $updatePayload['description'] = $data['description'];
            }
            if (isset($data['item_type'])) {
                $updatePayload['item_type'] = $data['item_type'];
            }
            if (array_key_exists('notes', $data)) {
                $updatePayload['notes'] = $data['notes'];
            }
            if (isset($data['unit_min'])) {
                $updatePayload['unit_min'] = (float) $data['unit_min'];
            }
            if (isset($data['unit_max'])) {
                $updatePayload['unit_max'] = (float) $data['unit_max'];
            }

            $item->update($updatePayload);

            $this->recalculateDraft($quotation);

            return $item->fresh(['product', 'service']);
        });
    }

    public function removeLineItem(QuotationLineItem $item): void
    {
        $quotation = $item->quotation;
        if (! $quotation || ! $quotation->isEditable()) {
            throw new InvalidArgumentException('Cannot remove line items from a non-editable or finalized quotation.');
        }

        DB::transaction(function () use ($item, $quotation): void {
            $item->delete();
            $this->recalculateDraft($quotation);
        });
    }

    public function recalculateDraft(Quotation $quotation): Quotation
    {
        $lines = $quotation->lineItems()->get();

        // Calculate final total if unit_final values are populated
        $hasUnitFinals = $lines->contains(fn (QuotationLineItem $line) => $line->unit_final !== null);

        $services = (float) $lines
            ->whereIn('item_type', [QuotationLineItem::ITEM_TYPE_SERVICE, QuotationLineItem::ITEM_TYPE_ADDITIONAL_SERVICE])
            ->sum(fn (QuotationLineItem $l) => $l->line_total_computed ?? 0);

        $products = (float) $lines
            ->whereIn('item_type', [QuotationLineItem::ITEM_TYPE_PRODUCT, QuotationLineItem::ITEM_TYPE_MATERIAL])
            ->sum(fn (QuotationLineItem $l) => $l->line_total_computed ?? 0);

        $labor = (float) $lines
            ->where('item_type', QuotationLineItem::ITEM_TYPE_LABOR)
            ->sum(fn (QuotationLineItem $l) => $l->line_total_computed ?? 0);

        $discounts = (float) abs($lines
            ->where('item_type', QuotationLineItem::ITEM_TYPE_DISCOUNT)
            ->sum(fn (QuotationLineItem $l) => $l->line_total_computed ?? 0));

        $fees = (float) $lines
            ->where('item_type', QuotationLineItem::ITEM_TYPE_FEE)
            ->sum(fn (QuotationLineItem $l) => $l->line_total_computed ?? 0);

        $finalTotal = $hasUnitFinals
            ? max(0, round($services + $products + $labor + $fees - $discounts, 2))
            : null;

        $minTotal = (float) $lines->sum(fn (QuotationLineItem $l) => ((float) $l->quantity) * ((float) ($l->unit_min ?? $l->unit_final ?? 0)));
        $maxTotal = (float) $lines->sum(fn (QuotationLineItem $l) => ((float) $l->quantity) * ((float) ($l->unit_max ?? $l->unit_final ?? 0)));

        $quotation->update([
            'min_total' => round($minTotal, 2),
            'max_total' => round($maxTotal, 2),
            'final_total' => $finalTotal,
        ]);

        return $quotation->fresh(['lineItems.product', 'lineItems.service']);
    }

    public function finalize(Booking $booking, User $admin, ?string $notes = null): Quotation
    {
        $allowedStatuses = [
            Booking::STATUS_CONFIRMED,
            Booking::STATUS_SCHEDULED,
            Booking::STATUS_IN_PROGRESS,
            Booking::STATUS_COMPLETED,
        ];

        if (! in_array($booking->status, $allowedStatuses, true)) {
            throw new InvalidArgumentException(
                "Billing can only be finalized for confirmed, scheduled, in-progress, or completed bookings. Current status: {$booking->status}"
            );
        }

        return DB::transaction(function () use ($booking, $admin, $notes): Quotation {
            $quotation = $booking->quotations()
                ->where('type', Quotation::TYPE_FINAL)
                ->where('status', Quotation::STATUS_DRAFT)
                ->latest('version')
                ->first();

            if (! $quotation) {
                // If no draft exists, create one
                $quotation = $this->createFinalDraft($booking, $admin);
            }

            $lineItems = $quotation->lineItems()->get();
            if ($lineItems->isEmpty()) {
                throw new InvalidArgumentException('Cannot finalize billing with no line items.');
            }

            // Ensure all line items have unit_final and line_total set
            foreach ($lineItems as $item) {
                if ($item->unit_final === null) {
                    throw new InvalidArgumentException(
                        "Line item '{$item->description}' must have a finalized unit price set before finalizing billing."
                    );
                }

                $lineTotal = round((float) $item->quantity * (float) $item->unit_final, 2);
                $item->update(['line_total' => $lineTotal]);
            }

            $summary = $this->calculator->calculate($booking);

            $quotation->update([
                'services_subtotal' => $summary->servicesSubtotal,
                'products_subtotal' => $summary->productsSubtotal,
                'labor_subtotal' => $summary->laborSubtotal,
                'discounts_total' => $summary->discountsTotal,
                'fees_subtotal' => $summary->feesSubtotal,
                'final_total' => $summary->finalTotal,
                'amount_paid_snapshot' => $summary->totalPaid,
                'balance_due_snapshot' => $summary->balanceDue,
                'status' => Quotation::STATUS_APPROVED,
                'finalized_at' => now(),
                'finalized_by' => $admin->id,
                'approved_by' => $admin->id,
                'approved_at' => now(),
                'notes' => $notes ?? $quotation->notes,
            ]);

            if ($booking->user) {
                $this->dispatcher->notifyUser($booking->user, new FinalBillingFinalizedNotification($booking, $quotation));
            }

            return $quotation->fresh(['lineItems.product', 'lineItems.service', 'preparer', 'approver', 'finalizer']);
        });
    }
}
