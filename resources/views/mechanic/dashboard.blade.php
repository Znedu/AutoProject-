@extends('layouts.dashboard')

@section('title', 'Mechanic Dashboard | AutoProject+')

@section('content')
<div
    x-data="mechanicDashboard()"
    class="space-y-8 animate-fade-in"
>
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Mechanic Dashboard</h1>
        <p class="text-gray-600 dark:text-gray-400">Manage your assigned jobs and service updates.</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-stat-card
            title="Assigned Jobs"
            value="{{ $assignedJobsCount }}"
            icon="wrench"
            color="blue"
        />
        <x-stat-card
            title="In Progress"
            value="{{ $inProgressCount }}"
            icon="clock"
            color="red"
        />
        <x-stat-card
            title="Completed Today"
            value="{{ $completedTodayCount }}"
            icon="check-square"
            color="green"
        />
        <x-stat-card
            title="Pending Start"
            value="{{ $pendingStartCount }}"
            icon="info"
            color="charcoal"
        />
    </div>

    {{-- Quick Actions --}}
    <x-card>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-4 mb-4">
            <a href="{{ url('/mechanic/jobs') }}">
                <x-button variant="accent">View All Jobs</x-button>
            </a>
            <a href="{{ url('/mechanic/notes') }}">
                <x-button variant="secondary">Add Service Note</x-button>
            </a>
        </div>
        <div class="p-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-xl">
            <div class="flex items-start gap-2">
                <x-icon name="message-square" class="text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0 w-5 h-5" />
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>Photo Updates:</strong> Click "Update Progress" on any in-progress job to send photos and updates to customers. This builds trust and keeps them informed!
                </p>
            </div>
        </div>
    </x-card>

    {{-- Current Assigned Jobs --}}
    <x-card>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Current Assigned Jobs</h2>
        <div class="space-y-4">
            <template x-for="job in assignedJobs" :key="job.id">
                <div class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#151515] rounded-xl p-4">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3 mb-2">
                                <h3 class="font-bold text-gray-900 dark:text-white" x-text="job.service"></h3>
                                <x-status-badge ::status="job.status">
                                    <span x-text="job.status === 'in-progress' ? 'In Progress' : 'Pending Start'"></span>
                                </x-status-badge>
                                <span
                                    class="px-2 py-1 rounded text-xs font-semibold"
                                    :class="job.priority === 'High' ? 'bg-red-100 dark:bg-red-500/20 text-red-800 dark:text-red-300' : 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-800 dark:text-yellow-300'"
                                    x-text="job.priority + ' Priority'"
                                ></span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Customer: <span x-text="job.customer"></span></p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Vehicle: <span x-text="job.vehicle"></span></p>
                        </div>
                        <div class="flex gap-2">
                            <template x-if="job.status === 'pending'">
                                <x-button variant="accent" size="sm" @click="handleStartJob(job)">Start Job</x-button>
                            </template>
                            <template x-if="job.status === 'in-progress'">
                                <div class="flex gap-2">
                                    <x-button variant="secondary" size="sm" @click="handleUpdateProgress(job.id)">
                                        Update Progress
                                    </x-button>
                                    <x-button variant="outline" size="sm" @click="handlePauseJob(job)">Pause</x-button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </x-card>

    {{-- Recent Activity --}}
    <x-card>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Recent Activity</h2>
        <div class="space-y-4">
            @forelse ($recentActivities as $activity)
                <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-white/10 last:border-b-0 last:pb-0">
                    <div class="w-2 h-2 bg-[#E63946] rounded-full mt-2"></div>
                    <div>
                        <p class="text-gray-900 dark:text-white font-medium">{{ $activity['message'] }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $activity['job'] }} - {{ $activity['time'] }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-6 text-gray-600 dark:text-gray-400">
                    No recent activity recorded.
                </div>
            @endforelse
        </div>
    </x-card>

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
        style="display:none;"
        @keydown.escape.window="closeModal()"
    >
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="closeModal()"></div>

        {{-- Modal Panel --}}
        <div
            x-show="showUpdateModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95"
            class="relative bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-xl shadow-xl w-full sm:max-w-2xl mx-auto max-h-[90vh] overflow-y-auto"
            @click.stop
        >
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between sticky top-0 bg-white dark:bg-[#151515] z-10">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Update Progress</h2>
                <button
                    @click="closeModal()"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors cursor-pointer"
                >
                    <x-icon name="close" class="w-6 h-6" />
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="px-6 py-5 space-y-4">
                {{-- Note Input --}}
                <x-textarea
                    label="Progress Update Note *"
                    x-model="updateNote"
                    placeholder="Describe the current progress, what's been completed, and next steps..."
                    rows="4"
                    helperText="This note will be visible to the customer"
                />

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
                    <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-white">
                        Progress Photos <span class="font-normal text-gray-500 dark:text-gray-400">(Optional — max 5)</span>
                    </label>

                    <label
                        class="flex items-center gap-3 px-4 py-4 rounded-xl border-2 border-dashed border-gray-300 dark:border-white/10 hover:border-[#E63946] dark:hover:border-[#E63946] cursor-pointer transition-colors group"
                    >
                        <x-icon name="camera" class="w-8 h-8 text-gray-400 group-hover:text-[#E63946] transition-colors flex-shrink-0" />
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-[#E63946] transition-colors">Click to upload photos</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">JPG, PNG up to 10MB each</p>
                        </div>
                        <input
                            type="file"
                            accept="image/*"
                            multiple
                            class="sr-only"
                            @change="handlePhotoUpload($event)"
                        />
                    </label>

                    {{-- Photo Preview Gallery --}}
                    <template x-if="selectedPhotos.length > 0">
                        <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <template x-for="(photo, index) in selectedPhotos" :key="index">
                                <div class="relative group">
                                    <div class="aspect-square bg-gray-100 dark:bg-gray-800 rounded-xl overflow-hidden border border-gray-200 dark:border-white/10">
                                        <img
                                            :src="photo.url"
                                            :alt="'Preview ' + (index + 1)"
                                            class="w-full h-full object-cover"
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        @click="removePhoto(index)"
                                        class="absolute -top-1.5 -right-1.5 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                                    >
                                        &times;
                                    </button>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 truncate" x-text="photo.name"></p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-950/30 rounded-xl border border-blue-200 dark:border-blue-800">
                        <div class="flex items-start gap-2">
                            <x-icon name="info" class="text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0 w-4 h-4" />
                            <p class="text-xs text-blue-800 dark:text-blue-200">
                                <strong>Tip:</strong> Include photos showing different angles, close-ups of work progress, and any issues found. This helps keep customers informed and builds trust.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-black/20 border-t border-gray-200 dark:border-white/10 flex gap-3">
                <x-button
                    variant="accent"
                    @click="handleSubmitUpdate()"
                    class="bg-green-600 hover:bg-green-700 border-green-600 text-white"
                >
                    Send Update
                </x-button>
                <x-button
                    variant="outline"
                    @click="closeModal()"
                    class="text-red-600 border-red-600 hover:bg-red-50 dark:hover:bg-red-950/30"
                >
                    Cancel
                </x-button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function mechanicDashboard() {
        return {
            assignedJobs: @json($assignedJobs),
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

            handleUpdateProgress(jobId) {
                this.selectedJobId = jobId;
                
                // Pre-fill local fields if job exists in assignedJobs list
                const job = this.assignedJobs.find(j => j.id === jobId);
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
                        const job = this.assignedJobs.find(j => j.id === this.selectedJobId);
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
