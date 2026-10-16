@extends('layouts.dashboard')

@section('title', 'Vehicle Service Records & History | AutoProject+')

@section('content')
<div
    x-data="{
        activeRecord: null,
        showDetailModal: false,
        openModal(record) {
            this.activeRecord = record;
            this.showDetailModal = true;
        },
        closeModal() {
            this.showDetailModal = false;
            this.activeRecord = null;
        }
    }"
    class="space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-3">
                <x-icon name="history" class="w-8 h-8 text-[#E63946]" />
                Vehicle Service Records & History
            </h1>
            <p class="text-gray-600 dark:text-gray-400">Official maintenance logs, customization specs, mechanic notes, and billing history for your vehicles.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('customer.vehicles.index') }}">
                <x-button variant="outline" size="sm" class="flex items-center gap-2">
                    <x-icon name="car" class="w-4 h-4" />
                    Manage Vehicles
                </x-button>
            </a>
            <a href="{{ route('customer.book-service') }}">
                <x-button variant="accent" size="sm" class="flex items-center gap-2 text-white">
                    <x-icon name="plus" class="w-4 h-4" />
                    Book New Service
                </x-button>
            </a>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-card class="bg-gradient-to-br from-green-500/10 via-green-500/5 to-transparent border-green-500/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Completed Services</p>
                    <p class="text-3xl font-extrabold text-green-600 dark:text-green-400">{{ $stats['completedCount'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Official service records</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-green-500/20 text-green-600 dark:text-green-400 flex items-center justify-center">
                    <x-icon name="check-circle" class="w-6 h-6" />
                </div>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-[#E63946]/10 via-[#E63946]/5 to-transparent border-[#E63946]/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Total Invested</p>
                    <p class="text-3xl font-extrabold text-[#E63946]">₱{{ number_format($stats['totalSpent'], 2) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Lifetime payments verified</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#E63946]/20 text-[#E63946] flex items-center justify-center">
                    <x-icon name="dollar-sign" class="w-6 h-6" />
                </div>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-[#457B9D]/10 via-[#457B9D]/5 to-transparent border-[#457B9D]/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Vehicles Serviced</p>
                    <p class="text-3xl font-extrabold text-[#457B9D]">{{ $stats['servicedVehiclesCount'] }}</p>
                    <p class="text-xs text-gray-500 mt-1">Across {{ $vehicles->count() }} registered vehicles</p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-[#457B9D]/20 text-[#457B9D] flex items-center justify-center">
                    <x-icon name="car" class="w-6 h-6" />
                </div>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-amber-500/10 via-amber-500/5 to-transparent border-amber-500/20">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Latest Service</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-white truncate">
                        {{ $stats['latestServiceDate'] ? \Carbon\Carbon::parse($stats['latestServiceDate'])->format('M d, Y') : 'None yet' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $stats['latestServiceDate'] ? \Carbon\Carbon::parse($stats['latestServiceDate'])->diffForHumans() : 'No history' }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-500 flex items-center justify-center">
                    <x-icon name="calendar" class="w-6 h-6" />
                </div>
            </div>
        </x-card>
    </div>

    {{-- Filter & Search Bar --}}
    <x-card class="p-4 sm:p-5">
        <form method="GET" action="{{ route('customer.service-history') }}" class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
            {{-- Vehicle Selector --}}
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Select Vehicle</label>
                    <select
                        name="vehicle_id"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                    >
                        <option value="">All Registered Vehicles</option>
                        @foreach ($vehicles as $v)
                            <option value="{{ $v->id }}" {{ (string) $selectedVehicleId === (string) $v->id ? 'selected' : '' }}>
                                {{ $v->display_name }} ({{ $v->plate_number }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Record Status</label>
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                    >
                        <option value="completed" {{ $selectedStatus === 'completed' ? 'selected' : '' }}>Completed Services (Official Log)</option>
                        <option value="in_progress" {{ $selectedStatus === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>All Service Bookings</option>
                    </select>
                </div>

                {{-- Keyword Search --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Search Records</label>
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Service name, plate, booking #..."
                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1A1A1A] text-gray-900 dark:text-white pl-9 pr-3 py-2 text-sm focus:ring-2 focus:ring-[#E63946] focus:outline-none"
                        >
                        <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" />
                    </div>
                </div>
            </div>

            <div class="flex items-end gap-2">
                <x-button type="submit" variant="primary" size="sm" class="h-[38px] px-4">
                    Filter
                </x-button>
                @if ($selectedVehicleId || $selectedStatus !== 'completed' || $search)
                    <a href="{{ route('customer.service-history') }}">
                        <x-button type="button" variant="ghost" size="sm" class="h-[38px]">
                            Reset
                        </x-button>
                    </a>
                @endif
            </div>
        </form>
    </x-card>

    {{-- Service Records Listing --}}
    @if ($records->isEmpty())
        <x-card class="text-center py-12">
            <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-white/5 flex items-center justify-center mx-auto mb-4 text-gray-400">
                <x-icon name="clipboard-check" class="w-8 h-8" />
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No Service Records Found</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-6">
                @if ($selectedVehicleId || $search || $selectedStatus !== 'completed')
                    No maintenance records match your current filter criteria. Try resetting your search filters.
                @else
                    You don't have any completed service records logged yet. Once our mechanics complete work on your vehicle, full logs, inspection notes, and receipts will appear here.
                @endif
            </p>
            <a href="{{ route('customer.book-service') }}">
                <x-button variant="accent" class="text-white">
                    Book a Service Today
                </x-button>
            </a>
        </x-card>
    @else
        <div class="space-y-4">
            @foreach ($records as $record)
                @php
                    $vehicle = $record->vehicle;
                    $services = $record->bookingServices;
                    $serviceNames = $services->pluck('service.name')->filter()->join(', ');
                    if (empty($serviceNames)) {
                        $serviceNames = $record->services->pluck('name')->join(', ') ?: 'Custom Automotive Service';
                    }
                    $job = $record->jobOrder;
                    $mechanic = $job?->mechanic;
                    $summary = $record->billingSummary;
                    $completedDate = $record->completed_at ? $record->completed_at->format('M d, Y') : ($record->scheduled_date ? $record->scheduled_date->format('M d, Y') : $record->created_at->format('M d, Y'));
                    $isCompleted = $record->status === \App\Models\Booking::STATUS_COMPLETED;
                    $hasPaint = (bool) $record->paintScope || $record->paintPanels->isNotEmpty();

                    // Pre-encode data for Alpine detailed modal
                    $modalPayload = [
                        'id' => $record->id,
                        'booking_number' => $record->booking_number,
                        'status' => $record->status,
                        'display_status' => $record->display_status,
                        'date' => $completedDate,
                        'vehicle_display' => $vehicle ? $vehicle->display_name : 'Vehicle Not Specified',
                        'plate_number' => $vehicle?->plate_number ?? 'N/A',
                        'vehicle_color' => $vehicle?->color ?? 'N/A',
                        'services' => $services->map(fn ($bs) => [
                            'name' => $bs->service?->name ?? 'Custom Service',
                            'brand' => $bs->preferred_brand ?? 'Standard OEM',
                            'category' => $bs->service?->category?->name ?? 'Customization',
                        ])->values()->all(),
                        'mechanic_name' => $mechanic?->name ?? 'Unassigned',
                        'mechanic_phone' => $mechanic?->phone ?? 'N/A',
                        'job_number' => $job?->job_number ?? 'N/A',
                        'internal_notes' => $job?->internal_notes ?? $record->notes ?? 'Standard shop procedures performed.',
                        'paint_scope' => $record->paintScope ? [
                            'scope_mode' => $record->paintScope->scope_mode,
                            'color_code' => $record->paintScope->color_code,
                            'finish_type' => $record->paintScope->finish_type,
                            'notes' => $record->paintScope->notes,
                        ] : null,
                        'paint_panels' => $record->paintPanels->map(fn ($p) => [
                            'name' => $p->panel?->name ?? ucwords(str_replace('_', ' ', $p->panel_code)),
                            'severity' => $p->severity_label ?? ucfirst(str_replace('_', ' ', $p->severity ?? '')),
                        ])->values()->all(),
                        'damage_photos' => $record->damagePhotos->map(fn ($d) => [
                            'url' => $d->url,
                            'name' => $d->original_name,
                        ])->values()->all(),
                        'service_updates' => $job ? $job->serviceUpdates->map(fn ($u) => [
                            'notes' => $u->notes,
                            'date' => $u->created_at->format('M d, Y h:i A'),
                            'photos' => $u->photos->pluck('photo_url')->all(),
                        ])->values()->all() : [],
                        'billing' => [
                            'final_total' => $summary->finalTotal,
                            'reservation_fee_paid' => $summary->reservationFeePaid,
                            'total_paid' => $summary->totalPaid,
                            'balance_due' => $summary->balanceDue,
                            'is_finalized' => $summary->isFinalized,
                        ],
                        'invoice_url' => route('customer.bookings.billing', $record),
                        'print_url' => route('customer.service-history.print', $record),
                    ];
                @endphp

                <x-card class="hover:border-[#E63946]/40 transition-all duration-300">
                    <div class="flex flex-col lg:flex-row justify-between gap-6">
                        {{-- Left Column: Vehicle & Service Details --}}
                        <div class="flex-1 space-y-4">
                            {{-- Header row with Vehicle & Status --}}
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-[#E63946]/10 text-[#E63946] border border-[#E63946]/20">
                                            <x-icon name="car" class="w-3.5 h-3.5" />
                                            {{ $vehicle ? $vehicle->display_name : 'Vehicle' }}
                                        </span>
                                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300">
                                            {{ $vehicle?->plate_number ?? 'NO PLATE' }}
                                        </span>
                                        @if ($vehicle?->color)
                                            <span class="text-xs text-gray-500">🎨 {{ $vehicle->color }}</span>
                                        @endif
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mt-1">
                                        {{ $serviceNames }}
                                    </h3>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Record #{{ $record->booking_number }} • Service Date: <strong class="text-gray-700 dark:text-gray-300">{{ $completedDate }}</strong>
                                    </p>
                                </div>

                                <div>
                                    <x-status-badge :status="str_replace('_', '-', $record->status)">
                                        {{ $record->display_status }}
                                    </x-status-badge>
                                </div>
                            </div>

                            {{-- Services Tags --}}
                            <div class="flex flex-wrap gap-2">
                                @foreach ($services as $bs)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-800 dark:text-gray-200">
                                        <x-icon name="wrench" class="w-3 h-3 text-[#E63946]" />
                                        <span class="font-semibold">{{ $bs->service?->name ?? 'Custom Service' }}</span>
                                        @if ($bs->preferred_brand)
                                            <span class="text-gray-400">({{ $bs->preferred_brand }})</span>
                                        @endif
                                    </div>
                                @endforeach

                                @if ($hasPaint)
                                    <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 font-medium">
                                        <span>🎨 Paint / Bodywork Scope Included</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Work Crew & Notes snippet --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-3 border-t border-gray-100 dark:border-white/5 text-xs">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300 flex items-center justify-center font-bold">
                                        {{ $mechanic ? substr($mechanic->name, 0, 1) : 'M' }}
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Assigned Mechanic</p>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $mechanic?->name ?? 'Workshop Team' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-green-500/10 text-green-600 dark:text-green-400 flex items-center justify-center font-bold">
                                        <x-icon name="shield" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <p class="text-gray-500">Service Guarantee</p>
                                        <p class="font-semibold text-gray-900 dark:text-white">Shop Verified & Logged</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Financial Breakdown & Actions --}}
                        <div class="lg:w-72 flex flex-col justify-between p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                            <div class="space-y-2">
                                <div class="flex justify-between items-baseline text-xs text-gray-500">
                                    <span>Total Charges:</span>
                                    <span class="font-mono text-sm font-bold text-gray-900 dark:text-white">₱{{ number_format($summary->finalTotal, 2) }}</span>
                                </div>

                                @if ($summary->reservationFeePaid > 0)
                                    <div class="flex justify-between items-center text-xs text-blue-600 dark:text-blue-400">
                                        <span class="flex items-center gap-1">
                                            <x-icon name="check-circle" class="w-3 h-3" />
                                            <span>Reservation Credited:</span>
                                        </span>
                                        <span class="font-mono font-semibold">-₱{{ number_format($summary->reservationFeePaid, 2) }}</span>
                                    </div>
                                @endif

                                <div class="flex justify-between items-center text-xs text-green-600 dark:text-green-400 pt-1 border-t border-dashed border-gray-200 dark:border-white/10">
                                    <span>Total Settled:</span>
                                    <span class="font-mono font-bold">₱{{ number_format($summary->totalPaid, 2) }}</span>
                                </div>

                                @if ($summary->balanceDue > 0)
                                    <div class="flex justify-between items-center text-xs text-[#E63946] pt-1">
                                        <span>Balance Due:</span>
                                        <span class="font-mono font-bold">₱{{ number_format($summary->balanceDue, 2) }}</span>
                                    </div>
                                @else
                                    <div class="flex items-center gap-1.5 text-xs text-green-600 font-semibold pt-1">
                                        <x-icon name="check-circle" class="w-3.5 h-3.5" />
                                        <span>Fully Paid & Settled</span>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-4 mt-3 border-t border-gray-200 dark:border-white/10 flex flex-col gap-2">
                                <button
                                    type="button"
                                    @click='openModal(@json($modalPayload))'
                                    class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-xs font-bold text-white bg-[#E63946] hover:bg-[#D62839] active:scale-95 transition-all shadow-md shadow-[#E63946]/20 cursor-pointer"
                                >
                                    <x-icon name="file-text" class="w-4 h-4" />
                                    <span>View Service Record</span>
                                </button>

                                <div class="flex gap-2">
                                    <a
                                        href="{{ route('customer.bookings.billing', $record) }}"
                                        class="flex-1 inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 transition-colors"
                                    >
                                        <x-icon name="receipt" class="w-3.5 h-3.5" />
                                        <span>Invoice</span>
                                    </a>

                                    <a
                                        href="{{ route('customer.service-history.print', $record) }}"
                                        target="_blank"
                                        class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 transition-colors"
                                        title="Print Official Record"
                                    >
                                        <x-icon name="activity" class="w-3.5 h-3.5" />
                                        <span>Print</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pt-4">
            {{ $records->links() }}
        </div>
    @endif

    {{-- Comprehensive Service Record Detail Modal --}}
    <div
        x-show="showDetailModal"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        role="dialog"
        aria-modal="true"
    >
        <div
            x-show="showDetailModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity"
            @click="closeModal()"
        ></div>

        <div class="flex min-h-screen items-center justify-center p-4">
            <div
                x-show="showDetailModal"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.stop
                class="relative w-full max-w-3xl rounded-2xl bg-white dark:bg-[#151515] p-6 text-left shadow-2xl border border-gray-200 dark:border-white/10 max-h-[90vh] overflow-y-auto"
            >
                <template x-if="activeRecord">
                    <div class="space-y-6">
                        {{-- Modal Top Bar --}}
                        <div class="flex items-start justify-between pb-4 border-b border-gray-200 dark:border-white/10">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="px-2.5 py-0.5 rounded-md text-xs font-bold bg-[#E63946] text-white">
                                        OFFICIAL SERVICE RECORD
                                    </span>
                                    <span class="font-mono text-xs text-gray-500 font-bold" x-text="activeRecord.booking_number"></span>
                                </div>
                                <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white" x-text="activeRecord.vehicle_display"></h2>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Plate: <strong class="text-gray-800 dark:text-gray-200" x-text="activeRecord.plate_number"></strong> • Serviced on <span x-text="activeRecord.date"></span>
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="closeModal()"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 cursor-pointer"
                            >
                                <x-icon name="close" class="w-5 h-5" />
                            </button>
                        </div>

                        {{-- Service Breakdown --}}
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-3">Customization & Services Performed</h4>
                            <div class="space-y-2">
                                <template x-for="(svc, idx) in activeRecord.services" :key="idx">
                                    <div class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-[#E63946]/10 text-[#E63946] flex items-center justify-center font-bold text-xs">
                                                <x-icon name="wrench" class="w-4 h-4" />
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-900 dark:text-white text-sm" x-text="svc.name"></p>
                                                <p class="text-xs text-gray-500" x-text="'Category: ' + svc.category + ' • Brand: ' + svc.brand"></p>
                                            </div>
                                        </div>
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-green-500/10 text-green-600 border border-green-500/20">
                                            Completed
                                        </span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Paint / Bodywork Scope if applicable --}}
                        <template x-if="activeRecord.paint_scope || (activeRecord.paint_panels && activeRecord.paint_panels.length > 0)">
                            <div class="p-4 rounded-xl bg-purple-50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-800/30 space-y-3">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-purple-700 dark:text-purple-300 flex items-center gap-1.5">
                                    <span>🎨 Paint Job & Bodywork Specifications</span>
                                </h4>

                                <template x-if="activeRecord.paint_scope">
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                                        <div>
                                            <p class="text-gray-500">Scope Mode:</p>
                                            <p class="font-bold text-gray-900 dark:text-white capitalize" x-text="activeRecord.paint_scope.scope_mode"></p>
                                        </div>
                                        <div>
                                            <p class="text-gray-500">Color Code:</p>
                                            <p class="font-bold text-gray-900 dark:text-white uppercase font-mono" x-text="activeRecord.paint_scope.color_code || 'OEM Matched'"></p>
                                        </div>
                                        <div>
                                            <p class="text-gray-500">Finish Type:</p>
                                            <p class="font-bold text-gray-900 dark:text-white capitalize" x-text="activeRecord.paint_scope.finish_type || 'Gloss'"></p>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="activeRecord.paint_panels && activeRecord.paint_panels.length > 0">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1.5">Panels Repaired / Custom Painted:</p>
                                        <div class="flex flex-wrap gap-1.5">
                                            <template x-for="(panel, pIdx) in activeRecord.paint_panels" :key="pIdx">
                                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-white/10 border border-purple-300 dark:border-purple-700 font-semibold text-gray-800 dark:text-gray-200">
                                                    <span x-text="panel.name"></span>
                                                    <span class="text-[10px] text-purple-600 dark:text-purple-400" x-text="'(' + panel.severity + ')'"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Work Photos --}}
                        <template x-if="(activeRecord.damage_photos && activeRecord.damage_photos.length > 0)">
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Inspection & Damage Photos</h4>
                                <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                                    <template x-for="(photo, photoIdx) in activeRecord.damage_photos" :key="photoIdx">
                                        <a :href="photo.url" target="_blank" class="group relative block overflow-hidden rounded-xl border border-gray-200 dark:border-white/10 bg-black/10">
                                            <img :src="photo.url" :alt="photo.name" class="w-full h-24 object-cover transition-transform group-hover:scale-105" />
                                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-[10px] text-white font-bold">
                                                View Full
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Mechanic & Workshop Notes --}}
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 space-y-2">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500">Workshop & Mechanic Notes</h4>
                            <p class="text-sm text-gray-700 dark:text-gray-300 italic" x-text="activeRecord.internal_notes"></p>
                            <p class="text-xs text-gray-400">Assigned Lead Mechanic: <strong class="text-gray-700 dark:text-gray-200" x-text="activeRecord.mechanic_name"></strong></p>
                        </div>

                        {{-- Billing & Financial Summary --}}
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 space-y-2 text-sm">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Billing & Payment Record</h4>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>Total Service Charges:</span>
                                <span class="font-mono font-bold text-gray-900 dark:text-white" x-text="'₱' + Number(activeRecord.billing.final_total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                            </div>
                            <template x-if="activeRecord.billing.reservation_fee_paid > 0">
                                <div class="flex justify-between text-blue-600 dark:text-blue-400 text-xs">
                                    <span>Prepaid Reservation Fee Credited:</span>
                                    <span class="font-mono font-bold" x-text="'-₱' + Number(activeRecord.billing.reservation_fee_paid).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                                </div>
                            </template>
                            <div class="flex justify-between text-green-600 dark:text-green-400 font-bold pt-2 border-t border-gray-200 dark:border-white/10">
                                <span>Total Paid & Recognized Revenue:</span>
                                <span class="font-mono" x-text="'₱' + Number(activeRecord.billing.total_paid).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></span>
                            </div>
                        </div>

                        {{-- Modal Bottom Action Buttons --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-gray-200 dark:border-white/10">
                            <a :href="activeRecord.print_url" target="_blank">
                                <x-button variant="outline" size="sm" class="flex items-center gap-2">
                                    <x-icon name="activity" class="w-4 h-4" />
                                    <span>Print Service Certificate</span>
                                </x-button>
                            </a>

                            <div class="flex items-center gap-2">
                                <a :href="activeRecord.invoice_url">
                                    <x-button variant="secondary" size="sm" class="flex items-center gap-2">
                                        <x-icon name="receipt" class="w-4 h-4" />
                                        <span>View Invoice Details</span>
                                    </x-button>
                                </a>
                                <x-button variant="primary" size="sm" @click="closeModal()">
                                    Close
                                </x-button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection
