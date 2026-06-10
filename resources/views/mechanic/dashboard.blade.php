@extends('layouts.dashboard')

@section('title', 'Mechanic Dashboard | AutoProject+')

@section('content')
<div 
    x-data="{
        showUpdateModal: false,
        selectedJobId: null,
        updateNote: '',
        selectedPhotos: [],
        assignedJobs: [
            {
                id: 1,
                customer: 'Juan Dela Cruz',
                service: 'Paint Job',
                vehicle: 'Toyota Supra 2021',
                status: 'in-progress',
                priority: 'High'
            },
            {
                id: 2,
                customer: 'Maria Santos',
                service: 'Engine Customization',
                vehicle: 'Honda Civic 2020',
                status: 'pending',
                priority: 'Medium'
            }
        ],

        handleStartJob(job) {
            job.status = 'in-progress';
            showToast.success('Job #' + job.id + ' started!');
        },

        handlePauseJob(job) {
            job.status = 'pending';
            showToast.info('Job #' + job.id + ' paused!');
        },

        handleUpdateProgress(jobId) {
            this.selectedJobId = jobId;
            this.showUpdateModal = true;
        },

        handlePhotoUpload(e) {
            const files = Array.from(e.target.files || []);
            if (files.length + this.selectedPhotos.length > 5) {
                showToast.error('Maximum 5 photos allowed per update');
                return;
            }
            files.forEach(file => {
                this.selectedPhotos.push({
                    name: file.name,
                    url: URL.createObjectURL(file)
                });
            });
        },

        removePhoto(index) {
            this.selectedPhotos = this.selectedPhotos.filter((_, i) => i !== index);
        },

        handleSubmitUpdate() {
            if (!this.updateNote.trim() && this.selectedPhotos.length === 0) {
                showToast.error('Please add a note or at least one photo');
                return;
            }
            showToast.success('Progress update sent! ' + this.selectedPhotos.length + ' photo(s) attached. Customer will be notified.');
            this.closeModal();
        },

        closeModal() {
            this.showUpdateModal = false;
            this.updateNote = '';
            this.selectedPhotos = [];
            this.selectedJobId = null;
        }
    }"
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
            value="2"
            icon="wrench"
            color="blue"
        />
        <x-stat-card
            title="In Progress"
            value="1"
            icon="clock"
            color="red"
        />
        <x-stat-card
            title="Completed Today"
            value="3"
            icon="check-square"
            color="green"
        />
        <x-stat-card
            title="Pending Start"
            value="1"
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
            <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-white/10">
                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                <div>
                    <p class="text-gray-900 dark:text-white font-medium">Completed: Turbo Installation</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Subaru WRX 2022 - 2 hours ago</p>
                </div>
            </div>
            <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-white/10">
                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                <div>
                    <p class="text-gray-900 dark:text-white font-medium">Updated progress: Paint Job</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Toyota Supra 2021 - 3 hours ago</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2"></div>
                <div>
                    <p class="text-gray-900 dark:text-white font-medium">Started: Paint Job</p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Toyota Supra 2021 - 1 day ago</p>
                </div>
            </div>
        </div>
    </x-card>

    {{-- Update Progress Modal --}}
    <div 
        x-show="showUpdateModal" 
        x-cloak 
        class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    >
        <x-card class="max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Update Progress</h2>
                <button
                    @click="closeModal()"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 cursor-pointer"
                >
                    <x-icon name="chevron-right" class="w-6 h-6 transform rotate-90" />
                </button>
            </div>

            <div class="space-y-4">
                {{-- Note Input --}}
                <x-textarea
                    label="Progress Update Note *"
                    x-model="updateNote"
                    placeholder="Describe the current progress, what's been completed, and next steps..."
                    rows="4"
                    helperText="This note will be visible to the customer"
                />

                {{-- Photo Upload --}}
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-white">
                        Progress Photos (Optional - Max 5)
                    </label>

                    {{-- Upload Panel --}}
                    <div class="border-2 border-dashed border-gray-300 dark:border-white/10 rounded-xl p-6 text-center bg-white/5 hover:border-[#E63946] transition-colors duration-300">
                        <input
                            type="file"
                            id="photo-upload"
                            accept="image/*"
                            multiple
                            @change="handlePhotoUpload"
                            class="hidden"
                        />
                        <label
                            for="photo-upload"
                            class="cursor-pointer inline-flex flex-col items-center"
                        >
                            <x-icon name="message-square" class="text-gray-400 dark:text-gray-500 mb-2 w-10 h-10" />
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Click to upload photos
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                JPG, PNG up to 10MB each
                            </span>
                        </label>
                    </div>

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
                                        class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1 hover:bg-red-700 transition-colors cursor-pointer"
                                    >
                                        <x-icon name="chevron-right" class="w-4 h-4 transform rotate-45" />
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

                {{-- Modal Action Buttons --}}
                <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-white/10">
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
        </x-card>
    </div>
</div>
@endsection
