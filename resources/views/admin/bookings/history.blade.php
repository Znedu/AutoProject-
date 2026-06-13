@extends('layouts.dashboard')

@section('title', 'Booking History | AutoProject+')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Booking History</h1>
            <p class="text-gray-600 dark:text-gray-400">Complete record of all customer booking requests.</p>
        </div>
        <a href="{{ route('admin.approvals.index') }}">
            <x-button variant="secondary">Pending Approvals</x-button>
        </a>
    </div>

    <x-card>
        <div class="flex flex-wrap gap-2">
            @foreach (['all', 'pending', 'approved', 'rejected', 'cancelled', 'completed'] as $filter)
                <a href="{{ route('admin.bookings.history', ['status' => $filter]) }}">
                    <x-button
                        :variant="$selectedFilter === $filter ? 'primary' : 'ghost'"
                        size="sm"
                        class="capitalize"
                    >
                        {{ $filter === 'all' ? 'All Bookings' : $filter }}
                    </x-button>
                </a>
            @endforeach
        </div>
    </x-card>

    <x-card class="p-0 overflow-hidden">
        <x-table>
            <x-table-header>
                <x-table-row>
                    <x-table-head>Booking #</x-table-head>
                    <x-table-head>Customer</x-table-head>
                    <x-table-head>Services</x-table-head>
                    <x-table-head>Schedule</x-table-head>
                    <x-table-head>Status</x-table-head>
                    <x-table-head>Submitted</x-table-head>
                </x-table-row>
            </x-table-header>
            <x-table-body>
                @forelse ($bookings as $booking)
                    @php
                        $quotation = $booking->quotations->first();
                        $serviceNames = $booking->bookingServices->pluck('service.name')->join(', ');
                    @endphp
                    <x-table-row>
                        <x-table-cell>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $booking->booking_number }}</span>
                        </x-table-cell>
                        <x-table-cell>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $booking->customer_name }}</p>
                            <p class="text-xs text-gray-500">{{ $booking->contact_number }}</p>
                        </x-table-cell>
                        <x-table-cell>
                            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $serviceNames }}</p>
                            @if ($quotation)
                                <p class="text-xs text-[#E63946] font-semibold mt-1">{{ $quotation->total_range_display }}</p>
                            @endif
                        </x-table-cell>
                        <x-table-cell>
                            <p class="text-sm">{{ $booking->preferred_date->format('M j, Y') }}</p>
                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($booking->preferred_time)->format('h:i A') }}</p>
                        </x-table-cell>
                        <x-table-cell>
                            <x-status-badge :status="$booking->status">{{ $booking->display_status }}</x-status-badge>
                        </x-table-cell>
                        <x-table-cell>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $booking->created_at->format('M j, Y') }}</span>
                        </x-table-cell>
                    </x-table-row>
                @empty
                    <x-table-row>
                        <x-table-cell colspan="6">
                            <p class="text-center py-8 text-gray-500">No bookings found.</p>
                        </x-table-cell>
                    </x-table-row>
                @endforelse
            </x-table-body>
        </x-table>
    </x-card>

    <div>
        {{ $bookings->links() }}
    </div>
</div>
@endsection
