@extends('layouts.dashboard', ['title' => 'Parts & Inventory Catalog', 'role' => 'customer'])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
                <span class="p-2 rounded-xl bg-[#E63946]/10 text-[#E63946]">
                    <x-icon name="box" class="w-6 h-6" />
                </span>
                Parts & Performance Catalog
            </h1>
            <p class="text-sm text-gray-600 dark:text-white/60 mt-1">
                Browse available automotive parts, lubricants, performance upgrades, and accessories in real-time.
            </p>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-gray-200 dark:border-white/10">
            <div class="text-xs font-medium text-gray-500 dark:text-white/60">Total Products</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-emerald-500/20 bg-emerald-500/5">
            <div class="text-xs font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                In Stock
            </div>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['in_stock'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-amber-500/20 bg-amber-500/5">
            <div class="text-xs font-medium text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Low Stock
            </div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['low_stock'] }}</div>
        </div>
        <div class="p-4 rounded-2xl bg-white dark:glass-card border border-red-500/20 bg-red-500/5">
            <div class="text-xs font-medium text-red-600 dark:text-red-400 flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                Out of Stock
            </div>
            <div class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['out_of_stock'] }}</div>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="p-4 rounded-2xl bg-white dark:glass-card border border-gray-200 dark:border-white/10 space-y-4">
        <form method="GET" action="{{ route('customer.inventory.index') }}" class="flex flex-col md:flex-row flex-wrap items-stretch md:items-center gap-3">
            
            <!-- Search -->
            <div class="relative flex-1 min-w-[240px] md:min-w-[280px]">
                <x-icon name="search" class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-white/40 pointer-events-none" />
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Search by part name, SKU, or category..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-white/40 text-sm focus:outline-none focus:border-[#E63946]"
                />
            </div>

            <!-- Category Select -->
            <div class="w-full sm:w-auto md:w-48">
                <select 
                    name="category" 
                    onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-[#E63946] cursor-pointer"
                >
                    <option value="all">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Stock Status Filter -->
            <div class="w-full sm:w-auto md:w-36">
                <select 
                    name="stock_status" 
                    onchange="this.form.submit()"
                    class="w-full px-3 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-900 dark:text-white text-sm focus:outline-none focus:border-[#E63946] cursor-pointer"
                >
                    <option value="">All Stock</option>
                    <option value="in_stock" {{ $stockStatus === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low_stock" {{ $stockStatus === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out_of_stock" {{ $stockStatus === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>

            <!-- Reset -->
            @if($search || ($category && $category !== 'all') || $stockStatus)
                <a href="{{ route('customer.inventory.index') }}" class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-white/10 text-gray-700 dark:text-white/70 hover:bg-gray-100 dark:hover:bg-white/10 text-sm font-medium flex items-center justify-center gap-2 whitespace-nowrap">
                    <x-icon name="x" class="w-4 h-4" />
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Product Grid -->
    @if($products->isEmpty())
        <div class="p-12 text-center rounded-2xl bg-white dark:glass-card border border-gray-200 dark:border-white/10">
            <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-400">
                <x-icon name="box" class="w-8 h-8" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-4">No Products Found</h3>
            <p class="text-sm text-gray-500 dark:text-white/60 mt-1">Try clearing filters or searching for another term.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="flex flex-col justify-between rounded-2xl bg-white dark:glass-card border border-gray-200 dark:border-white/10 p-5 hover:border-[#E63946]/40 transition-all duration-300 shadow-sm hover:shadow-lg">
                    <div>
                        <!-- Category & Status Badge -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white/80 border border-gray-200 dark:border-white/10">
                                {{ $product->category }}
                            </span>

                            <!-- Real-time Stock Badge -->
                            @if($product->stock_status === 'in_stock')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    In Stock ({{ $product->stock_quantity }})
                                </span>
                            @elseif($product->stock_status === 'low_stock')
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center gap-1.5 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Low Stock (Only {{ $product->stock_quantity }} left)
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Out of Stock
                                </span>
                            @endif
                        </div>

                        <!-- Product Title & SKU -->
                        <h3 class="text-base font-bold text-gray-900 dark:text-white line-clamp-2">{{ $product->name }}</h3>
                        <p class="text-xs font-mono text-gray-500 dark:text-white/40 mt-1">SKU: {{ $product->sku }}</p>
                        
                        <p class="text-xs text-gray-600 dark:text-white/60 mt-2 line-clamp-2">
                            {{ $product->description ?? 'Standard automotive grade replacement part.' }}
                        </p>
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-100 dark:border-white/10 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-gray-400 block">Unit Price</span>
                            <span class="text-lg font-extrabold text-[#E63946]">
                                ₱{{ number_format($product->unit_price, 2) }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-white/40">/ {{ $product->unit_label }}</span>
                        </div>

                        <!-- Action Button (Disabled when Out of Stock) -->
                        @if($product->is_out_of_stock)
                            <button 
                                disabled 
                                title="This item is currently out of stock."
                                class="px-3.5 py-2 rounded-xl bg-gray-200 dark:bg-white/10 text-gray-400 dark:text-white/30 text-xs font-semibold cursor-not-allowed flex items-center gap-1.5"
                            >
                                <x-icon name="x" class="w-3.5 h-3.5" />
                                Not Available
                            </button>
                        @else
                            <a 
                                href="{{ route('customer.book-service') }}?product_id={{ $product->id }}" 
                                class="px-3.5 py-2 rounded-xl bg-[#E63946] hover:bg-[#E63946]/90 text-white text-xs font-semibold transition-all flex items-center gap-1.5 shadow-md shadow-[#E63946]/20"
                            >
                                <x-icon name="plus" class="w-3.5 h-3.5" />
                                Select for Service
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif

</div>
@endsection
