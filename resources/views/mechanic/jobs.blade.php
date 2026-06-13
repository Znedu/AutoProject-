@extends('layouts.dashboard')

@section('title', 'Assigned Jobs | AutoProject+')

@section('content')
<div 
    x-data="{
        selectedFilter: 'all',
        jobs: @js($jobs),

        handleStartJob(job) {
            fetch('/mechanic/jobs/' + job.id + '/start', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    job.status = 'in-progress';
                    job.progress = 5;
                    showToast.success('Job #' + job.id + ' started!');
                } else {
                    showToast.error('Failed to start job.');
                }
            })
            .catch(err => showToast.error('An error occurred.'));
        },

        handlePauseJob(job) {
            fetch('/mechanic/jobs/' + job.id + '/pause', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    job.status = 'pending';
                    showToast.info('Job #' + job.id + ' paused!');
                } else {
                    showToast.error('Failed to pause job.');
                }
            })
            .catch(err => showToast.error('An error occurred.'));
        },

        handleCompleteJob(job) {
            fetch('/mechanic/jobs/' + job.id + '/complete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    job.status = 'completed';
                    job.progress = 100;
                    showToast.success('Job #' + job.id + ' marked as complete!');
                } else {
                    showToast.error('Failed to complete job.');
                }
            })
            .catch(err => showToast.error('An error occurred.'));
        },

        handleUpdateProgress(jobId) {
            window.location.href = '/mechanic/notes?job_id=' + jobId;
        },

        getFilteredJobs() {
            if (this.selectedFilter === 'all') return this.jobs;
            return this.jobs.filter(j => j.status === this.selectedFilter);
        }
    }"
    class="space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Assigned Jobs</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage and update your assigned service jobs.</p>
    </div>

    {{-- Filters --}}
    <x-card>
        <div class="flex flex-wrap gap-2">
            <template x-for="filter in ['all', 'pending', 'in-progress', 'completed']" :key="filter">
                <x-button
                    ::variant="selectedFilter === filter ? 'primary' : 'ghost'"
                    size="sm"
                    @click="selectedFilter = filter"
                    class="capitalize"
                    x-text="filter === 'all' ? 'All Jobs' : (filter === 'pending' ? 'Pending' : (filter === 'in-progress' ? 'In Progress' : filter))"
                ></x-button>
            </template>
        </div>
    </x-card>

    {{-- Jobs List --}}
    <div class="space-y-4">
        <template x-if="getFilteredJobs().length === 0">
            <x-card>
                <div class="text-center py-8">
                    <p class="text-gray-600 dark:text-gray-400">No assigned jobs found for this filter.</p>
                </div>
            </x-card>
        </template>

        <template x-for="job in getFilteredJobs()" :key="job.id">
            <x-card>
                <div class="space-y-4">
                    {{-- Job Header --}}
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3 mb-3">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="job.service"></h3>
                                <x-status-badge ::status="job.status">
                                    <span x-text="job.status === 'in-progress' ? 'In Progress' : (job.status === 'pending' ? 'Pending Start' : 'Completed')"></span>
                                </x-status-badge>
                                <span 
                                    class="px-2 py-1 rounded text-xs font-semibold"
                                    :class="job.priority === 'High' ? 'bg-red-100 dark:bg-red-500/20 text-red-800 dark:text-red-300' : 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-800 dark:text-yellow-300'"
                                    x-text="job.priority + ' Priority'"
                                ></span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-1">Customer</p>
                                    <p class="font-bold text-gray-900 dark:text-white" x-text="job.customer"></p>
                                    <p class="text-gray-600 dark:text-gray-400" x-text="job.contactNumber"></p>
                                </div>
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-1">Vehicle</p>
                                    <p class="font-bold text-gray-900 dark:text-white" x-text="job.vehicle"></p>
                                    <p class="text-gray-600 dark:text-gray-400">Plate: <span x-text="job.plateNumber"></span></p>
                                </div>
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-1">Start Date</p>
                                    <p class="font-bold text-gray-900 dark:text-white" x-text="job.startDate"></p>
                                </div>
                                <div>
                                    <p class="text-gray-600 dark:text-gray-400 mb-1">Est. Completion</p>
                                    <p class="font-bold text-gray-900 dark:text-white" x-text="job.estimatedCompletion"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <template x-if="job.status !== 'pending'">
                        <div class="space-y-2 pt-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Progress</span>
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
                    </template>

                    {{-- Action Triggers --}}
                    <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200 dark:border-white/10">
                        <template x-if="job.status === 'pending'">
                            <x-button
                                variant="accent"
                                size="sm"
                                @click="handleStartJob(job)"
                            >
                                Start Job
                            </x-button>
                        </template>
                        <template x-if="job.status === 'in-progress'">
                            <div class="flex gap-3">
                                <x-button
                                    variant="secondary"
                                    size="sm"
                                    @click="handlePauseJob(job)"
                                >
                                    Pause Job
                                </x-button>
                                <x-button
                                    variant="accent"
                                    size="sm"
                                    @click="handleCompleteJob(job)"
                                    class="bg-green-600 hover:bg-green-700 border-green-600 text-white"
                                >
                                    Complete Job
                                </x-button>
                            </div>
                        </template>
                        <x-button 
                            variant="outline" 
                            size="sm"
                            @click="handleUpdateProgress(job.id)"
                        >
                            Update Progress
                        </x-button>
                    </div>
                </div>
            </x-card>
        </template>
    </div>
</div>
@endsection
