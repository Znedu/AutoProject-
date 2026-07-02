@extends('layouts.dashboard')

@section('title', 'Job Assignment | AutoProject+')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Job Assignment</h1>
            <p class="text-gray-600 dark:text-gray-400">Assign mechanics to approved bookings and manage job priorities.</p>
        </div>
        <a href="{{ route('admin.approvals.index') }}">
            <x-button variant="secondary">
                <x-icon name="check-square" class="w-4 h-4 mr-2" />
                Booking Approvals
            </x-button>
        </a>
    </div>

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-card class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Unassigned</p>
            <p class="text-3xl font-bold text-[#E63946]">{{ $stats['unassigned'] }}</p>
        </x-card>
        <x-card class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Assigned</p>
            <p class="text-3xl font-bold text-[#457B9D]">{{ $stats['assigned'] }}</p>
        </x-card>
        <x-card class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">In Progress</p>
            <p class="text-3xl font-bold text-green-500">{{ $stats['in_progress'] }}</p>
        </x-card>
        <x-card class="text-center">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider">Mechanics Available</p>
            <p class="text-3xl font-bold text-gray-700 dark:text-gray-200">{{ $stats['mechanics_available'] }}</p>
        </x-card>
    </div>

    {{-- Filter Tabs --}}
    <x-card>
        <div class="flex flex-wrap gap-2">
            @foreach (['unassigned' => 'Unassigned', 'assigned' => 'Assigned', 'all' => 'All Jobs'] as $filter => $label)
                <a href="{{ route('admin.jobs.index', ['filter' => $filter]) }}">
                    <x-button
                        :variant="$selectedFilter === $filter ? 'primary' : 'ghost'"
                        size="sm"
                    >
                        {{ $label }}
                        @if ($filter === 'unassigned' && $stats['unassigned'] > 0)
                            <span class="ml-2 px-1.5 py-0.5 text-xs bg-white/20 rounded-full">{{ $stats['unassigned'] }}</span>
                        @endif
                    </x-button>
                </a>
            @endforeach
        </div>
    </x-card>

    {{-- Session Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-5 py-4 bg-green-500/10 border border-green-500/20 rounded-xl text-green-600 dark:text-green-400">
            <x-icon name="check-circle" class="w-5 h-5 flex-shrink-0" />
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-3 px-5 py-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-600 dark:text-red-400">
            <x-icon name="info" class="w-5 h-5 flex-shrink-0" />
            <p class="text-sm font-medium">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Job Cards --}}
    <div class="space-y-6">
        @forelse ($jobs as $job)
            @php
                $booking     = $job->booking;
                $quotation   = $booking->quotations->first();
                $serviceNames = $booking->bookingServices->pluck('service.name')->join(', ');
                $vehicleLabel = trim(implode(' ', array_filter([
                    $booking->vehicle?->make,
                    $booking->vehicle?->model,
                    $booking->vehicle?->year,
                ])));
                $isUnassigned = $job->status === \App\Models\JobOrder::STATUS_PENDING;
            @endphp

            <x-card x-data="{ showForm: {{ $isUnassigned ? 'true' : 'false' }} }">
                <div class="space-y-6">

                    {{-- Card Header --}}
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $serviceNames }}</h3>

                                {{-- Job Number Badge --}}
                                <span class="px-2.5 py-1 text-xs font-mono font-semibold bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300 rounded-lg">
                                    {{ $job->job_number }}
                                </span>

                                {{-- Status Badge --}}
                                @if ($isUnassigned)
                                    <span class="flex items-center gap-1.5 px-3 py-1 bg-red-500/10 text-[#E63946] border border-red-500/20 rounded-full text-xs font-semibold">
                                        <x-icon name="info" class="w-3.5 h-3.5" />
                                        Unassigned
                                    </span>
                                @else
                                    <span class="flex items-center gap-1.5 px-3 py-1 bg-blue-500/10 text-[#457B9D] border border-blue-500/20 rounded-full text-xs font-semibold">
                                        <x-icon name="check-circle" class="w-3.5 h-3.5" />
                                        Assigned
                                    </span>
                                @endif

                                {{-- Priority Badge --}}
                                @php
                                    $priorityClasses = match($job->priority) {
                                        'high'   => 'bg-red-500/10 text-red-500 border-red-500/20',
                                        'medium' => 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
                                        default  => 'bg-gray-500/10 text-gray-500 border-gray-500/20',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-semibold border rounded-full capitalize {{ $priorityClasses }}">
                                    {{ $job->priority }} Priority
                                </span>
                            </div>

                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Booking {{ $booking->booking_number }}
                                &bull; Approved {{ $booking->approved_at?->diffForHumans() }}
                                &bull; Scheduled: {{ $booking->scheduled_date?->format('F j, Y') ?? 'TBD' }}
                            </p>
                        </div>

                        {{-- Reassign toggle for already-assigned jobs --}}
                        @unless ($isUnassigned)
                            <button
                                type="button"
                                @click="showForm = !showForm"
                                class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-[#457B9D] border border-[#457B9D]/30 rounded-xl hover:bg-[#457B9D]/10 transition-colors duration-200"
                            >
                                <x-icon name="wrench" class="w-4 h-4" />
                                <span x-text="showForm ? 'Cancel' : 'Reassign'"></span>
                            </button>
                        @endunless
                    </div>

                    {{-- Details Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b border-gray-200 dark:border-white/10">
                        {{-- Customer --}}
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Customer</h4>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $booking->customer_name }}</p>
                            <p class="text-sm text-gray-500">{{ $booking->contact_number }}</p>
                        </div>

                        {{-- Vehicle --}}
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Vehicle</h4>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $vehicleLabel ?: '—' }}</p>
                            <p class="text-sm text-gray-500">{{ $booking->vehicle?->plate_number }}</p>
                        </div>

                        {{-- Currently Assigned --}}
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Assigned Mechanic</h4>
                            @if ($job->mechanic)
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#457B9D] to-[#1D3557] flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                                        {{ $job->mechanic->initials }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $job->mechanic->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $job->mechanic->email }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="flex items-center gap-2 text-[#E63946]">
                                    <x-icon name="info" class="w-4 h-4" />
                                    <span class="text-sm font-medium">No mechanic assigned yet</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Cost Estimation --}}
                    @if ($quotation)
                        <div class="flex items-center gap-4 bg-gray-50 dark:bg-white/5 rounded-xl px-5 py-3 border border-gray-200 dark:border-white/10">
                            <x-icon name="dollar-sign" class="w-5 h-5 text-[#E63946]" />
                            <div>
                                <p class="text-xs text-gray-500">Estimated Cost</p>
                                <p class="font-bold text-gray-900 dark:text-white">{{ $quotation->total_range_display }}</p>
                            </div>
                            @if ($booking->notes)
                                <div class="ml-auto text-right">
                                    <p class="text-xs text-gray-500">Customer Notes</p>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 max-w-xs truncate">{{ $booking->notes }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Assignment Form --}}
                    <div x-show="showForm" x-cloak>
                        <div class="bg-blue-500/5 dark:bg-blue-500/10 border-2 border-[#457B9D]/30 rounded-xl p-6">
                            <div class="flex items-center gap-2 mb-5">
                                <x-icon name="wrench" class="w-5 h-5 text-[#457B9D]" />
                                <h4 class="font-bold text-gray-900 dark:text-white">
                                    {{ $isUnassigned ? 'Assign Mechanic' : 'Reassign Mechanic' }}
                                </h4>
                            </div>

                            <form
                                method="POST"
                                action="{{ route('admin.jobs.assign', $job) }}"
                                class="space-y-5"
                            >
                                @csrf

                                {{-- Mechanic Select --}}
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            Select Mechanic <span class="text-[#E63946]">*</span>
                                        </label>
                                        <select
                                            name="mechanic_id"
                                            required
                                            id="mechanic_select_{{ $job->id }}"
                                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1E1E1E] px-4 py-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-[#457B9D] focus:ring-2 focus:ring-[#457B9D]/20 transition-colors"
                                        >
                                            <option value="">— Choose a mechanic —</option>
                                            @foreach ($mechanics as $mechanic)
                                                <option
                                                    value="{{ $mechanic->id }}"
                                                    {{ ($job->mechanic_id === $mechanic->id) ? 'selected' : '' }}
                                                >
                                                    {{ $mechanic->name }}
                                                    ({{ $mechanic->active_jobs_count }} active {{ Str::plural('job', $mechanic->active_jobs_count) }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('mechanic_id')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                            Priority <span class="text-[#E63946]">*</span>
                                        </label>
                                        <select
                                            name="priority"
                                            required
                                            id="priority_select_{{ $job->id }}"
                                            class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1E1E1E] px-4 py-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-[#457B9D] focus:ring-2 focus:ring-[#457B9D]/20 transition-colors"
                                        >
                                            <option value="low"    {{ $job->priority === 'low'    ? 'selected' : '' }}>Low</option>
                                            <option value="medium" {{ $job->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="high"   {{ $job->priority === 'high'   ? 'selected' : '' }}>High</option>
                                        </select>
                                        @error('priority')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Estimated Date --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Estimated Completion Date
                                        <span class="font-normal text-gray-400">(optional)</span>
                                    </label>
                                    <input
                                        type="date"
                                        name="estimated_completion_date"
                                        id="est_date_{{ $job->id }}"
                                        min="{{ now()->toDateString() }}"
                                        value="{{ $job->estimated_completion_date?->toDateString() }}"
                                        class="w-full md:w-72 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1E1E1E] px-4 py-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-[#457B9D] focus:ring-2 focus:ring-[#457B9D]/20 transition-colors"
                                    />
                                    @error('estimated_completion_date')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Internal Notes --}}
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                                        Internal Notes
                                        <span class="font-normal text-gray-400">(visible to mechanic, not customer)</span>
                                    </label>
                                    <textarea
                                        name="internal_notes"
                                        id="notes_{{ $job->id }}"
                                        rows="3"
                                        maxlength="1000"
                                        placeholder="Add instructions or notes for the mechanic..."
                                        class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1E1E1E] px-4 py-3 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-[#457B9D] focus:ring-2 focus:ring-[#457B9D]/20 transition-colors resize-none"
                                    >{{ $job->internal_notes }}</textarea>
                                    @error('internal_notes')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Submit --}}
                                <div class="flex items-center gap-3 pt-2">
                                    <x-button variant="accent" type="submit" id="assign_btn_{{ $job->id }}">
                                        <x-icon name="check-circle" class="w-5 h-5 mr-2" />
                                        {{ $isUnassigned ? 'Assign Mechanic' : 'Reassign Mechanic' }}
                                    </x-button>
                                    @unless ($isUnassigned)
                                        <button
                                            type="button"
                                            @click="showForm = false"
                                            class="px-4 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
                                        >
                                            Cancel
                                        </button>
                                    @endunless
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Already Assigned — summary (when form is hidden) --}}
                    @unless ($isUnassigned)
                        <div x-show="!showForm" class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 pt-2 border-t border-gray-200 dark:border-white/10">
                            <x-icon name="check-circle" class="w-4 h-4 text-green-500" />
                            <span>
                                Assigned by <strong class="text-gray-900 dark:text-white">{{ $job->assigner?->name ?? 'Admin' }}</strong>
                                {{ $job->assigned_at?->diffForHumans() }}
                                @if ($job->estimated_completion_date)
                                    &bull; ETA: <strong class="text-gray-900 dark:text-white">{{ $job->estimated_completion_date->format('M j, Y') }}</strong>
                                @endif
                            </span>
                        </div>
                    @endunless

                </div>
            </x-card>
        @empty
            <x-card class="text-center py-16">
                <x-icon name="wrench" class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">No jobs found</p>
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">
                    @if ($selectedFilter === 'unassigned')
                        All approved bookings have been assigned to mechanics.
                    @else
                        No jobs match the selected filter.
                    @endif
                </p>
                <div class="mt-6">
                    <a href="{{ route('admin.approvals.index') }}">
                        <x-button variant="primary" size="sm">Go to Booking Approvals</x-button>
                    </a>
                </div>
            </x-card>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($jobs->hasPages())
        <div class="pt-2">
            {{ $jobs->links() }}
        </div>
    @endif

</div>
@endsection
