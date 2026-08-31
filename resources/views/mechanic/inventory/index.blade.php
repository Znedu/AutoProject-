@extends('layouts.dashboard', ['title' => 'Shop Inventory & Storage', 'role' => 'mechanic'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span class="p-2 rounded-xl bg-[#E63946]/10 text-[#E63946]">
                    <x-icon name="box" class="w-6 h-6" />
                </span>
                Workshop Shop Inventory & Storage
            </h1>
            <p class="text-sm text-gray-600 dark:text-white/60 mt-1">
                Read-only live stock count and physical shelf/rack location lookup for workshop technicians.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="px-3 py-1.5 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20 text-xs font-semibold flex items-center gap-1.5">
                <x-icon name="info" class="w-4 h-4" />
                Mechanic Read-Only View
            </span>
        </div>
    </div>

    <!-- Stats summary cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-gray-200 dark:border-white/10">
            <div class="text-xs font-medium text-gray-500 dark:text-white/60">Total Item Lines</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_items'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-gray-200 dark:border-white/10">
            <div class="text-xs font-medium text-gray-500 dark:text-white/60">Total On-Hand Units</div>
            <div class="text-2xl font-bold text-blue-500 dark:text-blue-400 mt-1">{{ number_format($stats['total_units']) }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-amber-500/20 bg-amber-500/5">
            <div class="text-xs font-medium text-amber-600 dark:text-amber-400">Low Stock Lines</div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['low_stock_count'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-red-500/20 bg-red-500/5">
            <div class="text-xs font-medium text-red-600 dark:text-red-400">Out of Stock Lines</div>
            <div class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['out_of_stock_count'] }}</div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="p-4 rounded-2xl bg-white dark:glass-card border border-gray-200 dark:border-white/10">
        <form method="GET" action="{{ route('mechanic.inventory.index') }}" class="flex flex-col md:flex-row gap-3">
            
            <div class="relative flex-1">
                <x-icon name="search" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-white/40" />
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Search by part name, SKU, rack location..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-[#E63946]"
                />
            </div>

            <div class="w-full md:w-56">
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

            <div class="w-full md:w-48">
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

            @if($search || ($category && $category !== 'all') || $stockStatus)
                <a href="{{ route('mechanic.inventory.index') }}" class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-white/10 text-gray-700 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10 text-sm font-medium flex items-center justify-center gap-2">
                    <x-icon name="x" class="w-4 h-4" />
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-white dark:glass-card border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-xs uppercase font-semibold text-gray-500 dark:text-white/60 tracking-wider">
                        <th class="py-4 px-6">Physical Location</th>
                        <th class="py-4 px-6">Part Name & SKU</th>
                        <th class="py-4 px-6">Category</th>
                        <th class="py-4 px-6 text-center">Available Stock</th>
                        <th class="py-4 px-6">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    @forelse($products as $product)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5 transition-colors">
                            <!-- Location -->
                            <td class="py-4 px-6 font-medium">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-800 dark:text-white font-mono text-xs border border-gray-200 dark:border-white/10">
                                    <x-icon name="map-pin" class="w-3.5 h-3.5 text-[#E63946]" />
                                    {{ $product->location ?? 'Unassigned' }}
                                </span>
                            </td>

                            <!-- Product Name & SKU -->
                            <td class="py-4 px-6">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $product->name }}</div>
                                <div class="text-xs font-mono text-gray-500 dark:text-white/40 mt-0.5">SKU: {{ $product->sku }}</div>
                            </td>

                            <!-- Category -->
                            <td class="py-4 px-6 text-gray-600 dark:text-white/70">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white/80">
                                    {{ $product->category }}
                                </span>
                            </td>

                            <!-- Available Stock Count -->
                            <td class="py-4 px-6 text-center">
                                <span class="text-lg font-extrabold font-mono {{ $product->stock_quantity <= 0 ? 'text-red-500' : ($product->is_low_stock ? 'text-amber-500' : 'text-emerald-500') }}">
                                    {{ $product->stock_quantity }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-white/40 block">{{ $product->unit_label }}</span>
                            </td>

                            <!-- Status Badge -->
                            <td class="py-4 px-6">
                                @if($product->stock_status === 'in_stock')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        In Stock
                                    </span>
                                @elseif($product->stock_status === 'low_stock')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 inline-flex items-center gap-1">
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-500 dark:text-white/50">
                                No inventory items match your search filter.
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

</div>
@endsection
