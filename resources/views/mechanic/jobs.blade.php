@extends('layouts.dashboard')

@section('title', 'Assigned Jobs | AutoProject+')

@section('content')
<div
    x-data="mechanicJobs()"
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

                    {{-- Progress Bar (show whenever progress > 0, even if paused) --}}
                    <template x-if="job.progress > 0">
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

                    {{-- Action Buttons --}}
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

    {{-- Update Progress Modal --}}
    <div
        x-show="showUpdateModal"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6"
        style="display: none;"
        @keydown.escape.window="closeModal()"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
            @click="closeModal()"
        ></div>

        {{-- Modal Panel --}}
        <div
            x-show="showUpdateModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="relative bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-xl shadow-xl w-full sm:max-w-lg mx-auto overflow-hidden"
            @click.stop
        >
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Update Job Progress</h3>
                <button
                    @click="closeModal()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors cursor-pointer"
                >
                    <x-icon name="close" class="h-5 w-5" />
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-5 space-y-4">
                {{-- Progress Note --}}
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                        Progress Note <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        x-model="updateNote"
                        rows="4"
                        placeholder="Describe the current progress, work completed, any issues found, parts used, etc..."
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1a1a1a] text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-[#E63946] transition-all resize-none"
                    ></textarea>
                </div>

                {{-- Stage Selector & Progress Bar Controls --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
                            Active Service Stage
                        </label>
                        <select
                            x-model="updateStageId"
                            @change="updateProgressFromStage()"
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#151515] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#E63946] transition-all text-sm"
                        >
                            <option value="">Select stage...</option>
                            <template x-for="stage in stages" :key="stage.id">
                                <option :value="stage.id" x-text="stage.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2 text-gray-700 dark:text-gray-300">
                            Progress Percentage
                        </label>
                        <div class="flex items-center h-[42px] px-4 rounded-xl border border-gray-300 dark:border-white/10 bg-gray-50 dark:bg-[#1a1a1a] text-gray-900 dark:text-white">
                            <span class="font-bold text-[#E63946]" x-text="updateProgress + '%'"></span>
                        </div>
                    </div>
                </div>

                {{-- Photo Upload --}}
                <div>
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                        Attach Photos
                        <span class="text-gray-400 dark:text-gray-500 font-normal ml-1">(max 5)</span>
                    </label>

                    <label
                        class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 border-dashed border-gray-300 dark:border-white/10 hover:border-[#E63946] dark:hover:border-[#E63946] cursor-pointer transition-colors group"
                    >
                        <x-icon name="camera" class="w-5 h-5 text-gray-400 group-hover:text-[#E63946] transition-colors" />
                        <span class="text-sm text-gray-500 dark:text-gray-400 group-hover:text-[#E63946] transition-colors">
                            Click to upload photos
                        </span>
                        <input
                            type="file"
                            multiple
                            accept="image/*"
                            class="sr-only"
                            @change="handlePhotoUpload($event)"
                        />
                    </label>

                    {{-- Photo Previews --}}
                    <template x-if="selectedPhotos.length > 0">
                        <div class="mt-3 flex flex-wrap gap-2">
                            <template x-for="(photo, index) in selectedPhotos" :key="index">
                                <div class="relative group">
                                    <img
                                        :src="photo.url"
                                        :alt="photo.name"
                                        class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-white/10"
                                    />
                                    <button
                                        @click="removePhoto(index)"
                                        class="absolute -top-1.5 -right-1.5 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        &times;
                                    </button>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate max-w-[4rem]" x-text="photo.name"></p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Customer visibility notice --}}
                <div class="flex items-start gap-2 p-3 rounded-lg bg-blue-50 dark:bg-blue-950/20 border border-blue-200 dark:border-blue-500/20">
                    <x-icon name="info" class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" />
                    <p class="text-xs text-blue-700 dark:text-blue-300">
                        This note will be visible to the customer and help them track their service progress.
                    </p>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-black/20 border-t border-gray-200 dark:border-white/10 flex justify-end gap-3">
                <x-button variant="outline" size="sm" @click="closeModal()">
                    Cancel
                </x-button>
                <x-button variant="accent" size="sm" @click="handleSubmitUpdate()" class="text-white">
                    Save Update
                </x-button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function mechanicJobs() {
        return {
            selectedFilter: 'all',
            jobs: @json($jobs),
            stages: @json($stages),
            showUpdateModal: false,
            selectedJobId: null,
            updateNote: '',
            updateStageId: '',
            updateProgress: 0,
            selectedPhotos: [],
            rawPhotos: [],

            init() {
            },

            getFilteredJobs() {
                if (this.selectedFilter === 'all') return this.jobs;
                return this.jobs.filter(j => j.status === this.selectedFilter);
            },

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
                        // Use server-returned value (max of existing progress or 5)
                        job.progress = data.progress ?? Math.max(job.progress || 0, 5);
                        showToast.success('Job #' + job.id + ' started!');
                    } else {
                        showToast.error('Failed to start job.');
                    }
                })
                .catch(() => showToast.error('An error occurred.'));
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
                .catch(() => showToast.error('An error occurred.'));
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
                .catch(() => showToast.error('An error occurred.'));
            },

            handleUpdateProgress(jobId) {
                this.selectedJobId = jobId;
                
                // Pre-fill local fields if job exists in jobs list
                const job = this.jobs.find(j => j.id === jobId);
                if (job) {
                    this.updateProgress = job.progress || 0;
                    this.updateStageId = job.currentStageId || '';
                }

                this.showUpdateModal = true;
            },

            updateProgressFromStage() {
                if (!this.updateStageId) return;
                const stageId = parseInt(this.updateStageId);
                const index = this.stages.findIndex(s => s.id === stageId);
                if (index !== -1) {
                    this.updateProgress = Math.round(((index + 1) / this.stages.length) * 100);
                }
            },

            handlePhotoUpload(e) {
                const files = Array.from(e.target.files || []);
                if (files.length + this.selectedPhotos.length > 5) {
                    showToast.error('Maximum 5 photos allowed per update');
                    return;
                }
                files.forEach(file => {
                    this.rawPhotos.push(file);
                    this.selectedPhotos.push({ name: file.name, url: URL.createObjectURL(file) });
                });
            },

            removePhoto(index) {
                this.selectedPhotos = this.selectedPhotos.filter((_, i) => i !== index);
                this.rawPhotos      = this.rawPhotos.filter((_, i) => i !== index);
            },

            handleSubmitUpdate() {
                if (!this.updateNote.trim()) {
                    showToast.error('Please add a note');
                    return;
                }

                const formData = new FormData();
                formData.append('jobId', this.selectedJobId);
                formData.append('note', this.updateNote);
                formData.append('progress', this.updateProgress);
                formData.append('stage_id', this.updateStageId);

                this.rawPhotos.forEach(file => {
                    formData.append('photos[]', file);
                });

                fetch('/mechanic/notes', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast.success('Progress update note saved successfully! Customer will be notified.');
                        
                        // Dynamically update the local state of the job status/progress
                        const job = this.jobs.find(j => j.id === this.selectedJobId);
                        if (job) {
                            job.progress = this.updateProgress;
                            job.currentStageId = parseInt(this.updateStageId);
                            if (this.updateProgress >= 100) {
                                job.status = 'completed';
                            } else {
                                job.status = 'in-progress';
                            }
                        }

                        this.closeModal();
                    } else {
                        showToast.error('Failed to save progress update: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(() => showToast.error('An error occurred.'));
            },

            closeModal() {
                this.showUpdateModal = false;
                this.updateNote = '';
                this.selectedPhotos = [];
                this.rawPhotos = [];
                this.selectedJobId = null;
                this.updateStageId = '';
                this.updateProgress = 0;
            }
        };
    }
</script>
@endpush
