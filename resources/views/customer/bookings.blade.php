@extends('layouts.dashboard')

@section('title', 'My Bookings | AutoProject+')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Pending Verification',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'waiting_payment' => 'Waiting Payment',
        'pending_payment_verification' => 'Pending Verification',
        'payment_requires_resubmission' => 'Payment Rejected',
        'confirmed' => 'Confirmed',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    $filters = ['all', 'pending_payment_verification', 'payment_requires_resubmission', 'confirmed', 'in_progress', 'completed', 'cancelled'];
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
                        @elseif ($filter === 'pending_payment_verification')
                            Pending Verification
                        @elseif ($filter === 'payment_requires_resubmission')
                            Requires Resubmission
                        @else
                            {{ str_replace('_', ' ', $filter) }}
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
                $payment = $booking->payments->sortByDesc('id')->first();
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
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ $booking->preferred_date ? $booking->preferred_date->format('F j, Y') : 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Time</p>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    @if ($booking->preferred_time)
                                        {{ is_string($booking->preferred_time) ? \Carbon\Carbon::parse($booking->preferred_time)->format('h:i A') : $booking->preferred_time->format('h:i A') }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Estimated Cost</p>
                                <p class="font-medium text-[#E63946]">
                                    {{ $quotation?->total_range_display ?? '—' }}
                                </p>
                            </div>
                        </div>

                        @if ($booking->paintPanels->isNotEmpty() || $booking->damagePhotos->isNotEmpty())
                            <div class="pt-2 text-xs text-gray-600 dark:text-gray-400 flex flex-wrap items-center gap-3">
                                @if ($booking->paintPanels->isNotEmpty())
                                    <div>
                                        <span class="font-semibold text-gray-700 dark:text-gray-300">Panels ({{ $booking->paintPanels->count() }}):</span>
                                        <span>{{ $booking->paintPanels->pluck('panel.name')->filter()->join(', ') ?: $booking->paintPanels->pluck('panel_code')->join(', ') }}</span>
                                    </div>
                                @endif
                                @if ($booking->damagePhotos->isNotEmpty())
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-semibold text-gray-700 dark:text-gray-300">Photos:</span>
                                        <div class="flex gap-1.5 items-center">
                                            @foreach ($booking->damagePhotos->take(3) as $photo)
                                                <a href="{{ $photo->url }}" target="_blank" class="block w-6 h-6 rounded overflow-hidden border border-gray-200 dark:border-white/10 hover:scale-110 transition-transform">
                                                    <img src="{{ $photo->url }}" alt="Damage photo" class="w-full h-full object-cover" />
                                                </a>
                                            @endforeach
                                            @if ($booking->damagePhotos->count() > 3)
                                                <span class="text-[10px] text-gray-500 font-medium">+{{ $booking->damagePhotos->count() - 3 }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2 text-sm">
                                @php
                                    try {
                                        $resFee = \App\Models\BusinessSetting::getValue('reservation_fee', 200.00);
                                    } catch (\Throwable $e) {
                                        $resFee = 200.00;
                                    }
                                @endphp
                                <span class="text-gray-700 dark:text-gray-300">Reservation Fee (₱{{ number_format((float) $resFee) }}):</span>
                                @if ($payment && $payment->is_verified)
                                    <span class="flex items-center gap-1 text-green-600 font-medium">
                                        <x-icon name="check-square" class="w-4 h-4 text-green-600 inline" />
                                        Paid & Verified
                                    </span>
                                @elseif ($payment && $payment->status === 'rejected')
                                    <span class="flex items-center gap-1 text-red-600 font-medium">
                                        <x-icon name="x" class="w-4 h-4 text-red-600 inline" />
                                        Payment Rejected
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
                        <x-button
                            variant="primary"
                            size="sm"
                            class="whitespace-nowrap w-full"
                            @click="$dispatch('open-modal', { name: 'customer-booking-details-{{ $booking->id }}' })"
                        >
                            <x-icon name="clipboard-list" class="w-4 h-4 mr-1 inline" />
                            Booking Details
                        </x-button>

                        <a href="{{ route('customer.track', ['booking_id' => $booking->id]) }}">
                            <x-button variant="secondary" size="sm" class="whitespace-nowrap w-full">
                                Track Status
                            </x-button>
                        </a>

                        @if (in_array($booking->status, [\App\Models\Booking::STATUS_CONFIRMED, \App\Models\Booking::STATUS_SCHEDULED, \App\Models\Booking::STATUS_IN_PROGRESS, \App\Models\Booking::STATUS_COMPLETED]))
                            <a href="{{ route('customer.bookings.billing', $booking) }}">
                                <x-button variant="secondary" size="sm" class="whitespace-nowrap w-full">
                                    <x-icon name="receipt" class="w-4 h-4 mr-1 inline" />
                                    View Billing
                                </x-button>
                            </a>
                        @endif

                        @if ($booking->status === \App\Models\Booking::STATUS_COMPLETED)
                            <a href="{{ route('customer.service-history', ['search' => $booking->booking_number]) }}">
                                <x-button variant="outline" size="sm" class="whitespace-nowrap w-full text-green-600 border-green-600/30 hover:bg-green-600/10">
                                    <x-icon name="clipboard-check" class="w-4 h-4 mr-1 inline" />
                                    Service Record
                                </x-button>
                            </a>
                        @endif

                        <a href="{{ route('customer.support.index') }}?subject={{ urlencode('Issue with '.$booking->booking_number.' - '.$serviceNames) }}">
                            <x-button variant="secondary" size="sm" class="whitespace-nowrap">
                                <x-icon name="message-square" class="w-4 h-4 mr-1 inline text-gray-500" />
                                Contact Support
                            </x-button>
                        </a>

                        @if ($booking->status === \App\Models\Booking::STATUS_PAYMENT_REQUIRES_RESUBMISSION)
                            <a href="{{ route('customer.payment', $booking->id) }}">
                                <x-button variant="accent" size="sm" class="whitespace-nowrap w-full text-white bg-red-600 hover:bg-red-700">
                                    Resubmit Payment
                                </x-button>
                            </a>
                        @elseif ($payment)
                            <a href="{{ route('customer.payment', $booking->id) }}">
                                <x-button variant="secondary" size="sm" class="whitespace-nowrap w-full">
                                    Payment Details
                                </x-button>
                            </a>
                        @endif

                        @if ($booking->is_cancellable)
                            <form
                                method="POST"
                                action="{{ route('customer.bookings.destroy', $booking) }}"
                                onsubmit="event.preventDefault(); const form = this; window.showConfirm({ title: 'Cancel Booking', message: 'Are you sure you want to cancel booking {{ $booking->booking_number }}? Please note that the reservation fee is non-refundable. This action cannot be undone.', confirmText: 'Yes, Cancel Booking', variant: 'danger', onConfirm: () => form.submit() });"
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

            {{-- Full Booking Details Modal --}}
            <x-modal name="customer-booking-details-{{ $booking->id }}" maxWidth="3xl" title="Booking Details — {{ $booking->booking_number }}">
                <div class="space-y-5 text-sm">
                    {{-- Status & Schedule Banner --}}
                    <div class="flex items-center justify-between flex-wrap gap-3 p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Status</p>
                            <div class="mt-1">
                                <x-status-badge :status="$badgeStatus">
                                    {{ $statusLabels[$booking->status] ?? $booking->display_status }}
                                </x-status-badge>
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Appointment Schedule</p>
                            <p class="font-bold text-gray-900 dark:text-white mt-1">
                                {{ $booking->preferred_date ? $booking->preferred_date->format('M d, Y') : 'N/A' }}
                                @if ($booking->preferred_time)
                                    • {{ is_string($booking->preferred_time) ? \Carbon\Carbon::parse($booking->preferred_time)->format('h:i A') : $booking->preferred_time->format('h:i A') }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Estimated Total</p>
                            <p class="font-bold text-[#E63946] mt-1">
                                {{ $quotation?->total_range_display ?? 'To be computed' }}
                            </p>
                        </div>
                    </div>

                    {{-- Vehicle & Services Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-zinc-900/50 space-y-2">
                            <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <x-icon name="car" class="w-4 h-4 text-gray-500" />
                                Vehicle Information
                            </h4>
                            <div class="text-xs space-y-1 text-gray-600 dark:text-gray-400">
                                <p><span class="font-medium text-gray-700 dark:text-gray-300">Vehicle:</span> {{ $vehicleLabel }}</p>
                                <p><span class="font-medium text-gray-700 dark:text-gray-300">Plate Number:</span> {{ $booking->vehicle?->plate_number ?? 'N/A' }}</p>
                                <p><span class="font-medium text-gray-700 dark:text-gray-300">Contact Number:</span> {{ $booking->contact_number ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-zinc-900/50 space-y-2">
                            <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <x-icon name="wrench" class="w-4 h-4 text-gray-500" />
                                Services Requested
                            </h4>
                            <div class="text-xs space-y-1.5 text-gray-600 dark:text-gray-400">
                                @foreach ($booking->bookingServices as $bs)
                                    <div class="flex items-center justify-between">
                                        <span class="font-medium text-gray-800 dark:text-gray-200">{{ $bs->service->name }}</span>
                                        @if ($bs->brand_preference)
                                            <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-white/10 text-[10px] text-gray-700 dark:text-gray-300">
                                                {{ $bs->brand_preference }}
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @if ($booking->notes)
                        <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/[0.02]">
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Customer Notes / Instructions</h4>
                            <p class="text-gray-800 dark:text-gray-200 text-xs leading-relaxed">{{ $booking->notes }}</p>
                        </div>
                    @endif

                    {{-- Paint Scope & Inspected Panels --}}
                    @if ($booking->paintPanels->isNotEmpty() || $booking->paintScope)
                        <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-zinc-900/50 space-y-3">
                            <div class="flex items-center justify-between flex-wrap gap-2 pb-2 border-b border-gray-100 dark:border-white/5">
                                <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <x-icon name="tool" class="w-4 h-4 text-[#E63946]" />
                                    Paint Scope & Damage Inspection
                                </h4>
                                @if ($booking->paintScope)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#E63946]/10 text-[#E63946] border border-[#E63946]/20 capitalize">
                                        {{ str_replace('_', ' ', $booking->paintScope->scope_mode) }}
                                    </span>
                                @endif
                            </div>

                            @if ($booking->paintScope && ($booking->paintScope->color_code || $booking->paintScope->finish_type))
                                <div class="flex gap-4 text-xs text-gray-600 dark:text-gray-400">
                                    @if ($booking->paintScope->color_code)
                                        <p><span class="font-medium text-gray-700 dark:text-gray-300">Color Code:</span> {{ $booking->paintScope->color_code }}</p>
                                    @endif
                                    @if ($booking->paintScope->finish_type)
                                        <p><span class="font-medium text-gray-700 dark:text-gray-300">Finish:</span> {{ ucfirst($booking->paintScope->finish_type) }}</p>
                                    @endif
                                </div>
                            @endif

                            @if ($booking->paintPanels->isNotEmpty())
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 font-medium">Panels Selected for Inspection ({{ $booking->paintPanels->count() }}):</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($booking->paintPanels as $panel)
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-50 dark:bg-white/5 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-white/10 shadow-xs">
                                                <span class="font-semibold">{{ $panel->panel->name ?? ucwords(str_replace('_', ' ', $panel->panel_code)) }}</span>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider
                                                    {{ in_array($panel->severity, ['dent', 'body_work', 'replace_panel']) ? 'bg-red-500/10 text-red-600 border border-red-500/20' : ($panel->severity === 'light_scratch' ? 'bg-amber-500/10 text-amber-600 border border-amber-500/20' : 'bg-blue-500/10 text-blue-600 border border-blue-500/20') }}">
                                                    {{ $panel->severity_label }}
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- CUSTOMER UPLOADS SECTION --}}
                    <div class="space-y-4">
                        <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-icon name="camera" class="w-4 h-4 text-gray-500" />
                            Customer Uploads
                        </h4>

                        {{-- 1. Damage Reference Photos --}}
                        <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-zinc-900/50 space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                                    Damage Reference Photos ({{ $booking->damagePhotos->count() }})
                                </p>
                                <span class="text-[11px] text-gray-500">Click any photo to view full resolution</span>
                            </div>

                            @if ($booking->damagePhotos->isNotEmpty())
                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                    @foreach ($booking->damagePhotos as $photo)
                                        <a href="{{ $photo->url }}" target="_blank" class="block relative group overflow-hidden rounded-xl border border-gray-200 dark:border-white/10 bg-black aspect-square shadow-sm hover:shadow-md transition-all">
                                            <img src="{{ $photo->url }}" alt="{{ $photo->original_name }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                                <span class="text-white text-[11px] font-bold uppercase tracking-wider bg-black/60 px-2 py-1 rounded">View</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-gray-500 italic py-2">No damage photos attached to this booking.</p>
                            @endif
                        </div>

                        {{-- 2. Payment Proof Screenshot --}}
                        <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-zinc-900/50 space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Reservation Fee Payment Receipt</p>
                                @if ($payment)
                                    <span class="text-[11px] font-mono px-2 py-0.5 rounded bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300">
                                        Ref: {{ $payment->reference_number ?? 'N/A' }}
                                    </span>
                                @endif
                            </div>

                            @php
                                $proofPhoto = $payment?->proofs->first();
                            @endphp

                            @if ($proofPhoto)
                                <div class="flex items-start gap-4 flex-wrap sm:flex-nowrap">
                                    <a href="{{ $proofPhoto->url }}" target="_blank" class="block flex-shrink-0 w-24 h-24 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 bg-black relative group shadow-sm hover:shadow-md">
                                        <img src="{{ $proofPhoto->url }}" alt="Payment proof" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <span class="text-white text-[10px] font-bold uppercase tracking-wider bg-black/60 px-1.5 py-0.5 rounded">View</span>
                                        </div>
                                    </a>
                                    <div class="text-xs space-y-1.5 text-gray-600 dark:text-gray-400 flex-1">
                                        <p><span class="font-medium text-gray-700 dark:text-gray-300">Method:</span> {{ strtoupper($payment->method) }}</p>
                                        <p><span class="font-medium text-gray-700 dark:text-gray-300">Amount:</span> ₱{{ number_format((float) $payment->amount, 2) }}</p>
                                        <p><span class="font-medium text-gray-700 dark:text-gray-300">Date:</span> {{ $payment->paid_at ? $payment->paid_at->format('M d, Y h:i A') : 'N/A' }}</p>
                                        <p>
                                            <span class="font-medium text-gray-700 dark:text-gray-300">Status:</span>
                                            @if ($payment->is_verified)
                                                <span class="text-green-600 font-semibold">Verified</span>
                                            @elseif ($payment->status === 'rejected')
                                                <span class="text-red-600 font-semibold">Rejected</span>
                                            @else
                                                <span class="text-amber-600 font-semibold">Pending Verification</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            @else
                                <p class="text-xs text-gray-500 italic py-2">
                                    {{ $payment && $payment->method === 'cash' ? 'In-Store Cash Payment recorded.' : 'No payment screenshot uploaded.' }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <x-slot:footer>
                    <div class="flex items-center justify-between w-full">
                        <div>
                            @if (in_array($booking->status, [\App\Models\Booking::STATUS_CONFIRMED, \App\Models\Booking::STATUS_SCHEDULED, \App\Models\Booking::STATUS_IN_PROGRESS, \App\Models\Booking::STATUS_COMPLETED]))
                                <a href="{{ route('customer.bookings.billing', $booking) }}">
                                    <x-button variant="primary" size="sm">
                                        <x-icon name="receipt" class="w-4 h-4 mr-1 inline" />
                                        View Billing Breakdown
                                    </x-button>
                                </a>
                            @endif
                        </div>
                        <x-button variant="secondary" size="sm" x-on:click="show = false">
                            Close
                        </x-button>
                    </div>
                </x-slot:footer>
            </x-modal>
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
