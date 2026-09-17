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
    $products = $products ?? \App\Models\Product::query()->active()->orderBy('name')->get(['id', 'sku', 'name', 'category', 'unit_price', 'unit_label', 'stock_quantity']);
@endphp

<div
    x-data="billingManager({
        searchUrl: '{{ $productSearchUrl }}',
        bookingId: {{ $booking->id }},
        grossSubtotal: {{ (float) ($summary->servicesSubtotal + $summary->productsSubtotal + $summary->laborSubtotal + $summary->feesSubtotal) }},
        products: {{ Js::from($products) }}
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

    {{-- Operating Instructions Banner --}}
    <x-card class="bg-gradient-to-r from-blue-500/10 via-gray-500/5 to-transparent border-blue-500/20">
        <div x-data="{ openInstructions: true }">
            <div class="flex items-center justify-between cursor-pointer" @click="openInstructions = !openInstructions">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-[#457B9D]/10 text-[#457B9D] flex items-center justify-center flex-shrink-0">
                        <x-icon name="info" class="w-5 h-5" />
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-sm">How to Operate Billing for this Booking</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Step-by-step guide for line items, reservation fee deduction, recording payments, and locking bills.</p>
                    </div>
                </div>
                <button type="button" class="text-[#457B9D] font-semibold text-xs flex items-center gap-1">
                    <span x-text="openInstructions ? 'Hide Instructions' : 'View Instructions'"></span>
                    <x-icon name="chevron-down" class="w-4 h-4 transform transition-transform" ::class="openInstructions ? 'rotate-180' : ''" />
                </button>
            </div>

            <div x-show="openInstructions" x-collapse class="mt-4 pt-4 border-t border-gray-200 dark:border-white/10 grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                <div class="p-3 bg-white dark:bg-[#1A1A1A] rounded-xl border border-gray-200 dark:border-white/10 space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-[#E63946]">
                        <span class="w-5 h-5 rounded-full bg-[#E63946] text-white flex items-center justify-center text-[10px]">1</span>
                        <span>Add Line Items</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">Use the form below to add parts from inventory catalog, labor, or extra services. Set quantity and unit price.</p>
                </div>

                <div class="p-3 bg-white dark:bg-[#1A1A1A] rounded-xl border border-gray-200 dark:border-white/10 space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-blue-500">
                        <span class="w-5 h-5 rounded-full bg-blue-500 text-white flex items-center justify-center text-[10px]">2</span>
                        <span>Booking Fee Already Settled</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">The customer's <strong>₱200 Reservation Fee</strong> is already collected and is <em>not</em> included in Total Charges. It shows as <strong>"Already Settled"</strong> in the balance widget.</p>
                </div>

                <div class="p-3 bg-white dark:bg-[#1A1A1A] rounded-xl border border-gray-200 dark:border-white/10 space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-green-600">
                        <span class="w-5 h-5 rounded-full bg-green-600 text-white flex items-center justify-center text-[10px]">3</span>
                        <span>Record Payments</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">Click <strong>Record Payment</strong> to log cash, GCash, or bank transfers received on-site. Net Balance Due updates live.</p>
                </div>

                <div class="p-3 bg-white dark:bg-[#1A1A1A] rounded-xl border border-gray-200 dark:border-white/10 space-y-1">
                    <div class="flex items-center gap-1.5 font-bold text-purple-600">
                        <span class="w-5 h-5 rounded-full bg-purple-600 text-white flex items-center justify-center text-[10px]">4</span>
                        <span>Finalize & Lock Bill</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">Click <strong>Finalize & Lock Bill</strong> when work is complete to issue the locked invoice and notify customer.</p>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Add Line Item Card (Only if editable) --}}
    @if ($canManage && ! $summary->isFinalized)
        {{-- relative z-10 ensures this card's stacking context (with its absolute dropdown) renders above the breakdown card below --}}
        <div class="relative z-10" style="overflow: visible;">
        <x-card style="overflow: visible;">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">Add Billing Line Item</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Add parts, labor, additional services, discounts, or miscellaneous fees to this bill.</p>
                </div>
            </div>

            <form method="POST" action="{{ $storeLineRoute }}" class="space-y-4">
                @csrf
                <input type="hidden" name="notes" :value="form.notes">

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

                    {{-- Description / Product Dropdown / Text Input / Discount Dropdown --}}
                    <div class="lg:col-span-2 relative">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            <span x-text="form.item_type === 'discount' ? 'Discount Name / Description' : (form.item_type === 'labor' ? 'Labor Task Description' : ((form.item_type === 'product' || form.item_type === 'material') ? 'Select Part / Product from Inventory' : 'Description / Item Name'))"></span>
                        </label>

                        {{-- 1. Searchable Product / Material Dropdown Combobox (When item_type is 'product' or 'material') --}}
                        <div
                            x-show="form.item_type === 'product' || form.item_type === 'material'"
                            class="relative"
                            @click.away="isProductDropdownOpen = false"
                        >
                            {{-- Combobox Input --}}
                            <div class="relative">
                                <input
                                    type="text"
                                    x-model="productSearchQuery"
                                    @focus="isProductDropdownOpen = true; if (form.product_id) $event.target.select();"
                                    @click="isProductDropdownOpen = true"
                                    @input="isProductDropdownOpen = true; if (form.product_id && productSearchQuery !== (productsMap[form.product_id]?.name || '')) { form.product_id = ''; form.sku = null; form.description = ''; }"
                                    @keydown.escape="isProductDropdownOpen = false"
                                    @keydown.enter.prevent="if (isProductDropdownOpen && filteredProducts.length > 0) { selectProduct(filteredProducts[0]); }"
                                    placeholder="Type to search or select parts from inventory..."
                                    class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-white pl-9 pr-9 py-2 text-sm focus:ring-2 focus:ring-[#E63946] focus:outline-none placeholder-gray-400"
                                    :class="form.product_id ? 'border-green-500/50 dark:border-green-500/50' : ''"
                                >
                                {{-- Search Icon --}}
                                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                    <x-icon name="search" class="w-4 h-4" />
                                </div>

                                {{-- Clear Button --}}
                                <button
                                    type="button"
                                    x-show="form.product_id || productSearchQuery"
                                    @click="clearProductSelection()"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition"
                                    title="Clear selection"
                                >
                                    <x-icon name="x" class="w-3.5 h-3.5" />
                                </button>
                            </div>

                            {{-- Dropdown Menu --}}
                            <div
                                x-show="isProductDropdownOpen"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="absolute z-50 left-0 right-0 mt-1 bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-xl shadow-2xl"
                                style="display: none; max-height: 16rem; overflow-y: auto;"
                            >
                                {{-- No results message --}}
                                <div x-show="filteredProducts.length === 0" class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400 text-center">
                                    No inventory items matching "<span class="font-medium text-gray-700 dark:text-gray-300" x-text="productSearchQuery"></span>"
                                </div>

                                {{-- Grouped items --}}
                                <template x-for="(items, categoryName) in filteredProductsGrouped" :key="categoryName">
                                    <div>
                                        <div class="sticky top-0 z-10 px-3 py-1.5 bg-gray-100/95 dark:bg-[#222222]/95 backdrop-blur-sm border-b border-gray-200/60 dark:border-white/5 text-[11px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 flex items-center justify-between">
                                            <span x-text="categoryName"></span>
                                            <span class="font-mono font-normal text-[10px] text-gray-400" x-text="items.length + (items.length === 1 ? ' item' : ' items')"></span>
                                        </div>

                                        <div class="divide-y divide-gray-100 dark:divide-white/5">
                                            <template x-for="p in items" :key="p.id">
                                                <button
                                                    type="button"
                                                    @click="p.stock_quantity > 0 ? selectProduct(p) : null"
                                                    :disabled="p.stock_quantity <= 0"
                                                    class="w-full text-left px-3.5 py-2.5 flex items-center justify-between gap-3 text-sm transition"
                                                    :class="{
                                                        'bg-red-50/70 dark:bg-red-500/10': form.product_id == p.id,
                                                        'opacity-45 cursor-not-allowed bg-gray-50/60 dark:bg-white/[0.02]': p.stock_quantity <= 0,
                                                        'hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer': p.stock_quantity > 0
                                                    }"
                                                >
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-2">
                                                            <p class="font-medium truncate" :class="p.stock_quantity <= 0 ? 'text-gray-400 dark:text-gray-500 line-through' : 'text-gray-900 dark:text-white'" x-text="p.name"></p>
                                                            <span x-show="form.product_id == p.id" class="text-green-600 dark:text-green-400 flex-shrink-0">
                                                                <x-icon name="check-circle" class="w-4 h-4" />
                                                            </span>
                                                            <span x-show="p.stock_quantity <= 0" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 uppercase tracking-wide flex-shrink-0">
                                                                Out of Stock
                                                            </span>
                                                        </div>
                                                        <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                            <span class="font-mono text-gray-600 dark:text-gray-300 font-medium" x-text="p.sku || 'No SKU'"></span>
                                                            <span>•</span>
                                                            <span
                                                                :class="p.stock_quantity <= 0 ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-gray-400'"
                                                                x-text="p.stock_quantity > 0 ? (p.stock_quantity + ' ' + (p.unit_label || 'units') + ' in stock') : '0 in stock'"
                                                            ></span>
                                                        </div>
                                                    </div>
                                                    <div class="text-right flex-shrink-0">
                                                        <span class="font-mono font-semibold" :class="p.stock_quantity <= 0 ? 'text-gray-400 line-through' : 'text-[#E63946]'" x-text="'₱' + Number(p.unit_price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                                                    </div>
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            {{-- Selected Inventory Item Details Pill --}}
                            <div x-show="form.product_id && productsMap[form.product_id]" class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <span class="font-mono font-medium text-gray-700 dark:text-gray-300" x-text="'SKU: ' + (productsMap[form.product_id]?.sku || 'N/A')"></span>
                                <span>•</span>
                                <span x-text="'Category: ' + (productsMap[form.product_id]?.category || 'General')"></span>
                                <span>•</span>
                                <span
                                    :class="(productsMap[form.product_id]?.stock_quantity || 0) <= 0 ? 'text-red-500 font-semibold' : 'text-green-600 dark:text-green-400 font-medium'"
                                    x-text="(productsMap[form.product_id]?.stock_quantity || 0) > 0 ? ((productsMap[form.product_id]?.stock_quantity) + ' ' + (productsMap[form.product_id]?.unit_label || 'units') + ' available') : 'Currently Out of Stock'"
                                ></span>
                            </div>
                        </div>

                        {{-- 2. Standard Text Input (When item_type is labor, service, additional_service, fee, etc.) --}}
                        <div x-show="form.item_type !== 'product' && form.item_type !== 'material' && form.item_type !== 'discount'">
                            <input
                                type="text"
                                x-model="form.description"
                                :required="form.item_type !== 'product' && form.item_type !== 'material' && form.item_type !== 'discount'"
                                :placeholder="form.item_type === 'labor' ? 'e.g., Surface Prep & Sanding, Body Panel Alignment' : 'Enter description or item name...'"
                                class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                            >
                        </div>

                        {{-- Discount Preset Dropdown & Custom Text --}}
                        <div x-show="form.item_type === 'discount'" class="space-y-2">
                            <select
                                x-model="form.discount_preset"
                                @change="updateDiscountDescription()"
                                class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                            >
                                <option value="Promotional / Seasonal Discount">Promotional / Seasonal Discount</option>
                                <option value="Custom / Loyalty Discount">Custom / Loyalty Discount</option>
                            </select>

                            <div x-show="form.discount_preset === 'Custom / Loyalty Discount'">
                                <input
                                    type="text"
                                    x-model="form.custom_description"
                                    @input="updateDiscountDescription()"
                                    placeholder="Enter custom discount description..."
                                    class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-1.5 text-xs focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                                >
                            </div>
                        </div>

                        {{-- Hidden inputs submitted with the form --}}
                        <input type="hidden" name="description" :value="form.description">
                        <input type="hidden" name="product_id" :value="form.product_id">
                        <input type="hidden" name="sku" :value="form.sku">
                    </div>

                    {{-- Quantity / Hours Worked / Discount Mode & Value --}}
                    <div class="lg:col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            <span x-text="form.item_type === 'labor' ? 'Hours Worked (hrs)' : (form.item_type === 'discount' ? 'Discount Value' : 'Qty')"></span>
                        </label>

                        {{-- Labor or Standard Qty --}}
                        <div x-show="form.item_type !== 'discount'">
                            <input
                                type="number"
                                name="quantity"
                                x-model.number="form.quantity"
                                :step="form.item_type === 'labor' ? '0.1' : '0.01'"
                                :min="form.item_type === 'labor' ? '0.1' : '0.01'"
                                :max="(form.item_type === 'product' || form.item_type === 'material') && form.product_id && productsMap[form.product_id] ? productsMap[form.product_id].stock_quantity : null"
                                :required="form.item_type !== 'discount'"
                                :placeholder="form.item_type === 'labor' ? '1.0' : '1'"
                                class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm text-center font-mono focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                                :class="{
                                    'border-red-500 dark:border-red-500/50 focus:ring-red-500': isExceedingStock()
                                }"
                            >
                            <div x-show="isExceedingStock()" class="text-[11px] text-red-500 font-medium mt-1 text-center" style="display: none;">
                                Exceeds available stock (<span x-text="productsMap[form.product_id]?.stock_quantity || 0"></span> max)
                            </div>
                        </div>

                        {{-- Discount Mode Selector & Value Input --}}
                        <div x-show="form.item_type === 'discount'" class="space-y-1.5">
                            <div class="flex rounded-xl overflow-hidden border border-gray-300 dark:border-white/10 bg-gray-100 dark:bg-white/5 p-0.5 text-xs">
                                <button
                                    type="button"
                                    @click="form.discount_mode = 'percent'; calculateDiscount()"
                                    class="flex-1 py-1 font-semibold rounded-lg transition"
                                    :class="form.discount_mode === 'percent' ? 'bg-[#E63946] text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                                >
                                    %
                                </button>
                                <button
                                    type="button"
                                    @click="form.discount_mode = 'fixed'; calculateDiscount()"
                                    class="flex-1 py-1 font-semibold rounded-lg transition"
                                    :class="form.discount_mode === 'fixed' ? 'bg-[#E63946] text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'"
                                >
                                    ₱
                                </button>
                            </div>

                            <input
                                type="number"
                                x-model.number="form.discount_value"
                                @input="calculateDiscount()"
                                step="0.01"
                                min="0.01"
                                :placeholder="form.discount_mode === 'percent' ? 'e.g. 5' : 'e.g. 500'"
                                class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-2.5 py-1.5 text-xs text-center font-mono focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                            >
                            <input type="hidden" name="quantity" value="1">
                        </div>
                    </div>

                    {{-- Unit Price / Rate per hour / Calculated Deductible --}}
                    <div class="lg:col-span-1">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            <span x-text="form.item_type === 'labor' ? 'Rate / Hour (₱)' : (form.item_type === 'discount' ? 'Deductible (₱)' : 'Unit Price (₱)')"></span>
                        </label>

                        {{-- Standard or Labor Unit Price --}}
                        <div x-show="form.item_type !== 'discount'">
                            <input
                                type="number"
                                name="unit_final"
                                x-model.number="form.unit_final"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                :required="form.item_type !== 'discount'"
                                class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm text-right font-mono focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                            >
                        </div>

                        {{-- Discount Deductible Box --}}
                        <div x-show="form.item_type === 'discount'">
                            <div class="w-full rounded-xl border border-red-300 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 px-3 py-2 text-sm text-right font-mono font-bold flex items-center justify-between min-h-[38px]">
                                <span class="text-[10px] text-red-400 font-normal">Deduction</span>
                                <span x-text="'-₱' + Number(form.unit_final || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                            </div>
                            <input type="hidden" name="unit_final" :value="form.unit_final">
                        </div>
                    </div>

                    {{-- Add Button --}}
                    <div class="lg:col-span-1 flex items-end">
                        <x-button type="submit" variant="primary" class="w-full" x-bind:disabled="isAddDisabled()">
                            <x-icon name="plus" class="w-4 h-4 mr-1.5" />
                            Add Line
                        </x-button>
                    </div>
                </div>
            </form>
        </x-card>
        </div>
    @endif

    {{-- Main Price Breakdown Component --}}
    <x-booking-price-breakdown
        :summary="$summary"
        :editable="$canManage"
        :booking="$booking"
    />

    {{-- Bottom Action Buttons --}}
    @if ($canRecordPayment || ($canFinalize && ! $summary->isFinalized))
        <x-card class="bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 shadow-lg">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#E63946]/10 text-[#E63946] flex items-center justify-center flex-shrink-0">
                        <x-icon name="shield-check" class="w-5 h-5" />
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">Billing Settlement & Actions</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @if (! $summary->isFinalized)
                                Complete itemization above, then record payments or lock the statement.
                            @else
                                Bill is finalized and locked. You may record additional payments.
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto justify-end">
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
        </x-card>
    @endif

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
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        <span x-text="editForm.item_type === 'labor' ? 'Labor Task Description' : (editForm.item_type === 'discount' ? 'Discount Name / Description' : 'Description')"></span>
                    </label>
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
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            <span x-text="editForm.item_type === 'labor' ? 'Hours Worked (hrs)' : (editForm.item_type === 'discount' ? 'Qty / Multiplier' : 'Quantity')"></span>
                        </label>
                        <input
                            type="number"
                            name="quantity"
                            x-model.number="editForm.quantity"
                            :step="editForm.item_type === 'labor' ? '0.1' : '0.01'"
                            min="0.01"
                            required
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2 text-sm font-mono text-center"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            <span x-text="editForm.item_type === 'labor' ? 'Rate / Hour (₱)' : (editForm.item_type === 'discount' ? 'Discount Amount (₱)' : 'Unit Price (₱)')"></span>
                        </label>
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
        grossSubtotal: config.grossSubtotal || 0,
        products: config.products || [],
        productsMap: {},
        showFinalizeModal: false,
        showPaymentModal: false,
        showEditModal: false,
        paymentMethod: 'cash',
        productSearchQuery: '',
        isProductDropdownOpen: false,
        form: {
            item_type: 'product',
            description: '',
            discount_preset: 'Promotional / Seasonal Discount',
            custom_description: '',
            discount_mode: 'percent',
            discount_value: 5,
            product_id: '',
            sku: null,
            quantity: 1,
            unit_final: null,
            notes: '',
        },
        editForm: {
            action: '',
            id: null,
            item_type: 'product',
            description: '',
            quantity: 1,
            unit_final: null,
            notes: ''
        },

        get filteredProducts() {
            const raw = (this.productSearchQuery || '').trim();
            if (this.form.product_id && raw === (this.productsMap[this.form.product_id]?.name || '')) {
                return this.products || [];
            }
            const q = raw.toLowerCase();
            if (!q) {
                return this.products || [];
            }
            return (this.products || []).filter(p => {
                const name = (p.name || '').toLowerCase();
                const sku = (p.sku || '').toLowerCase();
                const cat = (p.category || '').toLowerCase();
                return name.includes(q) || sku.includes(q) || cat.includes(q);
            });
        },

        get filteredProductsGrouped() {
            const list = this.filteredProducts;
            const groups = {};
            for (const p of list) {
                const cat = p.category || 'Other Parts & Supplies';
                if (!groups[cat]) {
                    groups[cat] = [];
                }
                groups[cat].push(p);
            }
            return groups;
        },

        init() {
            (this.products || []).forEach(p => {
                this.productsMap[p.id] = p;
            });

            window.addEventListener('edit-line', (e) => {
                const data = e.detail;
                const isStaff = '{{ $isStaff ? "1" : "0" }}' === '1';
                const base = isStaff ? `/staff/bookings/${this.bookingId}/billing/lines/` : `/admin/bookings/${this.bookingId}/billing/lines/`;
                this.editForm.action = base + data.id;
                this.editForm.id = data.id;
                this.editForm.item_type = data.item_type || 'product';
                this.editForm.description = data.description;
                this.editForm.quantity = data.quantity;
                this.editForm.unit_final = data.unit_final;
                this.editForm.notes = data.notes;
                this.showEditModal = true;
            });
        },

        selectProduct(p) {
            if (!p || p.stock_quantity <= 0) {
                return;
            }
            this.form.product_id = p.id;
            this.form.sku = p.sku;
            this.form.description = p.name;
            this.productSearchQuery = p.name;
            this.form.unit_final = parseFloat(p.unit_price);
            this.isProductDropdownOpen = false;
        },

        isAddDisabled() {
            if (this.form.item_type === 'product' || this.form.item_type === 'material') {
                if (!this.form.product_id) return true;
                const p = this.productsMap[this.form.product_id];
                if (!p) return true;
                if (p.stock_quantity <= 0) return true;
                const qty = parseFloat(this.form.quantity);
                if (isNaN(qty) || qty <= 0 || qty > p.stock_quantity) return true;
            }
            return false;
        },

        isExceedingStock() {
            if ((this.form.item_type === 'product' || this.form.item_type === 'material') && this.form.product_id) {
                const p = this.productsMap[this.form.product_id];
                if (p && p.stock_quantity > 0) {
                    const qty = parseFloat(this.form.quantity);
                    return !isNaN(qty) && qty > p.stock_quantity;
                }
            }
            return false;
        },

        clearProductSelection() {
            this.form.product_id = '';
            this.form.sku = null;
            this.form.description = '';
            this.productSearchQuery = '';
            this.form.unit_final = null;
            this.isProductDropdownOpen = true;
        },

        handleTypeChange() {
            this.form.product_id = '';
            this.form.sku = null;
            this.form.notes = '';
            this.productSearchQuery = '';
            this.isProductDropdownOpen = false;

            if (this.form.item_type === 'labor') {
                this.form.quantity = 1;
                this.form.unit_final = null;
                this.form.description = '';
            } else if (this.form.item_type === 'discount') {
                this.form.discount_preset = 'Promotional / Seasonal Discount';
                this.form.discount_mode = 'percent';
                this.form.discount_value = 5;
                this.form.quantity = 1;
                this.updateDiscountDescription();
                this.calculateDiscount();
            } else if (this.form.item_type === 'product' || this.form.item_type === 'material') {
                this.form.description = '';
                this.form.unit_final = null;
                this.form.quantity = 1;
            } else {
                this.form.quantity = 1;
                this.form.unit_final = null;
                this.form.description = '';
            }
        },

        updateDiscountDescription() {
            if (this.form.discount_preset === 'Custom / Loyalty Discount') {
                this.form.description = this.form.custom_description || this.form.discount_preset;
            } else {
                this.form.description = this.form.discount_preset;
            }
        },

        calculateDiscount() {
            if (this.form.item_type !== 'discount') return;

            let val = parseFloat(this.form.discount_value) || 0;
            let deductible = 0;

            if (this.form.discount_mode === 'percent') {
                deductible = Math.round((this.grossSubtotal * (val / 100)) * 100) / 100;
                this.form.notes = `-${val}%`;
            } else {
                deductible = Math.round(val * 100) / 100;
                this.form.notes = 'Fixed Discount';
            }

            this.form.unit_final = deductible;
            this.form.quantity = 1;
        }
    };
}
</script>
@endsection
