@extends('layouts.dashboard', ['title' => 'Inventory System & Stock Control', 'role' => auth()->user()?->roleSlug() ?? 'admin'])

@section('content')
<div x-data="inventoryApp()" class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span class="p-2 rounded-xl bg-[#E63946]/10 text-[#E63946]">
                    <x-icon name="box" class="w-6 h-6" />
                </span>
                Inventory Management & Stock Control
            </h1>
            <p class="text-sm text-gray-600 dark:text-white/60 mt-1">
                Full CRUD control, stock levels, reorder thresholds, location tracking, and stock movement logs.
            </p>
        </div>

        <button 
            @click="openAddModal()" 
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#E63946] hover:bg-[#E63946]/90 text-white font-medium text-sm transition-all shadow-lg shadow-[#E63946]/20 cursor-pointer"
        >
            <x-icon name="plus" class="w-4 h-4" />
            Add New Item
        </button>
    </div>

    <!-- Alert Banners (Highlighted out-of-stock, low-stock, expired, and expiring-soon items) -->
    @if($outOfStockItems->isNotEmpty() || $lowStockItems->isNotEmpty() || $expiredItems->isNotEmpty() || $expiringSoonItems->isNotEmpty())
        <div class="space-y-3">
            @if($expiredItems->isNotEmpty())
                <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-300 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex items-start md:items-center gap-3">
                        <span class="p-2 rounded-xl bg-red-500/20 text-red-500 shrink-0">
                            <x-icon name="alert-triangle" class="w-5 h-5" />
                        </span>
                        <div>
                            <div class="font-bold text-sm">Expired Product Alert: {{ $expiredItems->count() }} Item(s) Past Expiration Date!</div>
                            <div class="text-xs opacity-80 mt-0.5">
                                {{ $expiredItems->pluck('name')->take(3)->implode(', ') }} {{ $expiredItems->count() > 3 ? 'and ' . ($expiredItems->count() - 3) . ' more' : '' }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route(auth()->user()->roleSlug() . '.inventory.index', ['expiration_status' => 'expired']) }}" class="px-3 py-1.5 rounded-xl bg-red-500 text-white font-semibold text-xs transition-all hover:bg-red-600 shrink-0 text-center">
                        View Expired Items
                    </a>
                </div>
            @endif

            @if($expiringSoonItems->isNotEmpty())
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-300 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex items-start md:items-center gap-3">
                        <span class="p-2 rounded-xl bg-amber-500/20 text-amber-500 shrink-0">
                            <x-icon name="alert-triangle" class="w-5 h-5" />
                        </span>
                        <div>
                            <div class="font-bold text-sm">Expiration Warning: {{ $expiringSoonItems->count() }} Item(s) Expiring Within 30 Days</div>
                            <div class="text-xs opacity-80 mt-0.5">
                                {{ $expiringSoonItems->pluck('name')->take(3)->implode(', ') }} {{ $expiringSoonItems->count() > 3 ? 'and ' . ($expiringSoonItems->count() - 3) . ' more' : '' }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route(auth()->user()->roleSlug() . '.inventory.index', ['expiration_status' => 'expiring_soon']) }}" class="px-3 py-1.5 rounded-xl bg-amber-500 text-white font-semibold text-xs transition-all hover:bg-amber-600 shrink-0 text-center">
                        View Expiring Soon List
                    </a>
                </div>
            @endif

            @if($outOfStockItems->isNotEmpty())
                <div class="p-4 rounded-2xl bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-300 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex items-start md:items-center gap-3">
                        <span class="p-2 rounded-xl bg-red-500/20 text-red-500 shrink-0">
                            <x-icon name="alert-triangle" class="w-5 h-5" />
                        </span>
                        <div>
                            <div class="font-bold text-sm">Critical Stock Alert: {{ $outOfStockItems->count() }} Item(s) Out of Stock!</div>
                            <div class="text-xs opacity-80 mt-0.5">
                                {{ $outOfStockItems->pluck('name')->take(3)->implode(', ') }} {{ $outOfStockItems->count() > 3 ? 'and ' . ($outOfStockItems->count() - 3) . ' more' : '' }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route(auth()->user()->roleSlug() . '.inventory.index', ['stock_status' => 'out_of_stock']) }}" class="px-3 py-1.5 rounded-xl bg-red-500 text-white font-semibold text-xs transition-all hover:bg-red-600 shrink-0 text-center">
                        View Out of Stock Items
                    </a>
                </div>
            @endif

            @if($lowStockItems->isNotEmpty())
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-800 dark:text-amber-300 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="flex items-start md:items-center gap-3">
                        <span class="p-2 rounded-xl bg-amber-500/20 text-amber-500 shrink-0">
                            <x-icon name="alert-triangle" class="w-5 h-5" />
                        </span>
                        <div>
                            <div class="font-bold text-sm">Reorder Warning: {{ $lowStockItems->count() }} Item(s) Below Minimum Threshold</div>
                            <div class="text-xs opacity-80 mt-0.5">
                                {{ $lowStockItems->pluck('name')->take(3)->implode(', ') }} {{ $lowStockItems->count() > 3 ? 'and ' . ($lowStockItems->count() - 3) . ' more' : '' }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route(auth()->user()->roleSlug() . '.inventory.index', ['stock_status' => 'low_stock']) }}" class="px-3 py-1.5 rounded-xl bg-amber-500 text-white font-semibold text-xs transition-all hover:bg-amber-600 shrink-0 text-center">
                        View Reorder List
                    </a>
                </div>
            @endif
        </div>
    @endif

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-gray-200 dark:border-white/10">
            <div class="text-xs font-medium text-gray-500 dark:text-white/60">Total Active SKUs</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_sku'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-gray-200 dark:border-white/10">
            <div class="text-xs font-medium text-gray-500 dark:text-white/60">Total Valuation</div>
            <div class="text-2xl font-bold text-emerald-500 dark:text-emerald-400 mt-1">₱{{ number_format($stats['total_stock_value'], 2) }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-amber-500/20 bg-amber-500/5">
            <div class="text-xs font-medium text-amber-600 dark:text-amber-400">Low Stock</div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['low_stock'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-red-500/20 bg-red-500/5">
            <div class="text-xs font-medium text-red-600 dark:text-red-400">Out of Stock</div>
            <div class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['out_of_stock'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-purple-500/20 bg-purple-500/5">
            <div class="text-xs font-medium text-purple-600 dark:text-purple-400">Expiring Soon</div>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ $stats['expiring_soon'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-rose-500/20 bg-rose-500/5">
            <div class="text-xs font-medium text-rose-600 dark:text-rose-400">Expired Items</div>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">{{ $stats['expired'] }}</div>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="p-4 rounded-2xl bg-white dark:glass-card border border-gray-200 dark:border-white/10">
        <form method="GET" action="{{ route(auth()->user()->roleSlug() . '.inventory.index') }}" class="flex flex-col md:flex-row gap-3">
            
            <div class="relative flex-1">
                <x-icon name="search" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-white/40" />
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Search name, SKU, category, location..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-[#E63946]"
                />
            </div>

            <div class="w-full md:w-52">
                <select 
                    name="category" 
                    onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-[#E63946]"
                >
                    <option value="all">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full md:w-44">
                <select 
                    name="stock_status" 
                    onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-[#E63946]"
                >
                    <option value="">All Stock Status</option>
                    <option value="in_stock" {{ $stockStatus === 'in_stock' ? 'selected' : '' }}>In Stock Only</option>
                    <option value="low_stock" {{ $stockStatus === 'low_stock' ? 'selected' : '' }}>Low Stock Only</option>
                    <option value="out_of_stock" {{ $stockStatus === 'out_of_stock' ? 'selected' : '' }}>Out of Stock Only</option>
                </select>
            </div>

            <div class="w-full md:w-48">
                <select 
                    name="expiration_status" 
                    onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-[#E63946]"
                >
                    <option value="">All Expiration Status</option>
                    <option value="expired" {{ $expirationStatus === 'expired' ? 'selected' : '' }}>Expired Items</option>
                    <option value="expiring_soon" {{ $expirationStatus === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon (&le; 30 Days)</option>
                    <option value="has_expiration" {{ $expirationStatus === 'has_expiration' ? 'selected' : '' }}>Has Expiration Date</option>
                </select>
            </div>

            @if($search || ($category && $category !== 'all') || $stockStatus || $expirationStatus)
                <a href="{{ route(auth()->user()->roleSlug() . '.inventory.index') }}" class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-white/10 text-gray-700 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10 text-sm font-medium flex items-center justify-center gap-2">
                    <x-icon name="x" class="w-4 h-4" />
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Inventory Data Table -->
    <div class="rounded-2xl bg-white dark:glass-card border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-xs uppercase font-semibold text-gray-500 dark:text-white/60 tracking-wider">
                        <th class="py-4 px-6">Product Details</th>
                        <th class="py-4 px-6">Category</th>
                        <th class="py-4 px-6 text-right">Cost Price</th>
                        <th class="py-4 px-6 text-right">Selling Price</th>
                        <th class="py-4 px-6 text-center">Current Stock</th>
                        <th class="py-4 px-6 text-center">Reorder Level</th>
                        <th class="py-4 px-6">Location</th>
                        <th class="py-4 px-6">Expiration Date</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                            
                            <!-- Name & SKU -->
                            <td class="py-4 px-6">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $product->name }}</div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs font-mono text-gray-500 dark:text-white/40">SKU: {{ $product->sku }}</span>
                                    <span class="text-xs text-gray-400">• {{ $product->unit_label }}</span>
                                </div>
                            </td>

                            <!-- Category -->
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white/80">
                                    {{ $product->category }}
                                </span>
                            </td>

                            <!-- Cost Price -->
                            <td class="py-4 px-6 text-right font-mono text-gray-600 dark:text-white/70">
                                ₱{{ number_format($product->cost_price, 2) }}
                            </td>

                            <!-- Selling Price -->
                            <td class="py-4 px-6 text-right font-mono font-bold text-gray-900 dark:text-white">
                                ₱{{ number_format($product->unit_price, 2) }}
                            </td>

                            <!-- Stock Quantity -->
                            <td class="py-4 px-6 text-center">
                                <span class="text-base font-extrabold font-mono {{ $product->stock_quantity <= 0 ? 'text-red-500' : ($product->is_low_stock ? 'text-amber-500' : 'text-emerald-500') }}">
                                    {{ $product->stock_quantity }}
                                </span>
                            </td>

                            <!-- Reorder Level -->
                            <td class="py-4 px-6 text-center text-xs font-mono text-gray-500 dark:text-white/60">
                                {{ $product->min_stock_threshold }}
                            </td>

                            <!-- Location -->
                            <td class="py-4 px-6 text-xs text-gray-600 dark:text-white/70">
                                {{ $product->location ?? 'N/A' }}
                            </td>

                            <!-- Expiration Date -->
                            <td class="py-4 px-6 text-xs whitespace-nowrap">
                                @if($product->expiration_date)
                                    @if($product->is_expired)
                                        <span class="text-red-600 dark:text-red-400 font-semibold">
                                            Expired ({{ $product->expiration_date->format('M d, Y') }})
                                        </span>
                                    @elseif($product->is_expiring_soon)
                                        <span class="text-amber-600 dark:text-amber-400 font-semibold">
                                            Expiring Soon ({{ $product->expiration_date->format('M d, Y') }})
                                        </span>
                                    @else
                                        <span class="font-mono text-gray-700 dark:text-white/80">
                                            {{ $product->expiration_date->format('M d, Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-gray-400 dark:text-white/40">N/A</span>
                                @endif
                            </td>

                            <!-- Status Badge -->
                            <td class="py-4 px-6">
                                @if($product->stock_status === 'in_stock')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        In Stock
                                    </span>
                                @elseif($product->stock_status === 'low_stock')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 inline-flex items-center gap-1 animate-pulse">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Low Stock
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Out of Stock
                                    </span>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-center gap-2">
                                    
                                    <!-- Stock In / Out Adjustment Button -->
                                    <button 
                                        @click="openStockModal({{ json_encode($product) }})"
                                        title="Adjust Stock (Stock In / Stock Out)"
                                        class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition-colors cursor-pointer"
                                    >
                                        <x-icon name="refresh-cw" class="w-4 h-4" />
                                    </button>

                                    <!-- Edit Button -->
                                    <button 
                                        @click="openEditModal({{ json_encode($product) }})"
                                        title="Edit Product Details"
                                        class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-amber-600 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors cursor-pointer"
                                    >
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </button>

                                    <!-- Delete Button -->
                                    <button 
                                        @click="confirmDelete({{ json_encode($product) }})"
                                        title="Delete Item"
                                        class="p-2 rounded-xl border border-gray-200 dark:border-white/10 text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors cursor-pointer"
                                    >
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </button>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-gray-500 dark:text-white/50">
                                No inventory items found matching your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-white/10">
            {{ $products->links() }}
        </div>
    </div>

    <!-- ================= MODALS ================= -->

    <!-- ADD PRODUCT MODAL -->
    <div x-show="showAddModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="showAddModal = false" class="w-full max-w-2xl bg-white dark:bg-[#121212] rounded-2xl shadow-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-icon name="plus-circle" class="w-5 h-5 text-[#E63946]" />
                    Create New Inventory Item
                </h3>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <form method="POST" action="{{ route(auth()->user()->roleSlug() . '.inventory.store') }}" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Item Name *</label>
                        <input type="text" name="name" required placeholder="e.g., Motul 5W30 Synthetic Oil" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">SKU / Code (Leave blank to auto-generate)</label>
                        <input type="text" name="sku" placeholder="PRD-OIL-5W30" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Unit Label *</label>
                        <input type="text" name="unit_label" required placeholder="e.g., pc, set, can, liter" value="pc" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Cost Price (₱) *</label>
                        <input type="number" step="0.01" min="0" name="cost_price" required placeholder="0.00" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Selling Price / Unit Price (₱) *</label>
                        <input type="number" step="0.01" min="0" name="unit_price" required placeholder="0.00" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Initial Quantity *</label>
                        <input type="number" min="0" name="stock_quantity" value="0" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Reorder Level / Min Stock *</label>
                        <input type="number" min="0" name="min_stock_threshold" value="5" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Physical Location</label>
                        <input type="text" name="location" placeholder="Rack A-1 / Shelf 2" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">
                            Expiration Date <span class="text-gray-400 font-normal">(Optional - oils, paints, fluids)</span>
                        </label>
                        <input type="date" name="expiration_date" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Part details or compatibility notes..." class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white"></textarea>
                </div>

                <div class="hidden">
                    <input type="hidden" name="status" value="active">
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-white/10 flex justify-end gap-3">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 text-gray-700 dark:text-white/70 hover:bg-gray-100 text-sm font-medium">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#E63946] text-white hover:bg-[#E63946]/90 text-sm font-semibold shadow-md">Create Item</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT PRODUCT MODAL -->
    <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="showEditModal = false" class="w-full max-w-2xl bg-white dark:bg-[#121212] rounded-2xl shadow-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-icon name="pencil" class="w-5 h-5 text-amber-500" />
                    Edit Inventory Item: <span class="text-[#E63946]" x-text="activeProduct?.name"></span>
                </h3>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <form :action="updateUrl" method="POST" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Item Name *</label>
                        <input type="text" name="name" x-model="activeProduct.name" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">SKU / Code *</label>
                        <input type="text" name="sku" x-model="activeProduct.sku" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Category *</label>
                        <select name="category" x-model="activeProduct.category" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Unit Label *</label>
                        <input type="text" name="unit_label" x-model="activeProduct.unit_label" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Cost Price (₱) *</label>
                        <input type="number" step="0.01" min="0" name="cost_price" x-model="activeProduct.cost_price" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Selling Price (₱) *</label>
                        <input type="number" step="0.01" min="0" name="unit_price" x-model="activeProduct.unit_price" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Reorder Level / Min Stock *</label>
                        <input type="number" min="0" name="min_stock_threshold" x-model="activeProduct.min_stock_threshold" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Physical Location</label>
                        <input type="text" name="location" x-model="activeProduct.location" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Status *</label>
                        <select name="status" x-model="activeProduct.status" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-white/80">
                                Expiration Date <span class="text-gray-400 font-normal">(Update when restocking batch)</span>
                            </label>
                            <button type="button" @click="activeProduct.expiration_date = ''" class="text-[11px] text-[#E63946] hover:underline font-medium cursor-pointer" x-show="activeProduct?.expiration_date">
                                Clear Date
                            </button>
                        </div>
                        <input type="date" name="expiration_date" x-model="activeProduct.expiration_date" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                        <p class="text-[11px] text-gray-500 dark:text-white/50 mt-1">Select new expiration date when restocking perishable fluids, oils, or paints.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Description</label>
                    <textarea name="description" rows="2" x-model="activeProduct.description" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white"></textarea>
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-white/10 flex justify-end gap-3">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 text-gray-700 dark:text-white/70 hover:bg-gray-100 text-sm font-medium">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 text-white hover:bg-amber-600 text-sm font-semibold shadow-md">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- STOCK ADJUSTMENT MODAL (Stock In / Stock Out) -->
    <div x-show="showStockModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="showStockModal = false" class="w-full max-w-lg bg-white dark:bg-[#121212] rounded-2xl shadow-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="p-6 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-icon name="refresh-cw" class="w-5 h-5 text-blue-500" />
                    Adjust Stock / Restock: <span class="text-[#E63946]" x-text="activeProduct?.name"></span>
                </h3>
                <button @click="showStockModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <x-icon name="x" class="w-5 h-5" />
                </button>
            </div>

            <form :action="adjustStockUrl" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="p-3 rounded-xl bg-blue-500/10 border border-blue-500/20 text-xs text-blue-800 dark:text-blue-300 flex justify-between items-center">
                    <span>Current Available Quantity:</span>
                    <span class="font-bold text-base font-mono" x-text="activeProduct?.stock_quantity + ' ' + activeProduct?.unit_label"></span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Adjustment Action *</label>
                    <select name="type" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                        <option value="in">➕ Stock In (Receive shipment / Add stock)</option>
                        <option value="out">➖ Stock Out (Damage / Loss / Internal Use)</option>
                        <option value="adjustment">🔄 Stock Audit (Direct override set count)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Quantity *</label>
                    <input type="number" min="1" name="quantity" required placeholder="1" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-white/80">
                            Batch Expiration Date <span class="text-gray-400 font-normal">(Optional - update on restock)</span>
                        </label>
                        <button type="button" @click="activeProduct.expiration_date = ''" class="text-[11px] text-[#E63946] hover:underline font-medium cursor-pointer" x-show="activeProduct?.expiration_date">
                            Clear Date
                        </button>
                    </div>
                    <input type="date" name="expiration_date" x-model="activeProduct.expiration_date" class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Reason for Adjustment *</label>
                    <select name="reason" required class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white">
                        <option value="Supplier Shipment Restock">Supplier Shipment Restock</option>
                        <option value="Workshop Installation / Usage">Workshop Installation / Usage</option>
                        <option value="Damaged / Expired Item">Damaged / Expired Item</option>
                        <option value="Physical Stock Audit Correction">Physical Stock Audit Correction</option>
                        <option value="Return to Vendor">Return to Vendor</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-white/80 mb-1">Notes / Invoice Reference</label>
                    <textarea name="notes" rows="2" placeholder="e.g. PO #10842 received from supplier..." class="w-full px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm focus:outline-none focus:border-[#E63946] text-gray-900 dark:text-white"></textarea>
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-white/10 flex justify-end gap-3">
                    <button type="button" @click="showStockModal = false" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 text-gray-700 dark:text-white/70 hover:bg-gray-100 text-sm font-medium">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 text-sm font-semibold shadow-md">Apply Adjustment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE CONFIRMATION MODAL -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div @click.away="showDeleteModal = false" class="w-full max-w-md bg-white dark:bg-[#121212] rounded-2xl shadow-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="p-6 text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-red-500/10 text-red-500 flex items-center justify-center mb-4">
                    <x-icon name="trash" class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Delete Item Confirmation</h3>
                <p class="text-sm text-gray-500 dark:text-white/60 mt-2">
                    Are you sure you want to delete <span class="font-bold text-gray-900 dark:text-white" x-text="activeProduct?.name"></span>? This will archive the product line.
                </p>

                <form :action="deleteUrl" method="POST" class="mt-6 flex justify-center gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 text-gray-700 dark:text-white/70 hover:bg-gray-100 text-sm font-medium">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700 text-sm font-semibold shadow-md">Yes, Delete Item</button>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function inventoryApp() {
        return {
            showAddModal: false,
            showEditModal: false,
            showStockModal: false,
            showDeleteModal: false,
            activeProduct: null,
            baseUrl: "{{ url(auth()->user()->roleSlug() . '/inventory') }}",

            get updateUrl() {
                return this.activeProduct ? `${this.baseUrl}/${this.activeProduct.id}` : '#';
            },

            get adjustStockUrl() {
                return this.activeProduct ? `${this.baseUrl}/${this.activeProduct.id}/adjust` : '#';
            },

            get deleteUrl() {
                return this.activeProduct ? `${this.baseUrl}/${this.activeProduct.id}` : '#';
            },

            openAddModal() {
                this.showAddModal = true;
            },

            openEditModal(product) {
                this.activeProduct = Object.assign({}, product);
                if (product.expiration_date) {
                    this.activeProduct.expiration_date = String(product.expiration_date).split('T')[0];
                } else {
                    this.activeProduct.expiration_date = '';
                }
                this.showEditModal = true;
            },

            openStockModal(product) {
                this.activeProduct = Object.assign({}, product);
                if (product.expiration_date) {
                    this.activeProduct.expiration_date = String(product.expiration_date).split('T')[0];
                } else {
                    this.activeProduct.expiration_date = '';
                }
                this.showStockModal = true;
            },

            confirmDelete(product) {
                this.activeProduct = Object.assign({}, product);
                this.showDeleteModal = true;
            }
        }
    }
</script>
@endpush
@endsection
