@extends('layouts.dashboard')

@section('title', 'Billing Breakdown #' . $booking->booking_number . ' | AutoProject+')

@section('content')
<div class="space-y-6 animate-fade-in">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('customer.bookings.index') }}" class="text-gray-500 hover:text-gray-900 dark:hover:text-white transition">
                    <x-icon name="chevron-left" class="w-5 h-5" />
                </a>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    Service Billing Statement
                </h1>
                <span class="font-mono text-sm font-semibold px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300">
                    #{{ $booking->booking_number }}
                </span>
            </div>
            <p class="text-gray-600 dark:text-gray-400">
                Vehicle: <span class="font-semibold text-gray-900 dark:text-white">{{ $booking->vehicle?->make }} {{ $booking->vehicle?->model }} {{ $booking->vehicle?->year }}</span> • Plate: <span class="font-semibold text-gray-900 dark:text-white">{{ $booking->vehicle?->plate_number }}</span>
            </p>
        </div>

        <div class="flex gap-3">
            <a href="{{ route('customer.track', ['booking_id' => $booking->id]) }}">
                <x-button variant="secondary">
                    <x-icon name="activity" class="w-4 h-4 mr-2" />
                    Track Job
                </x-button>
            </a>
            <a href="{{ route('customer.support.index') }}?subject={{ urlencode('Billing Inquiry: Booking #'.$booking->booking_number) }}">
                <x-button variant="secondary">
                    <x-icon name="message-square" class="w-4 h-4 mr-2 text-gray-500" />
                    Help & Support
                </x-button>
            </a>
        </div>
    </div>

    @if (! $summary->isFinalized)
        {{-- Notice Banner --}}
        <x-card class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-amber-500/30">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center flex-shrink-0">
                    <x-icon name="clock" class="w-5 h-5" />
                </div>
                <div class="space-y-1">
                    <h3 class="font-bold text-gray-900 dark:text-white text-base">Your final bill is being prepared</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Our technicians are currently evaluating your vehicle requirements. The itemized final billing statement and remaining balance will be confirmed and locked upon completion of inspection or service execution.
                    </p>
                </div>
            </div>
        </x-card>
    @endif

    {{-- Main Component --}}
    <x-booking-price-breakdown
        :summary="$summary"
        :editable="false"
        :booking="$booking"
    />
</div>
@endsection
