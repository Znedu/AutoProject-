@extends('layouts.dashboard')

@section('title', 'Assigned Jobs | AutoProject+')

@section('content')
<div 
    x-data="{
        selectedFilter: 'all',
        jobs: [
            {
                id: 1,
                customer: 'Juan Dela Cruz',
                contactNumber: '+63 912 345 6789',
                service: 'Paint Job',
                vehicle: 'Toyota Supra 2021',
                plateNumber: 'XYZ 5678',
                status: 'in-progress',
                progress: 65,
                startDate: 'March 29, 2026',
                estimatedCompletion: 'April 2, 2026',
                priority: 'High'
            },
            {
                id: 2,
                customer: 'Maria Santos',
                contactNumber: '+63 917 888 9999',
                service: 'Engine Customization',
                vehicle: 'Honda Civic 2020',
                plateNumber: 'ABC 1234',
                status: 'pending',
                progress: 0,
                startDate: 'April 5, 2026',
                estimatedCompletion: 'April 12, 2026',
                priority: 'Medium'
            },
            {
                id: 3,
                customer: 'Pedro Rodriguez',
                contactNumber: '+63 923 111 2222',
                service: 'Turbo Installation',
                vehicle: 'Subaru WRX 2022',
                plateNumber: 'GHI 3456',
                status: 'completed',
                progress: 100,
                startDate: 'March 18, 2026',
                estimatedCompletion: 'March 20, 2026',
                priority: 'High'
            }
        ],

        handleStartJob(job) {
            job.status = 'in-progress';
            job.progress = 5;
            showToast.success('Job #' + job.id + ' started!');
        },

        handlePauseJob(job) {
            job.status = 'pending';
            showToast.info('Job #' + job.id + ' paused!');
        },

        handleCompleteJob(job) {
            job.status = 'completed';
            job.progress = 100;
            showToast.success('Job #' + job.id + ' marked as complete!');
        },

        handleUpdateProgress(jobId) {
            // Simply opens the progress simulation or navigates
            showToast.info('Opening progress editor for Job #' + jobId + '...');
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
