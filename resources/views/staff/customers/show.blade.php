@extends('layouts.dashboard')

@section('title', 'Customer Profile | Staff | AutoProject+')

@section('content')
<div class="space-y-8 animate-fade-in">

    {{-- Back --}}
    <a href="{{ route('staff.customers.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-[#D2781A] transition">
        ← Back to Customers
    </a>

    {{-- Profile Header --}}
    <x-card>
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="w-16 h-16 rounded-full bg-[#D2781A]/20 flex items-center justify-center flex-shrink-0">
                <span class="text-[#D2781A] font-bold text-2xl">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $customer->name }}</h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $customer->email }}</p>
                @if ($customer->phone)
                    <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $customer->phone }}</p>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <x-status-badge :status="$customer->status ?? 'active'" />
                <a href="{{ route('staff.walk-in-booking') }}?customer_id={{ $customer->id }}&customer_name={{ urlencode($customer->name) }}">
                    <x-button variant="accent" size="sm">New Walk-In Booking</x-button>
                </a>
            </div>
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Vehicles --}}
        <x-card>
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Registered Vehicles</h2>
            <div class="space-y-3">
                @forelse ($customer->vehicles as $vehicle)
                    <div class="border border-gray-200 dark:border-white/10 rounded-xl p-4">
                        <p class="font-semibold text-gray-900 dark:text-white">
                            {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Plate: {{ $vehicle->plate_number }}</p>
                        @if ($vehicle->color)
                            <p class="text-sm text-gray-500 dark:text-gray-400">Color: {{ $vehicle->color }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No vehicles registered.</p>
                @endforelse
            </div>
        </x-card>

        {{-- Booking History --}}
        <x-card>
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Booking History</h2>
            <div class="space-y-3">
                @forelse ($bookings as $booking)
                    <div class="border border-gray-200 dark:border-white/10 rounded-xl p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-gray-900 dark:text-white text-sm">
                                    {{ $booking['service'] }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $booking['vehicle'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $booking['preferred_date'] }}</p>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <x-status-badge :status="$booking['status']" />
                                @if ($booking['is_walk_in'])
                                    <span class="text-[10px] text-[#D2781A] font-semibold">Walk-In</span>
                                @endif
                            </div>
                        </div>
                        @if ($booking['booking_number'])
                            <p class="text-[10px] text-gray-400 dark:text-gray-600 mt-2">
                                #{{ $booking['booking_number'] }}
                            </p>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-sm">No bookings found.</p>
                @endforelse
            </div>
        </x-card>

    </div>
</div>
@endsection
