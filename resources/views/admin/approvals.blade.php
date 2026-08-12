@extends('layouts.dashboard')

@section('title', 'Payment Verification | AutoProject+')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Payment Verification</h1>
            <p class="text-gray-600 dark:text-gray-400">Review customer reservation fee payments and confirm bookings.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.jobs.index') }}">
                <x-button variant="primary">
                    <x-icon name="wrench" class="w-4 h-4 mr-2" />
                    Job Assignment
                </x-button>
            </a>
            <a href="{{ route('admin.bookings.history') }}">
                <x-button variant="secondary">Booking History</x-button>
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-card class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pending Verification</p>
            <p class="text-3xl font-bold text-amber-500">{{ $stats['pending_verification'] }}</p>
        </x-card>
        <x-card class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Needs Resubmission</p>
            <p class="text-3xl font-bold text-orange-500">{{ $stats['requires_resubmission'] }}</p>
        </x-card>
        <x-card class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Confirmed Today</p>
            <p class="text-3xl font-bold text-green-500">{{ $stats['confirmed_today'] }}</p>
        </x-card>
        <x-card class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total This Week</p>
            <p class="text-3xl font-bold text-[#457B9D]">{{ $stats['total_week'] }}</p>
        </x-card>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="p-4 rounded-xl bg-green-500/10 border border-green-500/30 text-green-700 dark:text-green-400 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-700 dark:text-red-400 text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter Tabs --}}
    <x-card>
        <div class="flex flex-wrap gap-2">
            @foreach ([
                'pending_payment_verification'  => 'Pending Verification',
                'payment_requires_resubmission' => 'Needs Resubmission',
                'confirmed'                     => 'Confirmed',
                'all'                           => 'All Bookings',
            ] as $filter => $label)
                <a href="{{ route('admin.approvals.index', ['status' => $filter]) }}">
                    <x-button
                        :variant="$selectedFilter === $filter ? 'primary' : 'ghost'"
                        size="sm"
                    >{{ $label }}</x-button>
                </a>
            @endforeach
        </div>
    </x-card>

    {{-- Booking Cards --}}
    <div class="space-y-6">
        @forelse ($bookings as $booking)
            @php
                $quotation    = $booking->quotations->first();
                $payment      = $booking->payments->first();
                $proof        = $payment?->proofs?->first();
                $screenshotUrl = $proof?->url;
                $serviceNames = $booking->bookingServices->pluck('service.name')->join(', ');
                $vehicleLabel = trim(implode(' ', array_filter([
                    $booking->vehicle?->make,
                    $booking->vehicle?->model,
                    $booking->vehicle?->year,
                ])));
                $isPendingVerification  = $booking->status === \App\Models\Booking::STATUS_PENDING_PAYMENT_VERIFICATION;
                $requiresResubmission   = $booking->status === \App\Models\Booking::STATUS_PAYMENT_REQUIRES_RESUBMISSION;
                $isActionable           = $isPendingVerification;
                $maxAttempts            = \App\Services\Booking\PaymentVerificationService::MAX_ATTEMPTS;
            @endphp

            <x-card x-data="{ showRejectForm: false }">
                <div class="space-y-5">

                    {{-- Card Header: Service + Status + Attempt Badge --}}
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-3">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $serviceNames }}</h3>
                                <x-status-badge :status="str_replace('_', '-', $booking->status)">
                                    {{ $booking->badge_label }}
                                </x-status-badge>
                                @if ($booking->payment_attempts > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold
                                        {{ $booking->payment_attempts >= $maxAttempts
                                            ? 'bg-red-500/10 text-red-500 border border-red-500/20'
                                            : 'bg-orange-500/10 text-orange-500 border border-orange-500/20' }}">
                                        Attempt {{ $booking->payment_attempts }} / {{ $maxAttempts }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Submitted {{ $booking->created_at->format('F j, Y') }}
                                &bull; ID: <span class="font-mono font-semibold text-gray-700 dark:text-gray-300">{{ $booking->booking_number }}</span>
                                @if ($booking->is_walk_in)
                                    &bull; <span class="text-xs font-bold text-[#457B9D] uppercase tracking-wide">Walk-In</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    {{-- Customer / Vehicle / Schedule Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 pb-5 border-b border-gray-200 dark:border-white/10">
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Customer</h4>
                            <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $booking->customer_name }}</p>
                            <p class="text-gray-500 text-sm">{{ $booking->contact_number }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Vehicle</h4>
                            <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $vehicleLabel ?: 'N/A' }}</p>
                            <p class="text-gray-500 text-sm">{{ $booking->vehicle?->plate_number }}</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Preferred Schedule</h4>
                            <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $booking->preferred_date->format('F j, Y') }}</p>
                            <p class="text-gray-500 text-sm">{{ \Carbon\Carbon::parse($booking->preferred_time)->format('h:i A') }}</p>
                        </div>
                    </div>

                    {{-- Payment Proof + Info --}}
                    @if ($payment)
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                            {{-- Payment Details --}}
                            <div class="lg:col-span-2 space-y-3">
                                <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Payment Details</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="bg-gray-50 dark:bg-black/20 rounded-xl p-3 border border-gray-200 dark:border-white/5">
                                        <p class="text-xs text-gray-500 mb-1">Amount</p>
                                        <p class="font-bold text-gray-900 dark:text-white text-base">{{ $payment->formatted_amount }}</p>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-black/20 rounded-xl p-3 border border-gray-200 dark:border-white/5">
                                        <p class="text-xs text-gray-500 mb-1">Method</p>
                                        <p class="font-bold text-gray-900 dark:text-white text-base capitalize">{{ $payment->method }}</p>
                                    </div>
                                    <div class="bg-gray-50 dark:bg-black/20 rounded-xl p-3 border border-gray-200 dark:border-white/5">
                                        <p class="text-xs text-gray-500 mb-1">Reference #</p>
                                        <p class="font-mono font-semibold text-gray-900 dark:text-white text-sm break-all select-all">
                                            {{ $payment->reference_number ?? '—' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Rejection reason (shown on already-rejected payments viewed in history) --}}
                                @if ($payment->status === \App\Models\Payment::STATUS_REJECTED && $payment->rejection_reason)
                                    <div class="p-3 bg-red-50 dark:bg-red-950/20 border-l-4 border-red-500 rounded-r-xl">
                                        <p class="text-xs font-bold text-red-700 dark:text-red-400 mb-0.5">Previous Rejection Reason</p>
                                        <p class="text-sm text-red-600 dark:text-red-300">{{ $payment->rejection_reason }}</p>
                                    </div>
                                @endif

                                {{-- Estimated Cost --}}
                                @if ($quotation)
                                    <div class="bg-gradient-red rounded-xl px-5 py-3 text-white flex items-center justify-between shadow-md">
                                        <span class="text-sm text-white/80">Estimated Cost</span>
                                        <span class="text-2xl font-bold">{{ $quotation->total_range_display }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Screenshot Proof --}}
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Payment Proof</h4>
                                @if ($screenshotUrl)
                                    <a href="{{ $screenshotUrl }}" target="_blank"
                                       class="block relative group overflow-hidden rounded-xl border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-lg transition-all bg-gray-50 dark:bg-black/20">
                                        <img src="{{ $screenshotUrl }}"
                                             alt="Payment Screenshot"
                                             class="w-full object-contain max-h-44 rounded-xl transition-transform duration-300 group-hover:scale-105" />
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-xl">
                                            <span class="text-white text-xs font-bold uppercase tracking-wider bg-black/60 px-3 py-1.5 rounded-lg">View Full Size</span>
                                        </div>
                                    </a>
                                @else
                                    <div class="flex flex-col items-center justify-center h-32 rounded-xl border-2 border-dashed border-gray-300 dark:border-white/10 text-gray-400 text-sm bg-gray-50 dark:bg-black/10">
                                        <x-icon name="info" class="w-8 h-8 mb-1" />
                                        No screenshot uploaded
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 italic">No payment submitted yet.</p>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="pt-4 border-t border-gray-200 dark:border-white/10">
                        @if ($isActionable)
                            <div class="flex flex-wrap gap-3 items-start">
                                {{-- Verify & Confirm --}}
                                <form method="POST" action="{{ route('admin.bookings.confirm-payment', $booking) }}">
                                    @csrf
                                    <button type="submit"
                                        onclick="event.preventDefault(); const form = this.closest('form'); window.showConfirm({ title: 'Verify & Confirm Booking', message: 'Confirm payment and approve booking {{ $booking->booking_number }}?', confirmText: 'Verify & Confirm', variant: 'success', onConfirm: () => form.submit() });"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-white bg-green-600 hover:bg-green-700 active:scale-95 transition-all shadow-md shadow-green-500/20">
                                        <x-icon name="check-circle" class="w-5 h-5" />
                                        Verify &amp; Confirm
                                    </button>
                                </form>

                                {{-- Reject Payment Toggle --}}
                                <button type="button"
                                    @click="showRejectForm = !showRejectForm"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-red-500 border border-red-500/30 hover:bg-red-500/10 hover:border-red-500 active:scale-95 transition-all">
                                    <x-icon name="close" class="w-5 h-5" />
                                    Reject Payment
                                </button>

                                {{-- Rejection Reason Form --}}
                                <form
                                    x-show="showRejectForm"
                                    x-cloak
                                    method="POST"
                                    action="{{ route('admin.bookings.reject-payment', $booking) }}"
                                    class="w-full flex flex-col gap-3 mt-1"
                                >
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                            Rejection Reason <span class="text-red-500">*</span>
                                        </label>
                                        <textarea
                                            name="reason"
                                            rows="3"
                                            required
                                            minlength="10"
                                            maxlength="500"
                                            placeholder="Describe the issue clearly (e.g. blurry screenshot, incorrect reference number, amount mismatch)..."
                                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1E1E1E] px-4 py-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-[#E63946] resize-none"
                                        ></textarea>
                                        <p class="text-xs text-gray-400 mt-1">
                                            @if ($booking->payment_attempts + 1 >= \App\Services\Booking\PaymentVerificationService::MAX_ATTEMPTS)
                                                <span class="text-red-500 font-semibold">⚠ Warning: This is the final allowed rejection. The booking will be cancelled automatically.</span>
                                            @else
                                                Attempt {{ $booking->payment_attempts + 1 }} of {{ \App\Services\Booking\PaymentVerificationService::MAX_ATTEMPTS }}.
                                                Customer will be prompted to resubmit.
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit"
                                            class="px-5 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 active:scale-95 transition-all">
                                            Confirm Rejection
                                        </button>
                                        <button type="button" @click="showRejectForm = false"
                                            class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 transition-all">
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
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
                <x-icon name="check-circle" class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
                <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">No bookings found for this filter.</p>
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
