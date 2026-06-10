@extends('layouts.dashboard')

@section('title', 'Staff Dashboard | AutoProject+')

@section('content')
<div class="space-y-8 animate-fade-in">
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Staff Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage bookings and assist customers.</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card
            title="Pending Bookings"
            value="5"
            icon="clock"
            color="red"
        />
        <x-stat-card
            title="Scheduled Today"
            value="3"
            icon="calendar"
            color="blue"
        />
        <x-stat-card
            title="Open Tickets"
            value="8"
            icon="message-square"
            color="charcoal"
        />
        <x-stat-card
            title="Resolved Today"
            value="12"
            icon="check-square"
            color="green"
        />
    </div>

    {{-- Quick Actions --}}
    <x-card>
        <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Quick Actions</h2>
        <div class="flex flex-wrap gap-4">
            <a href="{{ url('/staff/booking-queue') }}">
                <x-button variant="accent">
                    View Booking Queue
                </x-button>
            </a>
            <a href="{{ url('/staff/assistance') }}">
                <x-button variant="secondary">
                    Customer Assistance
                </x-button>
            </a>
        </div>
    </x-card>

    {{-- Pending Bookings Approval --}}
    <x-card>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pending Bookings Approval</h2>
            <a href="{{ url('/staff/booking-queue') }}">
                <x-button variant="ghost" size="sm">View All</x-button>
            </a>
        </div>
        
        <div class="space-y-3">
            <div class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#151515] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold mb-1 text-gray-900 dark:text-white">Body Kit Installation</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Customer: Carlos Reyes</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Date: April 8, 2026</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-status-badge status="pending">Pending</x-status-badge>
                    <a href="{{ url('/staff/booking-queue?id=1') }}">
                        <x-button size="sm" variant="secondary">Review</x-button>
                    </a>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Open Support Tickets --}}
    <x-card>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Open Support Tickets</h2>
            <a href="{{ url('/staff/assistance') }}">
                <x-button variant="ghost" size="sm">View All</x-button>
            </a>
        </div>
        
        <div class="space-y-3">
            <div class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#151515] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold mb-1 text-gray-900 dark:text-white">Question about paint warranty</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Customer: Juan Dela Cruz</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Date: March 30, 2026</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-status-badge status="open">Open</x-status-badge>
                    <a href="{{ url('/staff/assistance?id=1') }}">
                        <x-button size="sm" variant="secondary">Respond</x-button>
                    </a>
                </div>
            </div>

            <div class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#151515] rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold mb-1 text-gray-900 dark:text-white">Reschedule booking request</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Customer: Maria Santos</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Date: March 28, 2026</p>
                </div>
                <div class="flex items-center gap-3">
                    <x-status-badge status="in-progress">In Progress</x-status-badge>
                    <a href="{{ url('/staff/assistance?id=2') }}">
                        <x-button size="sm" variant="secondary">Respond</x-button>
                    </a>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Today's Schedule --}}
    <x-card>
        <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Today's Service Schedule</h2>
        <div class="space-y-4">
            <div class="flex items-center gap-4 pb-4 border-b border-gray-200 dark:border-white/10">
                <div class="text-center min-w-[80px]">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Time</p>
                    <p class="font-bold text-gray-900 dark:text-white">10:00 AM</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 dark:text-white truncate">Engine Customization</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate">Maria Santos - Honda Civic 2020</p>
                </div>
                <x-status-badge status="confirmed">Confirmed</x-status-badge>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-center min-w-[80px]">
                    <p class="text-xs text-gray-600 dark:text-gray-400">Time</p>
                    <p class="font-bold text-gray-900 dark:text-white">02:00 PM</p>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-medium text-gray-900 dark:text-white truncate">Exhaust Fabrication</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate">Pedro Lopez - Mazda RX-7 2019</p>
                </div>
                <x-status-badge status="confirmed">Confirmed</x-status-badge>
            </div>
        </div>
    </x-card>
</div>
@endsection
