@extends('layouts.dashboard')

@section('title', 'Customer Dashboard | AutoProject+')

@section('content')


<div class="space-y-8 animate-fade-in">
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Customer Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-300">Welcome back, {{ auth()->user()->name }}! Here's your service overview.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card title="Upcoming Bookings" value="{{ $upcomingCount }}" color="blue">
            <x-slot name="icon">
                <x-icon name="calendar" class="w-6 h-6" />
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Active Services" value="{{ $activeCount }}" color="red">
            <x-slot name="icon">
                <x-icon name="wrench" class="w-6 h-6" />
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Completed Services" value="{{ $completedCount }}" color="green">
            <x-slot name="icon">
                <x-icon name="check-square" class="w-6 h-6" />
            </x-slot>
        </x-stat-card>
        <x-stat-card title="Support Messages" value="{{ $supportCount }}" color="charcoal">
            <x-slot name="icon">
                <x-icon name="message-square" class="w-6 h-6" />
            </x-slot>
        </x-stat-card>
    </div>

    {{-- Quick Actions --}}
    <div class="rounded-2xl p-6 bg-gray-100 dark:bg-[#151515]/80 backdrop-blur-md border border-gray-300 dark:border-[#E63946]/20">
        <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Quick Actions</h2>
        <div class="flex flex-wrap gap-4">
            <a href="{{ url('/customer/book-service') }}">
                <x-button variant="accent" class="text-white">Book New Service</x-button>
            </a>
            <a href="{{ url('/customer/track') }}">
                <x-button variant="secondary">Track Service</x-button>
            </a>
            <a href="{{ url('/customer/support') }}">
                <x-button variant="outline">Create Support Ticket</x-button>
            </a>
        </div>
    </div>

    {{-- Available Services Overview --}}
    <div class="rounded-2xl p-6 bg-white dark:bg-[#151515]/60 backdrop-blur-md border border-gray-300 dark:border-gray-800 shadow-lg">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Our Services</h2>
            <a href="{{ url('/customer/book-service') }}">
                <x-button variant="outline" size="sm">
                    View All & Book
                    <x-icon name="chevron-right" class="w-4 h-4 ml-1 inline-block" />
                </x-button>
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($serviceCategories as $category)
                <a href="{{ url('/customer/book-service') }}" class="block">
                    <div 
                        class="rounded-xl border border-gray-300 dark:border-gray-700/50 hover:border-[#E63946] hover:shadow-xl hover:shadow-[#E63946]/10 transition-all duration-300 group cursor-pointer h-full bg-gray-50 dark:bg-[#0B0B0B]/40 backdrop-blur-sm p-4"
                        style="border-left-color: {{ $category['color'] }}; border-left-width: 4px;"
                    >
                        <div class="flex items-start gap-3">
                            <div 
                                class="p-3 rounded-xl text-white flex-shrink-0 shadow-lg"
                                style="background-color: {{ $category['color'] }}"
                            >
                                <x-icon name="wrench" class="w-5 h-5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-1 group-hover:text-[#E63946] transition-colors text-base">
                                    {{ $category['name'] }}
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300 mb-2 font-medium">
                                    {{ $category['services_count'] }} services available
                                </p>
                                <p class="text-base font-bold text-[#E63946]">
                                    ₱{{ number_format($category['min_price']) }} - ₱{{ number_format($category['max_price']) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        
        <div class="mt-4 p-4 bg-red-50 dark:bg-[#E63946]/10 rounded-xl border border-red-200 dark:border-[#E63946]/30 backdrop-blur-sm">
            <p class="text-sm text-gray-700 dark:text-gray-300">
                <strong class="text-[#E63946]">Note:</strong> Prices shown are estimated ranges. Final pricing will be calculated after vehicle inspection. 
                We use quality branded parts and provide detailed quotes including all parts and labor costs.
            </p>
        </div>
    </div>

    {{-- Upcoming Bookings --}}
    <div class="rounded-2xl p-6 bg-white dark:bg-[#151515]/60 backdrop-blur-md border border-gray-300 dark:border-gray-800 shadow-lg">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Upcoming Bookings</h2>
        @if(count($upcomingBookings) > 0)
            <div class="space-y-4">
                @foreach($upcomingBookings as $booking)
                    <div class="border border-gray-300 dark:border-gray-700 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gray-50 dark:bg-[#0B0B0B]/40 hover:bg-gray-100 dark:hover:bg-[#0B0B0B]/60 transition-colors">
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 dark:text-white mb-1">{{ $booking['service'] }}</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $booking['vehicle'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $booking['date'] }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-status-badge status="{{ $booking['status'] }}">Confirmed</x-status-badge>
                            <a href="{{ url('/customer/track') }}">
                                <x-button size="sm" variant="outline">View Details</x-button>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400">No upcoming bookings</p>
        @endif
    </div>

    {{-- Active Services --}}
    <div class="rounded-2xl p-6 bg-white dark:bg-[#151515]/60 backdrop-blur-md border border-gray-300 dark:border-gray-800 shadow-lg">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Active Services</h2>
        @if(count($activeServices) > 0)
            <div class="space-y-4">
                @foreach($activeServices as $service)
                    <div class="border border-gray-300 dark:border-gray-700 rounded-xl p-4 bg-gray-50 dark:bg-[#0B0B0B]/40">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-3">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900 dark:text-white mb-1">{{ $service['service'] }}</h3>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $service['vehicle'] }}</p>
                            </div>
                            <x-status-badge status="in-progress">{{ $service['status'] }}</x-status-badge>
                        </div>
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Progress</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $service['progress'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-300 dark:bg-gray-800/50 rounded-full h-2">
                                <div
                                    class="bg-gradient-to-r from-[#E63946] to-[#E63946]/80 h-2 rounded-full transition-all shadow-lg shadow-[#E63946]/30"
                                    style="width: {{ $service['progress'] }}%"
                                ></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-600 dark:text-gray-400">No active services</p>
        @endif
    </div>
</div>
@endsection
