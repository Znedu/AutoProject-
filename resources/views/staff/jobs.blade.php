@extends('layouts.dashboard')

@section('title', 'Job Orders | Staff | AutoProject+')

@section('content')
<div 
    x-data="{
        selectedFilter: '{{ $selectedFilter }}',
        jobs: @js($jobs)
    }"
    class="space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Job Orders Monitoring</h1>
        <p class="text-gray-600 dark:text-gray-400">View and monitor the progress of all active automotive customization job orders (Read-Only).</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-card class="p-4 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold uppercase tracking-wider mb-1">Total Job Orders</p>
                <p class="text-2xl font-extrabold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="p-2 rounded-lg bg-gray-100 dark:bg-white/5">
                <x-icon name="clipboard-list" class="w-6 h-6 text-gray-600 dark:text-gray-400" />
            </div>
        </x-card>

        <x-card class="p-4 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold uppercase tracking-wider mb-1">Unassigned (Pending)</p>
                <p class="text-2xl font-extrabold text-[#E63946]">{{ $stats['unassigned'] }}</p>
            </div>
            <div class="p-2 rounded-lg bg-[#E63946]/10">
                <x-icon name="clock" class="w-6 h-6 text-[#E63946]" />
            </div>
        </x-card>

        <x-card class="p-4 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold uppercase tracking-wider mb-1">In Progress</p>
                <p class="text-2xl font-extrabold text-blue-500">{{ $stats['in_progress'] }}</p>
            </div>
            <div class="p-2 rounded-lg bg-blue-500/10">
                <x-icon name="wrench" class="w-6 h-6 text-blue-500" />
            </div>
        </x-card>

        <x-card class="p-4 flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-600 dark:text-gray-400 font-semibold uppercase tracking-wider mb-1">Completed</p>
                <p class="text-2xl font-extrabold text-green-500">{{ $stats['completed'] }}</p>
            </div>
            <div class="p-2 rounded-lg bg-green-500/10">
                <x-icon name="check-square" class="w-6 h-6 text-green-500" />
            </div>
        </x-card>
    </div>

    {{-- Filters --}}
    <x-card>
        <div class="flex flex-wrap gap-2">
            <template x-for="filter in ['all', 'unassigned', 'assigned', 'in_progress', 'completed']" :key="filter">
                <a :href="'{{ route('staff.jobs.index') }}?filter=' + filter">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center rounded-xl font-semibold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer px-4 py-2 text-sm capitalize"
                        :class="(selectedFilter === filter ? 'bg-gray-200 dark:bg-[#151515] text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 hover:border-[#E63946] hover:shadow-lg hover:shadow-[#E63946]/20' : 'text-gray-600 dark:text-[#B8B8B8] hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5')"
                        x-text="filter === 'all' ? 'All Jobs' : (filter === 'unassigned' ? 'Unassigned' : (filter === 'in_progress' ? 'In Progress' : filter))"
                    ></button>
                </a>
            </template>
        </div>
    </x-card>

    {{-- Job list --}}
    <div class="space-y-4">
        <template x-if="jobs.length === 0">
            <x-card>
                <div class="text-center py-8">
                    <p class="text-gray-600 dark:text-gray-400">No job orders found for this filter.</p>
                </div>
            </x-card>
        </template>

        <template x-for="job in jobs" :key="job.id">
            <x-card>
                <div class="space-y-4">
                    {{-- Job Header --}}
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3 mb-3">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                    <span x-text="job.service"></span>
                                    <span class="text-sm font-normal text-gray-500 dark:text-gray-400 ml-2" x-text="'#' + job.job_number"></span>
                                </h3>
                                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-sm font-semibold border"
                                    :class="({
                                        'pending': 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20',
                                        'assigned': 'bg-blue-500/10 text-blue-500 border-blue-500/20',
                                        'in_progress': 'bg-purple-500/10 text-purple-500 border-purple-500/20',
                                        'completed': 'bg-green-500/10 text-green-500 border-green-500/20'
                                    })[job.status] || 'bg-yellow-500/10 text-yellow-500 border-yellow-500/20'">
                                    <span x-text="job.status === 'in_progress' ? 'In Progress' : (job.status === 'pending' ? 'Pending Assignment' : (job.status === 'assigned' ? 'Assigned' : 'Completed'))"></span>
                                </span>
                                <span
                                    class="px-2 py-1 rounded text-xs font-semibold"
                                    :class="job.priority === 'High' ? 'bg-red-100 dark:bg-red-500/20 text-red-800 dark:text-red-300' : 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-800 dark:text-yellow-300'"
                                    x-text="job.priority + ' Priority'"
                                ></span>
                                <template x-if="job.is_walk_in">
                                    <span class="px-2 py-1 text-xs font-semibold rounded bg-[#D2781A]/10 text-[#D2781A] border border-[#D2781A]/20">Walk-In</span>
                                </template>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm mt-4">
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-1">Customer</p>
                                    <p class="font-bold text-gray-900 dark:text-white" x-text="job.customer"></p>
                                    <p class="text-xs text-gray-500" x-text="job.contact"></p>
                                </div>
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-1">Vehicle</p>
                                    <p class="font-bold text-gray-900 dark:text-white" x-text="job.vehicle"></p>
                                    <p class="text-xs text-gray-500">Plate: <span x-text="job.plate_number"></span></p>
                                </div>
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-1">Assigned Mechanic</p>
                                    <p class="font-bold text-gray-900 dark:text-white" x-text="job.mechanic"></p>
                                </div>
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-1">Est. Completion</p>
                                    <p class="font-bold text-gray-900 dark:text-white" x-text="job.estimated_completion"></p>
                                    <p class="text-xs text-gray-500" x-text="'Started: ' + job.started_at"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="space-y-2 pt-2 border-t border-gray-200 dark:border-white/10">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400 font-medium">Service Progress</span>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="job.progress + '%'"></span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-zinc-800 rounded-full h-3 overflow-hidden">
                            <div
                                class="h-3 rounded-full transition-all duration-300"
                                :class="job.status === 'completed' ? 'bg-green-500' : 'bg-[#E63946]'"
                                :style="'width: ' + job.progress + '%'"
                            ></div>
                        </div>
                    </div>
                </div>
            </x-card>
        </template>
    </div>
</div>
@endsection
