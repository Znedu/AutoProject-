@extends('layouts.dashboard')

@section('title', 'Booking Approval | AutoProject+')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Booking Approval</h1>
            <p class="text-gray-600 dark:text-gray-400">Review and approve customer booking requests.</p>
        </div>
        <a href="{{ route('admin.bookings.history') }}">
            <x-button variant="secondary">Booking History</x-button>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Pending Approval</p>
            <p class="text-3xl font-bold text-[#E63946]">{{ $stats['pending'] }}</p>
        </x-card>
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Approved Today</p>
            <p class="text-3xl font-bold text-green-500">{{ $stats['approved_today'] }}</p>
        </x-card>
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Rejected</p>
            <p class="text-3xl font-bold text-gray-500">{{ $stats['rejected'] }}</p>
        </x-card>
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total This Week</p>
            <p class="text-3xl font-bold text-[#457B9D]">{{ $stats['total_week'] }}</p>
        </x-card>
    </div>

    <x-card>
        <div class="flex flex-wrap gap-2">
            @foreach (['pending', 'all', 'approved', 'rejected'] as $filter)
                <a href="{{ route('admin.approvals.index', ['status' => $filter]) }}">
                    <x-button
                        :variant="$selectedFilter === $filter ? 'primary' : 'ghost'"
                        size="sm"
                        class="capitalize"
                    >
                        @if ($filter === 'pending')
                            Pending Approval
                        @elseif ($filter === 'all')
                            All Bookings
                        @else
                            {{ $filter }}
                        @endif
                    </x-button>
                </a>
            @endforeach
        </div>
    </x-card>

    <div class="space-y-6">
        @forelse ($bookings as $booking)
            @php
                $quotation = $booking->quotations->first();
                $payment = $booking->payments->first();
                $serviceNames = $booking->bookingServices->pluck('service.name')->join(', ');
                $vehicleLabel = trim(implode(' ', array_filter([
                    $booking->vehicle?->make,
                    $booking->vehicle?->model,
                    $booking->vehicle?->year,
                ])));
                $paymentVerified = $payment?->is_verified ?? false;
            @endphp

            <x-card x-data="{ showRejectForm: false }">
                <div class="space-y-6">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3 mb-3">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $serviceNames }}</h3>
                                <x-status-badge status="pending">Awaiting Approval</x-status-badge>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Submitted: {{ $booking->created_at->format('F j, Y') }} • Booking ID: {{ $booking->booking_number }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b border-gray-200 dark:border-white/10">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-3">Customer Information</h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500">Name</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $booking->customer_name }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Contact</p>
                                    <p class="text-gray-700 dark:text-gray-300">{{ $booking->contact_number }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-3">Vehicle Details</h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500">Vehicle</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $vehicleLabel }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Plate Number</p>
                                    <p class="text-gray-700 dark:text-gray-300">{{ $booking->vehicle?->plate_number }}</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-3">Preferred Schedule</h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500">Date</p>
                                    <p class="font-medium text-gray-900 dark:text-white">{{ $booking->preferred_date->format('F j, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Time</p>
                                    <p class="text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($booking->preferred_time)->format('h:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-3">Service Notes</h4>
                            <div class="bg-gray-50 dark:bg-black/20 rounded-xl p-4 border border-gray-200 dark:border-white/5">
                                <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed">
                                    {{ $booking->notes ?: 'No additional notes provided.' }}
                                </p>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-3">Cost Estimation</h4>
                            <div class="bg-gradient-red rounded-xl p-6 text-white shadow-lg shadow-[#E63946]/10">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <x-icon name="dollar-sign" class="w-8 h-8" />
                                        <div>
                                            <p class="text-xs text-white/80">Estimated Cost</p>
                                            <p class="text-3xl font-bold">{{ $quotation?->total_range_display ?? '—' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($payment)
                        <div class="bg-blue-500/5 dark:bg-blue-500/10 border-2 border-blue-500/30 rounded-xl p-6">
                            <div class="flex items-center gap-2 mb-4 flex-wrap">
                                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Reservation Fee Payment Verification</h4>
                                <div class="ml-auto">
                                    @if ($paymentVerified)
                                        <span class="flex items-center gap-1.5 px-3 py-1 bg-green-500/10 text-green-500 border border-green-500/20 rounded-full text-xs font-semibold">
                                            <x-icon name="check-circle" class="w-4 h-4" />
                                            Verified
                                        </span>
                                    @else
                                        <span class="flex items-center gap-1.5 px-3 py-1 bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 rounded-full text-xs font-semibold">
                                            <x-icon name="info" class="w-4 h-4" />
                                            Pending Verification
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="bg-white dark:bg-[#1E1E1E] rounded-xl p-4 border border-gray-200 dark:border-white/5 shadow-sm">
                                    <p class="text-xs text-gray-500 mb-1">Amount</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $payment->formatted_amount }}</p>
                                </div>
                                <div class="bg-white dark:bg-[#1E1E1E] rounded-xl p-4 border border-gray-200 dark:border-white/5 shadow-sm">
                                    <p class="text-xs text-gray-500 mb-1">Payment Method</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white capitalize">{{ $payment->method }}</p>
                                </div>
                                <div class="bg-white dark:bg-[#1E1E1E] rounded-xl p-4 border border-gray-200 dark:border-white/5 shadow-sm">
                                    <p class="text-xs text-gray-500 mb-1">Reference Number</p>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white break-all select-all">{{ $payment->reference_number }}</p>
                                </div>
                            </div>

                            @unless ($paymentVerified)
                                <form method="POST" action="{{ route('admin.bookings.verify-payment', $booking) }}" class="mb-4">
                                    @csrf
                                    <x-button variant="primary" type="submit" class="w-full md:w-auto">
                                        <x-icon name="check-circle" class="w-5 h-5 mr-2" />
                                        Verify Payment
                                    </x-button>
                                </form>
                            @endunless
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-200 dark:border-white/10">
                        @if ($booking->status === \App\Models\Booking::STATUS_PENDING)
                            <form method="POST" action="{{ route('admin.bookings.approve', $booking) }}">
                                @csrf
                                @if ($paymentVerified)
                                    <x-button variant="accent" type="submit">
                                        <x-icon name="check-circle" class="w-5 h-5 mr-2" />
                                        Approve Booking
                                    </x-button>
                                @else
                                    <x-button variant="accent" type="button" disabled class="opacity-50 cursor-not-allowed">
                                        <x-icon name="check-circle" class="w-5 h-5 mr-2" />
                                        Approve Booking
                                    </x-button>
                                @endif
                            </form>

                            <x-button
                                variant="outline"
                                type="button"
                                class="text-red-500 border-red-500/20 hover:bg-red-500/10 hover:border-red-500"
                                @click="showRejectForm = !showRejectForm"
                            >
                                <x-icon name="close" class="w-5 h-5 mr-2" />
                                Reject Booking
                            </x-button>

                            <form
                                x-show="showRejectForm"
                                x-cloak
                                method="POST"
                                action="{{ route('admin.bookings.reject', $booking) }}"
                                class="w-full flex flex-col gap-3 mt-2"
                            >
                                @csrf
                                <textarea
                                    name="reason"
                                    rows="3"
                                    required
                                    placeholder="Provide a reason for rejection..."
                                    class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1E1E1E] px-4 py-3 text-sm text-gray-900 dark:text-white"
                                ></textarea>
                                <x-button variant="outline" type="submit" class="text-red-500 border-red-500/20 hover:bg-red-500/10 w-full md:w-auto">
                                    Confirm Rejection
                                </x-button>
                            </form>
                        @else
                            <x-status-badge :status="str_replace('_', '-', $booking->status)">
                                {{ $booking->display_status }}
                            </x-status-badge>
                        @endif
                    </div>
                </div>
            </x-card>
        @empty
            <x-card class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400 text-lg">No booking approvals found for this filter.</p>
            </x-card>
        @endforelse
    </div>

    @if ($bookings->hasPages())
        <div class="pt-2">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection
