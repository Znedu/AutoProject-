@extends('layouts.dashboard')

@section('title', 'My Bookings | AutoProject+')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Pending Verification',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'waiting_payment' => 'Waiting Payment',
        'confirmed' => 'Confirmed',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    $filters = ['all', 'pending', 'confirmed', 'in_progress', 'completed'];
@endphp

<div class="space-y-6 animate-fade-in">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">My Bookings</h1>
            <p class="text-gray-600 dark:text-gray-400">View and manage all your service bookings.</p>
        </div>
        <a href="{{ route('customer.book-service') }}">
            <x-button variant="accent" class="text-white">New Booking</x-button>
        </a>
    </div>

    <x-card>
        <div class="flex flex-wrap gap-2">
            @foreach ($filters as $filter)
                <a href="{{ route('customer.bookings.index', ['status' => $filter]) }}">
                    <x-button
                        :variant="$selectedFilter === $filter ? 'primary' : 'ghost'"
                        size="sm"
                        class="capitalize"
                    >
                        @if ($filter === 'all')
                            All
                        @elseif ($filter === 'in_progress')
                            In Progress
                        @else
                            {{ $filter }}
                        @endif
                    </x-button>
                </a>
            @endforeach
        </div>
    </x-card>

    <div class="space-y-4">
        @forelse ($bookings as $booking)
            @php
                $quotation = $booking->quotations->first();
                $payment = $booking->payments->first();
                $serviceNames = $booking->bookingServices->pluck('service.name')->join(', ');
                $badgeStatus = str_replace('_', '-', $booking->status);
                $vehicleLabel = trim(implode(' ', array_filter([
                    $booking->vehicle?->make,
                    $booking->vehicle?->model,
                    $booking->vehicle?->year,
                ])));
            @endphp

            <x-card hover>
                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-1 space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-bold mb-1 text-gray-900 dark:text-white">{{ $serviceNames }}</h3>
                                <p class="text-gray-700 dark:text-gray-300">
                                    {{ $vehicleLabel }} • {{ $booking->vehicle?->plate_number }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">{{ $booking->booking_number }}</p>
                            </div>
                            <x-status-badge :status="$badgeStatus">
                                {{ $statusLabels[$booking->status] ?? $booking->display_status }}
                            </x-status-badge>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Date</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ $booking->preferred_date->format('F j, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Time</p>
                                <p class="font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($booking->preferred_time)->format('h:i A') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Estimated Cost</p>
                                <p class="font-medium text-[#E63946]">
                                    {{ $quotation?->total_range_display ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-700 dark:text-gray-300">Reservation Fee (₱200):</span>
                                @if ($payment && $payment->is_verified)
                                    <span class="flex items-center gap-1 text-green-600 font-medium">
                                        <x-icon name="check-square" class="w-4 h-4 text-green-600 inline" />
                                        Paid & Verified
                                    </span>
                                @elseif ($payment)
                                    <span class="flex items-center gap-1 text-amber-600 font-medium">
                                        <x-icon name="info" class="w-4 h-4 text-amber-600 inline" />
                                        Pending Verification
                                    </span>
                                @else
                                    <span class="flex items-center gap-1 text-red-600 font-medium">
                                        <x-icon name="x" class="w-4 h-4 text-red-600 inline" />
                                        Not Paid
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex lg:flex-col gap-2 justify-end">
                        <a href="{{ route('customer.track') }}">
                            <x-button variant="secondary" size="sm" class="whitespace-nowrap w-full">
                                Track Status
                            </x-button>
                        </a>

                        <a href="{{ route('customer.support.index') }}?subject={{ urlencode('Issue with '.$booking->booking_number.' - '.$serviceNames) }}">
                            <x-button variant="secondary" size="sm" class="whitespace-nowrap">
                                <x-icon name="message-square" class="w-4 h-4 mr-1 inline text-gray-500" />
                                Contact Support
                            </x-button>
                        </a>

                        @if ($booking->status === \App\Models\Booking::STATUS_APPROVED)
                            <a href="{{ url('/customer/payment/'.$booking->id) }}">
                                <x-button variant="accent" size="sm" class="whitespace-nowrap bg-green-600 hover:bg-green-700 text-white w-full border-green-600">
                                    Pay Now
                                </x-button>
                            </a>
                        @endif

                        @if ($booking->is_cancellable)
                            <form
                                method="POST"
                                action="{{ route('customer.bookings.destroy', $booking) }}"
                                onsubmit="return confirm('Are you sure you want to cancel this booking?')"
                            >
                                @csrf
                                @method('DELETE')
                                <x-button
                                    type="submit"
                                    variant="outline"
                                    size="sm"
                                    class="whitespace-nowrap bg-red-600 hover:bg-red-700 text-white border-red-600 hover:border-red-700 w-full"
                                >
                                    Cancel
                                </x-button>
                            </form>
                        @endif
                    </div>
                </div>
            </x-card>
        @empty
            <x-card>
                <div class="text-center py-8">
                    <p class="text-gray-600 dark:text-gray-400">No bookings found for this filter.</p>
                </div>
            </x-card>
        @endforelse
    </div>
</div>
@endsection
