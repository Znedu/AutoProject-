@extends('layouts.dashboard')

@section('title', 'Final Billing Breakdown #' . $booking->booking_number . ' | AutoProject+')

@section('content')
@php
    $canManage = Gate::allows('billing.manage', $booking);
    $canFinalize = Gate::allows('billing.finalize', $booking);
    $canRecordPayment = Gate::allows('billing.record-payment', $booking);
    $isStaff = auth()->user()->hasRole('staff');
    $storeLineRoute = $isStaff ? route('staff.bookings.billing.lines.store', $booking) : route('admin.bookings.billing.lines.store', $booking);
    $finalizeRoute = route('admin.bookings.billing.finalize', $booking);
    $recordPaymentRoute = $isStaff ? route('staff.bookings.billing.payments.store', $booking) : route('admin.bookings.billing.payments.store', $booking);
    $productSearchUrl = $isStaff ? route('staff.products.search') : route('admin.products.search');
@endphp

<div
    x-data="billingManager({
        searchUrl: '{{ $productSearchUrl }}',
        bookingId: {{ $booking->id }}
    })"
    class="space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ $isStaff ? route('staff.booking-queue') : route('admin.approvals.index') }}" class="text-gray-500 hover:text-gray-900 dark:hover:text-white transition">
                    <x-icon name="chevron-left" class="w-5 h-5" />
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Final Billing Breakdown
                </h1>
                <span class="font-mono text-sm font-semibold px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300">
                    #{{ $booking->booking_number }}
                </span>
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                Customer: <span class="font-semibold text-gray-900 dark:text-white">{{ $booking->customer_name }}</span> ({{ $booking->contact_number }})
                • Vehicle: <span class="font-semibold text-gray-900 dark:text-white">{{ $booking->vehicle?->make }} {{ $booking->vehicle?->model }} ({{ $booking->vehicle?->plate_number }})</span>
            </p>
        </div>

        {{-- Top Actions --}}
        <div class="flex flex-wrap gap-3">
            @if ($canRecordPayment)
                <x-button variant="secondary" size="md" @click="showPaymentModal = true">
                    <x-icon name="credit-card" class="w-4 h-4 mr-2" />
                    Record Payment
                </x-button>
            @endif

            @if ($canFinalize && ! $summary->isFinalized)
                <x-button variant="accent" size="md" @click="showFinalizeModal = true">
                    <x-icon name="check-square" class="w-4 h-4 mr-2" />
                    Finalize & Lock Bill
                </x-button>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-5 py-4 bg-green-500/10 border border-green-500/20 rounded-xl text-green-600 dark:text-green-400">
            <x-icon name="check-circle" class="w-5 h-5 flex-shrink-0" />
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 px-5 py-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-600 dark:text-red-400">
            <x-icon name="info" class="w-5 h-5 flex-shrink-0" />
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
    @endif
    @if ($errors->any())
        <div class="px-5 py-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-600 dark:text-red-400">
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Add Line Item Card (Only if editable) --}}
    @if ($canManage && ! $summary->isFinalized)
        <x-card>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Add Billing Line Item</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Add parts, labor, additional services, discounts, or miscellaneous fees to this bill.</p>
                </div>
            </div>

            <form method="POST" action="{{ $storeLineRoute }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
                    {{-- Item Type --}}
                    <div class="lg:col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Type</label>
                        <select
                            name="item_type"
                            x-model="form.item_type"
                            @change="handleTypeChange()"
                            required
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                        >
                            <option value="service">Service</option>
                            <option value="additional_service">Additional Service</option>
                            <option value="product">Part / Product</option>
                            <option value="material">Shop Material</option>
                            <option value="labor">Labor</option>
                            <option value="fee">Fee / Surcharge</option>
                            <option value="discount">Discount</option>
                        </select>
                    </div>

                    {{-- Description / Product Search --}}
                    <div class="lg:col-span-2 relative">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Description / Item Name</label>
                        <input
                            type="text"
                            name="description"
                            x-model="form.description"
                            @input.debounce.300ms="searchProducts()"
                            @focus="showSearchResults = (form.item_type === 'product' || form.item_type === 'material') && searchResults.length > 0"
                            required
                            placeholder="Enter description or search parts catalog..."
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                        >
                        <input type="hidden" name="product_id" :value="form.product_id">
                        <input type="hidden" name="sku" :value="form.sku">

                        {{-- Autocomplete Dropdown --}}
                        <div
                            x-show="showSearchResults && searchResults.length > 0"
                            @click.away="showSearchResults = false"
                            class="absolute z-20 left-0 right-0 mt-1 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-xl shadow-xl max-h-48 overflow-y-auto"
                            style="display: none;"
                        >
                            <template x-for="p in searchResults" :key="p.id">
                                <button
                                    type="button"
                                    @click="selectProduct(p)"
                                    class="w-full text-left px-4 py-2.5 hover:bg-gray-100 dark:hover:bg-white/10 flex justify-between items-center text-sm border-b border-gray-100 dark:border-white/5 last:border-0"
                                >
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white" x-text="p.name"></p>
                                        <p class="text-xs text-gray-500 font-mono" x-text="p.sku || 'No SKU'"></p>
                                    </div>
                                    <span class="font-mono font-semibold text-[#E63946]" x-text="'₱' + Number(p.unit_price).toFixed(2)"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Quantity --}}
                    <div class="lg:col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Qty</label>
                        <input
                            type="number"
                            name="quantity"
                            x-model.number="form.quantity"
                            step="0.01"
                            min="0.01"
                            required
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm text-center font-mono focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                        >
                    </div>

                    {{-- Unit Price --}}
                    <div class="lg:col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            <span x-text="form.item_type === 'discount' ? 'Discount Amount' : 'Unit Price (₱)'"></span>
                        </label>
                        <input
                            type="number"
                            name="unit_final"
                            x-model.number="form.unit_final"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            required
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm text-right font-mono focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                        >
                    </div>

                    {{-- Add Button --}}
                    <div class="lg:col-span-1 flex items-end">
                        <x-button type="submit" variant="primary" class="w-full">
                            <x-icon name="plus" class="w-4 h-4 mr-1.5" />
                            Add Line
                        </x-button>
                    </div>
                </div>
            </form>
        </x-card>
    @endif

    {{-- Main Price Breakdown Component --}}
    <x-booking-price-breakdown
        :summary="$summary"
        :editable="$canManage"
        :booking="$booking"
    />

    {{-- Edit Line Item Modal --}}
    <div
        x-show="showEditModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        style="display: none;"
    >
        <div class="bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-2xl p-6 max-w-lg w-full shadow-2xl space-y-4" @click.away="showEditModal = false">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-white/10">
                <h3 class="font-bold text-gray-900 dark:text-white">Edit Line Item</h3>
                <button type="button" @click="showEditModal = false" class="text-gray-500 hover:text-gray-900 dark:hover:text-white">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <form method="POST" :action="editForm.action" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <input
                        type="text"
                        name="description"
                        x-model="editForm.description"
                        required
                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm"
                    >
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                        <input
                            type="number"
                            name="quantity"
                            x-model.number="editForm.quantity"
                            step="0.01"
                            min="0.01"
                            required
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm font-mono text-center"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Unit Price (₱)</label>
                        <input
                            type="number"
                            name="unit_final"
                            x-model.number="editForm.unit_final"
                            step="0.01"
                            min="0"
                            required
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm font-mono text-right"
                        >
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes / Remarks</label>
                    <input
                        type="text"
                        name="notes"
                        x-model="editForm.notes"
                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm"
                    >
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <x-button type="button" variant="secondary" @click="showEditModal = false">Cancel</x-button>
                    <x-button type="submit" variant="primary">Save Changes</x-button>
                </div>
            </form>
        </div>
    </div>

    {{-- Finalize Modal --}}
    @if ($canFinalize)
        <div
            x-show="showFinalizeModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            style="display: none;"
        >
            <div class="bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4" @click.away="showFinalizeModal = false">
                <div class="flex items-center gap-3 text-[#E63946]">
                    <div class="w-10 h-10 rounded-full bg-[#E63946]/10 flex items-center justify-center flex-shrink-0">
                        <x-icon name="check-square" class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Finalize & Lock Billing</h3>
                        <p class="text-xs text-gray-500">Lock the itemized charges and generate customer bill.</p>
                    </div>
                </div>

                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Are you sure you want to finalize this billing statement? Once finalized, line items will be locked and an official statement will be generated with a total of <span class="font-mono font-bold text-gray-900 dark:text-white">₱{{ number_format($summary->finalTotal, 2) }}</span>.
                </p>

                <form method="POST" action="{{ $finalizeRoute }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Final Billing Notes (Optional)</label>
                        <textarea
                            name="notes"
                            rows="2"
                            placeholder="Add any billing or warranty notes..."
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white p-3 text-sm"
                        ></textarea>
                    </div>

                    <div class="flex justify-end gap-3">
                        <x-button type="button" variant="secondary" @click="showFinalizeModal = false">Cancel</x-button>
                        <x-button type="submit" variant="accent" class="text-white">Confirm Finalization</x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Record Payment Modal --}}
    @if ($canRecordPayment)
        <div
            x-show="showPaymentModal"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            style="display: none;"
        >
            <div class="bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-4" @click.away="showPaymentModal = false">
                <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-white/10">
                    <h3 class="font-bold text-gray-900 dark:text-white">Record Customer Payment</h3>
                    <button type="button" @click="showPaymentModal = false" class="text-gray-500 hover:text-gray-900 dark:hover:text-white">
                        <x-icon name="x" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ $recordPaymentRoute }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Payment Type</label>
                        <select
                            name="type"
                            required
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm"
                        >
                            <option value="final_payment">Final Settlement / Full Payment</option>
                            <option value="deposit">Partial Deposit</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Payment Method</label>
                        <select
                            name="method"
                            x-model="paymentMethod"
                            required
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm"
                        >
                            <option value="cash">Cash (Auto-Verified In-Store)</option>
                            <option value="gcash">GCash</option>
                            <option value="maya">Maya</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Amount (₱)</label>
                        <input
                            type="number"
                            name="amount"
                            step="0.01"
                            min="0.01"
                            max="{{ $summary->isFinalized ? $summary->balanceDue : 999999 }}"
                            value="{{ $summary->balanceDue > 0 ? $summary->balanceDue : '' }}"
                            required
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm font-mono"
                        >
                    </div>

                    <div x-show="paymentMethod !== 'cash'">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Reference Number</label>
                        <input
                            type="text"
                            name="reference_number"
                            placeholder="e.g. 1029384756"
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm"
                        >
                    </div>

                    <div x-show="paymentMethod !== 'cash'">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Proof Screenshot (Optional)</label>
                        <input
                            type="file"
                            name="payment_proof"
                            accept="image/*"
                            class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-gray-100 dark:file:bg-white/10 file:text-gray-700 dark:file:text-gray-200 hover:file:bg-gray-200"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Notes (Optional)</label>
                        <input
                            type="text"
                            name="notes"
                            placeholder="Cash received at front desk..."
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm"
                        >
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <x-button type="button" variant="secondary" @click="showPaymentModal = false">Cancel</x-button>
                        <x-button type="submit" variant="primary">Record Payment</x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
function billingManager(config) {
    return {
        searchUrl: config.searchUrl,
        bookingId: config.bookingId,
        showFinalizeModal: false,
        showPaymentModal: false,
        showEditModal: false,
        paymentMethod: 'cash',
        showSearchResults: false,
        searchResults: [],
        form: {
            item_type: 'product',
            description: '',
            product_id: null,
            sku: null,
            quantity: 1,
            unit_final: null,
        },
        editForm: {
            action: '',
            id: null,
            description: '',
            quantity: 1,
            unit_final: null,
            notes: ''
        },

        init() {
            window.addEventListener('edit-line', (e) => {
                const data = e.detail;
                const isStaff = '{{ $isStaff ? "1" : "0" }}' === '1';
                const base = isStaff ? `/staff/bookings/${this.bookingId}/billing/lines/` : `/admin/bookings/${this.bookingId}/billing/lines/`;
                this.editForm.action = base + data.id;
                this.editForm.id = data.id;
                this.editForm.description = data.description;
                this.editForm.quantity = data.quantity;
                this.editForm.unit_final = data.unit_final;
                this.editForm.notes = data.notes;
                this.showEditModal = true;
            });
        },

        handleTypeChange() {
            this.form.product_id = null;
            this.form.sku = null;
            if (this.form.item_type === 'product' || this.form.item_type === 'material') {
                this.searchProducts();
            } else {
                this.searchResults = [];
                this.showSearchResults = false;
            }
        },

        async searchProducts() {
            if (this.form.item_type !== 'product' && this.form.item_type !== 'material') {
                this.searchResults = [];
                this.showSearchResults = false;
                return;
            }

            try {
                const res = await fetch(`${this.searchUrl}?category=${this.form.item_type}&q=${encodeURIComponent(this.form.description)}`);
                if (res.ok) {
                    this.searchResults = await res.json();
                    this.showSearchResults = this.searchResults.length > 0;
                }
            } catch (err) {
                console.error(err);
            }
        },

        selectProduct(p) {
            this.form.product_id = p.id;
            this.form.description = p.name;
            this.form.sku = p.sku;
            this.form.unit_final = parseFloat(p.unit_price);
            this.showSearchResults = false;
        }
    };
}
</script>
@endsection
