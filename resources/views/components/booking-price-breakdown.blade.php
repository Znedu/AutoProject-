@props([
    'summary',
    'editable' => false,
    'showPayments' => true,
    'booking' => null,
])

@php
    /** @var \App\DataTransferObjects\BookingBillingSummary $summary */
    $categoryLabels = [
        \App\Models\QuotationLineItem::ITEM_TYPE_SERVICE => 'Core Services',
        \App\Models\QuotationLineItem::ITEM_TYPE_ADDITIONAL_SERVICE => 'Additional Services & Upgrades',
        \App\Models\QuotationLineItem::ITEM_TYPE_PRODUCT => 'Parts & Products',
        \App\Models\QuotationLineItem::ITEM_TYPE_MATERIAL => 'Shop Materials & Supplies',
        \App\Models\QuotationLineItem::ITEM_TYPE_LABOR => 'Labor & Workshop Charges',
        \App\Models\QuotationLineItem::ITEM_TYPE_FEE => 'Fees & Miscellaneous',
        \App\Models\QuotationLineItem::ITEM_TYPE_DISCOUNT => 'Discounts & Deductions',
    ];
@endphp

<div class="space-y-6">
    {{-- Itemized Table --}}
    <x-card class="p-0 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex justify-between items-center bg-gray-50/50 dark:bg-white/[0.02]">
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white">Itemized Charges Breakdown</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Comprehensive itemization of services, parts, labor, and applied adjustments.</p>
            </div>
            @if ($summary->isFinalized)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20">
                    <x-icon name="check-circle" class="w-3.5 h-3.5" />
                    Finalized & Locked
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                    <x-icon name="info" class="w-3.5 h-3.5" />
                    Draft Breakdown
                </span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        <th class="py-3 px-4 font-semibold">Description</th>
                        <th class="py-3 px-4 font-semibold text-center w-24">Qty</th>
                        <th class="py-3 px-4 font-semibold text-right w-36">Unit Price</th>
                        <th class="py-3 px-4 font-semibold text-right w-36">Line Total</th>
                        @if ($editable && ! $summary->isFinalized)
                            <th class="py-3 px-4 font-semibold text-center w-24">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @php $hasAnyItems = false; @endphp
                    @foreach ($summary->lineItemsGrouped as $type => $items)
                        @if ($items->isNotEmpty())
                            @php $hasAnyItems = true; @endphp
                            {{-- Group Category Header --}}
                            <tr class="bg-gray-100/70 dark:bg-white/[0.03] font-semibold text-xs text-gray-600 dark:text-gray-300">
                                <td colspan="{{ ($editable && ! $summary->isFinalized) ? 5 : 4 }}" class="py-2 px-4 uppercase tracking-wider">
                                    {{ $categoryLabels[$type] ?? ucfirst(str_replace('_', ' ', $type)) }}
                                </td>
                            </tr>

                            @foreach ($items as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ $item->description }}
                                            @if ($item->sku)
                                                <span class="ml-2 text-xs font-mono px-1.5 py-0.5 rounded bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-400">
                                                    {{ $item->sku }}
                                                </span>
                                            @endif
                                        </div>
                                        @if ($item->brand_preference)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Brand: <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $item->brand_preference }}</span></p>
                                        @endif
                                        @if ($item->notes)
                                            <p class="text-xs text-gray-500 italic mt-0.5">{{ $item->notes }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center text-gray-700 dark:text-gray-300 font-mono">
                                        {{ (float) $item->quantity == (int) $item->quantity ? (int) $item->quantity : number_format((float) $item->quantity, 2) }}
                                    </td>
                                    <td class="py-3 px-4 text-right text-gray-700 dark:text-gray-300 font-mono">
                                        @if ($item->unit_final !== null)
                                            @if ($item->item_type === \App\Models\QuotationLineItem::ITEM_TYPE_DISCOUNT)
                                                <span class="text-red-600 font-medium">-₱{{ number_format((float) $item->unit_final, 2) }}</span>
                                            @else
                                                ₱{{ number_format((float) $item->unit_final, 2) }}
                                            @endif
                                        @elseif ($item->unit_min !== null && $item->unit_max !== null)
                                            <span class="text-xs text-gray-500">₱{{ number_format((float) $item->unit_min) }} - ₱{{ number_format((float) $item->unit_max) }}</span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium font-mono {{ $item->item_type === \App\Models\QuotationLineItem::ITEM_TYPE_DISCOUNT ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">
                                        {{ $item->line_total_display }}
                                    </td>
                                    @if ($editable && ! $summary->isFinalized)
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button
                                                    type="button"
                                                    @click="$dispatch('edit-line', {
                                                        id: {{ $item->id }},
                                                        item_type: '{{ $item->item_type }}',
                                                        description: {{ json_encode($item->description) }},
                                                        quantity: {{ (float) $item->quantity }},
                                                        unit_final: {{ $item->unit_final !== null ? (float) $item->unit_final : "''" }},
                                                        notes: {{ json_encode($item->notes ?? '') }}
                                                    })"
                                                    class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-500 hover:text-gray-900 dark:hover:text-white transition"
                                                    title="Edit Line"
                                                >
                                                    <x-icon name="pencil" class="w-4 h-4" />
                                                </button>
                                                <form
                                                    method="POST"
                                                    action="{{ route(auth()->user()->hasRole('staff') ? 'staff.bookings.billing.lines.destroy' : 'admin.bookings.billing.lines.destroy', ['booking' => $booking->id, 'lineItem' => $item->id]) }}"
                                                    onsubmit="return confirm('Are you sure you want to remove this line item?');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        class="p-1 rounded-lg hover:bg-red-500/10 text-gray-500 hover:text-red-600 transition"
                                                        title="Remove Line"
                                                    >
                                                        <x-icon name="trash" class="w-4 h-4" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        @endif
                    @endforeach

                    @if (! $hasAnyItems)
                        <tr>
                            <td colspan="{{ ($editable && ! $summary->isFinalized) ? 5 : 4 }}" class="py-8 text-center text-gray-500">
                                No line items added yet.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- Subtotals & Totals Summary Footer --}}
        <div class="border-t border-gray-200 dark:border-white/10 bg-gray-50/70 dark:bg-white/[0.02] p-6">
            <div class="max-w-md ml-auto space-y-2 text-sm">
                @if ($summary->servicesSubtotal > 0)
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Services Subtotal</span>
                        <span class="font-mono text-gray-900 dark:text-white">₱{{ number_format($summary->servicesSubtotal, 2) }}</span>
                    </div>
                @endif

                @if ($summary->productsSubtotal > 0)
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Parts & Materials Subtotal</span>
                        <span class="font-mono text-gray-900 dark:text-white">₱{{ number_format($summary->productsSubtotal, 2) }}</span>
                    </div>
                @endif

                @if ($summary->laborSubtotal > 0)
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Labor Subtotal</span>
                        <span class="font-mono text-gray-900 dark:text-white">₱{{ number_format($summary->laborSubtotal, 2) }}</span>
                    </div>
                @endif

                @if ($summary->feesSubtotal > 0)
                    <div class="flex justify-between text-gray-600 dark:text-gray-400">
                        <span>Fees & Surcharges</span>
                        <span class="font-mono text-gray-900 dark:text-white">₱{{ number_format($summary->feesSubtotal, 2) }}</span>
                    </div>
                @endif

                @if ($summary->discountsTotal > 0)
                    <div class="flex justify-between text-red-600 font-medium">
                        <span>Total Discounts Applied</span>
                        <span class="font-mono">-₱{{ number_format($summary->discountsTotal, 2) }}</span>
                    </div>
                @endif

                <div class="pt-3 border-t border-gray-200 dark:border-white/10 flex justify-between items-baseline">
                    <span class="text-base font-bold text-gray-900 dark:text-white">Final Total Charges</span>
                    <span class="text-2xl font-bold font-mono text-[#E63946]">₱{{ number_format($summary->finalTotal, 2) }}</span>
                </div>
            </div>
        </div>
    </x-card>

    @if ($showPayments)
        {{-- Payments Applied & Balance Breakdown --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Payments Applied List --}}
            <div class="lg:col-span-2">
                <x-card class="h-full">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white">Payments Applied</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">All verified customer payments credited toward this bill.</p>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-green-500/10 text-green-600 dark:text-green-400 border border-green-500/20">
                            {{ $summary->paymentsBreakdown->count() }} {{ Str::plural('Payment', $summary->paymentsBreakdown->count()) }} Verified
                        </span>
                    </div>

                    @if ($summary->paymentsBreakdown->isNotEmpty())
                        <div class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($summary->paymentsBreakdown as $payment)
                                <div class="py-3 flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-green-500/10 text-green-600 flex items-center justify-center flex-shrink-0">
                                            <x-icon name="check" class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white capitalize">
                                                {{ str_replace('_', ' ', $payment->type) }}
                                                <span class="text-xs font-normal text-gray-500 uppercase">({{ $payment->method }})</span>
                                                @if ($payment->type === \App\Models\Payment::TYPE_RESERVATION_FEE)
                                                    <span class="ml-1 text-[11px] font-normal px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20">Slot Booking Fee (Separate)</span>
                                                @endif
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                Ref: <span class="font-mono text-gray-700 dark:text-gray-300">{{ $payment->reference_number ?? 'N/A' }}</span>
                                                @if ($payment->paid_at)
                                                    • {{ $payment->paid_at->format('M d, Y h:i A') }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <span class="font-mono font-bold text-green-600 dark:text-green-400 text-base">
                                        {{ $payment->formatted_amount }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-6 text-center text-gray-500 text-sm">
                            No verified payments on record yet.
                        </div>
                    @endif
                </x-card>
            </div>

            {{-- Remaining Balance Widget --}}
            <div class="lg:col-span-1">
                <x-card class="h-full flex flex-col justify-between {{ $summary->balanceDue <= 0 ? 'bg-gradient-to-br from-green-500/10 to-green-600/5 border-green-500/30' : 'bg-gradient-to-br from-[#E63946]/10 to-transparent border-[#E63946]/30' }}">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider {{ $summary->balanceDue <= 0 ? 'text-green-600 dark:text-green-400' : 'text-[#E63946]' }}">
                            Billing Settlement Status
                        </p>

                        <div class="mt-4 space-y-3">
                            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                                <span>Final Total</span>
                                <span class="font-mono font-semibold text-gray-900 dark:text-white">₱{{ number_format($summary->finalTotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-green-600">
                                <span>Bill Payments Credited</span>
                                <span class="font-mono font-semibold">₱{{ number_format($summary->totalPaid, 2) }}</span>
                            </div>
                            <div class="pt-3 border-t border-gray-200 dark:border-white/10">
                                <p class="text-xs text-gray-500">Remaining Balance Due</p>
                                <p class="text-3xl font-bold font-mono mt-1 {{ $summary->balanceDue <= 0 ? 'text-green-600 dark:text-green-400' : 'text-[#E63946]' }}">
                                    ₱{{ number_format($summary->balanceDue, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-200/50 dark:border-white/10">
                        @if ($summary->balanceDue <= 0)
                            <div class="flex items-center gap-2 text-green-600 dark:text-green-400 text-xs font-semibold">
                                <x-icon name="check-circle" class="w-4 h-4" />
                                <span>Account fully settled</span>
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 text-xs font-semibold">
                                <x-icon name="info" class="w-4 h-4" />
                                <span>Outstanding balance pending payment</span>
                            </div>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>
    @endif
</div>
