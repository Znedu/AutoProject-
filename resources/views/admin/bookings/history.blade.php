@extends('layouts.dashboard')

@section('title', 'Booking History | AutoProject+')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Booking History</h1>
        <p class="text-gray-600 dark:text-gray-400">Complete record of all customer booking requests.</p>
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
                        {{ $filter === 'all' ? 'All Bookings' : ucfirst($filter) }}
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
                    <x-table-head class="text-right">Actions</x-table-head>
                </x-table-row>
            </x-table-header>
            <x-table-body>
                @forelse ($bookings as $booking)
                    @php
                        $finalQuotation = $booking->quotations->where('type', \App\Models\Quotation::TYPE_FINAL)->first();
                        $quotation = $finalQuotation ?? $booking->quotations->first();
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
                                <p class="text-xs text-[#E63946] font-semibold mt-1">
                                    {{ $quotation->total_range_display }}
                                    @if ($quotation->isFinalized())
                                        <span class="text-[10px] text-green-600 ml-1 font-normal">(Finalized)</span>
                                    @endif
                                </p>
                            @endif

                            @if ($booking->paintPanels->isNotEmpty() || $booking->damagePhotos->isNotEmpty())
                                <div class="mt-2 text-xs flex flex-wrap items-center gap-2">
                                    @if ($booking->paintPanels->isNotEmpty())
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-white/10">
                                            {{ $booking->paintPanels->count() }} {{ \Illuminate\Support\Str::plural('Panel', $booking->paintPanels->count()) }}
                                        </span>
                                    @endif
                                    @if ($booking->damagePhotos->isNotEmpty())
                                        <div class="flex items-center gap-1">
                                            @foreach ($booking->damagePhotos->take(3) as $photo)
                                                <a href="{{ $photo->url }}" target="_blank" class="block w-6 h-6 rounded overflow-hidden border border-gray-200 dark:border-white/10 hover:scale-110 transition-transform shadow-xs">
                                                    <img src="{{ $photo->url }}" alt="Damage photo" class="w-full h-full object-cover" />
                                                </a>
                                            @endforeach
                                            @if ($booking->damagePhotos->count() > 3)
                                                <span class="text-[10px] text-gray-500 font-medium">+{{ $booking->damagePhotos->count() - 3 }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
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
                        <x-table-cell class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <x-button
                                    variant="secondary"
                                    size="sm"
                                    @click="$dispatch('open-modal', { name: 'admin-booking-details-{{ $booking->id }}' })"
                                >
                                    <x-icon name="clipboard-list" class="w-3.5 h-3.5 mr-1 text-gray-500" />
                                    Details
                                </x-button>

                                <a href="{{ route('admin.bookings.billing.show', $booking) }}">
                                    <x-button variant="secondary" size="sm">
                                        <x-icon name="receipt" class="w-3.5 h-3.5 mr-1 text-[#E63946]" />
                                        Billing
                                    </x-button>
                                </a>
                            </div>
                        </x-table-cell>
                    </x-table-row>
                @empty
                    <x-table-row>
                        <x-table-cell colspan="7">
                            <p class="text-center py-8 text-gray-500">No bookings found.</p>
                        </x-table-cell>
                    </x-table-row>
                @endforelse
            </x-table-body>
        </x-table>
    </x-card>

    {{-- Admin Booking Details Modals --}}
    @foreach ($bookings as $booking)
        @php
            $finalQuotation = $booking->quotations->where('type', \App\Models\Quotation::TYPE_FINAL)->first();
            $quotation = $finalQuotation ?? $booking->quotations->first();
            $payment = $booking->payments->where('type', \App\Models\Payment::TYPE_RESERVATION_FEE)->sortByDesc('id')->first() ?? $booking->payments->sortByDesc('id')->first();
            $vehicleLabel = trim(implode(' ', array_filter([
                $booking->vehicle?->make,
                $booking->vehicle?->model,
                $booking->vehicle?->year,
            ])));
        @endphp
        <x-modal name="admin-booking-details-{{ $booking->id }}" maxWidth="3xl" title="Booking Details — {{ $booking->booking_number }}">
            <div class="space-y-5 text-sm text-left">
                {{-- Status & Meta Banner --}}
                <div class="flex items-center justify-between flex-wrap gap-3 p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Status</p>
                        <div class="mt-1 flex items-center gap-2">
                            <x-status-badge :status="$booking->status">{{ $booking->display_status }}</x-status-badge>
                            @if ($booking->is_walk_in)
                                <span class="px-2 py-0.5 text-xs font-semibold rounded bg-[#D2781A]/10 text-[#D2781A] border border-[#D2781A]/20">Walk-In</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Schedule Slot</p>
                        <p class="font-bold text-gray-900 dark:text-white mt-1">
                            {{ $booking->preferred_date ? $booking->preferred_date->format('M d, Y') : 'N/A' }}
                            @if ($booking->preferred_time)
                                • {{ \Carbon\Carbon::parse($booking->preferred_time)->format('h:i A') }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium uppercase tracking-wider">Quotation Total</p>
                        <p class="font-bold text-[#E63946] mt-1">
                            {{ $quotation?->total_range_display ?? 'Pending Estimate' }}
                        </p>
                    </div>
                </div>

                {{-- Customer & Vehicle Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-zinc-900/50 space-y-2">
                        <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-icon name="user" class="w-4 h-4 text-gray-500" />
                            Customer Details
                        </h4>
                        <div class="text-xs space-y-1 text-gray-600 dark:text-gray-400">
                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Name:</span> {{ $booking->customer_name }}</p>
                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Email:</span> {{ $booking->user?->email ?? 'N/A' }}</p>
                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Phone:</span> {{ $booking->contact_number }}</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-zinc-900/50 space-y-2">
                        <h4 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <x-icon name="car" class="w-4 h-4 text-gray-500" />
                            Vehicle Details
                        </h4>
                        <div class="text-xs space-y-1 text-gray-600 dark:text-gray-400">
                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Vehicle:</span> {{ $vehicleLabel ?: 'Unknown' }}</p>
                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Plate Number:</span> {{ $booking->vehicle?->plate_number ?? 'N/A' }}</p>
                            <p><span class="font-medium text-gray-700 dark:text-gray-300">Submitted:</span> {{ $booking->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Services Requested --}}
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

                @if ($booking->notes)
                    <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/[0.02]">
                        <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Customer Notes</h4>
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
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2 font-medium">Panels Inspected ({{ $booking->paintPanels->count() }}):</p>
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
                    <a href="{{ route('admin.bookings.billing.show', $booking) }}">
                        <x-button variant="primary" size="sm">
                            <x-icon name="receipt" class="w-4 h-4 mr-1 inline" />
                            Manage Billing & Quotation
                        </x-button>
                    </a>
                    <x-button variant="secondary" size="sm" x-on:click="show = false">
                        Close
                    </x-button>
                </div>
            </x-slot:footer>
        </x-modal>
    @endforeach

    <div>
        {{ $bookings->links() }}
    </div>
</div>
@endsection
