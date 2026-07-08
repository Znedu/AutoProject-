@extends('layouts.dashboard')

@section('title', 'Schedule | Staff | AutoProject+')

@section('content')
<div class="space-y-8 animate-fade-in">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Schedule</h1>
            <p class="text-gray-600 dark:text-gray-400">Weekly booking schedule — {{ $weekLabel }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ $prevWeekUrl }}">
                <x-button variant="ghost" size="sm">&#8592; Prev Week</x-button>
            </a>
            @unless ($isCurrentWeek)
                <a href="{{ $currentWeekUrl }}">
                    <x-button variant="secondary" size="sm">Today</x-button>
                </a>
            @endunless
            <a href="{{ $nextWeekUrl }}">
                <x-button variant="ghost" size="sm">Next Week &#8594;</x-button>
            </a>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap gap-4 text-sm">
        <span class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-green-500"></span> Available
        </span>
        <span class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-red-500"></span> Booked
        </span>
        <span class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-gray-400"></span> Closed
        </span>
        <span class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-[#D2781A]"></span> Walk-In
        </span>
    </div>

    {{-- Calendar Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-7 gap-4">
        @foreach ($days as $day)
            <div x-data="{ expanded: false }"
                class="border {{ $day['is_today'] ? 'border-[#D2781A]' : 'border-gray-200 dark:border-white/10' }} rounded-2xl overflow-hidden bg-white dark:bg-[#111111]">

                {{-- Day Header --}}
                <div class="px-3 py-2 {{ $day['is_today'] ? 'bg-[#D2781A]/10' : 'bg-gray-50 dark:bg-white/5' }} border-b border-gray-200 dark:border-white/10">
                    <p class="text-xs font-semibold text-gray-900 dark:text-white">{{ $day['label'] }}</p>
                    @if ($day['is_today'])
                        <span class="text-[10px] text-[#D2781A] font-bold">TODAY</span>
                    @endif
                    @if ($day['is_sunday'])
                        <span class="text-[10px] text-gray-400">Closed (Sunday)</span>
                    @elseif ($day['is_fully_booked'] ?? false)
                        <span class="text-[10px] text-red-500 font-semibold">Fully Booked</span>
                    @else
                        <span class="text-[10px] text-gray-500 dark:text-gray-400">
                            {{ $day['booking_count'] ?? 0 }} booking{{ ($day['booking_count'] ?? 0) !== 1 ? 's' : '' }}
                        </span>
                    @endif
                </div>

                @if ($day['is_sunday'])
                    <div class="p-3 text-xs text-gray-400 dark:text-gray-600 italic">Shop is closed on Sundays.</div>
                @else
                    {{-- Bookings List --}}
                    <div class="p-3 space-y-2">
                        @forelse ($day['bookings'] as $booking)
                            <div class="text-xs rounded-lg p-2
                                {{ $booking['is_walk_in'] ? 'bg-[#D2781A]/10 border border-[#D2781A]/30' : 'bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10' }}">
                                <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $booking['time'] }}</p>
                                <p class="text-gray-700 dark:text-gray-300 truncate">{{ $booking['customer'] }}</p>
                                <p class="text-gray-500 dark:text-gray-400 truncate">{{ $booking['service'] }}</p>
                                <div class="flex items-center gap-1 mt-1">
                                    <x-status-badge :status="$booking['status']" />
                                    @if ($booking['is_walk_in'])
                                        <span class="text-[10px] text-[#D2781A] font-semibold">Walk-In</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 dark:text-gray-600 italic">No bookings.</p>
                        @endforelse
                    </div>

                    {{-- Slot availability toggle --}}
                    @if (count($day['slots'] ?? []) > 0)
                        <div class="px-3 pb-3">
                            <button type="button" @click="expanded = !expanded"
                                class="text-xs text-[#D2781A] hover:underline">
                                <span x-show="!expanded">Show slots ▾</span>
                                <span x-show="expanded" x-cloak>Hide slots ▴</span>
                            </button>
                            <div x-show="expanded" x-cloak class="mt-2 space-y-1">
                                @foreach ($day['slots'] as $daySlot)
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $daySlot['available'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                        <span class="text-gray-600 dark:text-gray-400">{{ $daySlot['time'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        @endforeach
    </div>

    {{-- Quick actions --}}
    <div class="flex gap-4">
        <a href="{{ route('staff.walk-in-booking') }}">
            <x-button variant="accent">New Walk-In Booking</x-button>
        </a>
        <a href="{{ route('staff.booking-queue') }}">
            <x-button variant="secondary">Booking Queue</x-button>
        </a>
    </div>

</div>
@endsection
