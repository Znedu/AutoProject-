@extends('layouts.dashboard')

@section('title', 'Service Notes | AutoProject+')

@section('content')
<div
    x-data="mechanicNotes()"
    class="max-w-4xl mx-auto space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Service Notes</h1>
            <p class="text-gray-600 dark:text-gray-400">Track progress and add notes for your jobs.</p>
        </div>
        <x-button variant="accent" @click="showAddForm = !showAddForm" class="text-white">
            <x-icon name="check-square" class="w-5 h-5 mr-2 inline-block text-white" />
            Add Note
        </x-button>
    </div>

    {{-- Add Note Form --}}
    <div x-show="showAddForm" x-collapse>
        <x-card>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Add Service Note</h2>
            <form @submit.prevent="handleSubmit()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2 text-gray-900 dark:text-white">Select Job <span class="text-red-500">*</span></label>
                    <select
                        x-model="formData.jobId"
                        required
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#151515] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#E63946] transition-all"
                    >
                        <template x-for="job in jobs" :key="job.value">
                            <option :value="job.value" x-text="job.label"></option>
                        </template>
                    </select>
                </div>
                <x-textarea
                    label="Service Note"
                    x-model="formData.note"
                    placeholder="Describe what work was done, current progress, any issues found, etc..."
                    rows="5"
                    required
                />
                <div class="flex gap-3">
                    <x-button type="submit" variant="accent" class="text-white">Add Note</x-button>
                    <x-button type="button" variant="outline" @click="showAddForm = false">Cancel</x-button>
                </div>
            </form>
        </x-card>
    </div>

    {{-- Notes History --}}
    <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Service Updates History</h2>

        {{-- Empty state --}}
        <template x-if="notes.length === 0">
            <x-card>
                <div class="text-center py-8">
                    <div class="flex justify-center mb-3">
                        <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center">
                            <x-icon name="clipboard-list" class="w-7 h-7 text-gray-400 dark:text-gray-600" />
                        </div>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400">No service updates yet. Submit your first note above.</p>
                </div>
            </x-card>
        </template>

        <div class="space-y-4">
            <template x-for="note in notes" :key="note.id">
                <x-card>
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-[#E63946]/10 rounded-xl flex-shrink-0">
                            <x-icon name="clipboard-list" class="w-6 h-6 text-[#E63946]" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-2">
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white mb-1" x-text="note.job"></h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span x-text="note.date"></span> • By <span x-text="note.mechanic"></span>
                                    </p>
                                </div>
                                {{-- Photo count badge --}}
                                <template x-if="note.photos && note.photos.length > 0">
                                    <div class="flex items-center gap-1 text-sm text-[#E63946] bg-red-50 dark:bg-red-950/30 px-2 py-1 rounded-lg flex-shrink-0">
                                        <x-icon name="camera" class="w-4 h-4 text-[#E63946]" />
                                        <span><span x-text="note.photos.length"></span> photo<span x-text="note.photos.length > 1 ? 's' : ''"></span></span>
                                    </div>
                                </template>
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed mb-3" x-text="note.note"></p>

                            {{-- Photo Gallery --}}
                            <template x-if="note.photos && note.photos.length > 0">
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-2">
                                    <template x-for="(photo, photoIndex) in note.photos" :key="photoIndex">
                                        <div
                                            class="relative group cursor-pointer"
                                            @click="openLightbox(photo.url)"
                                        >
                                            <div class="aspect-square bg-gray-200 dark:bg-gray-700 rounded-xl overflow-hidden">
                                                <img
                                                    :src="photo.url"
                                                    :alt="photo.caption"
                                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                                                />
                                            </div>
                                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors rounded-xl flex items-center justify-center">
                                                <svg class="text-white opacity-0 group-hover:opacity-100 transition-opacity w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                </svg>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate" x-text="photo.caption"></p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-card>
            </template>
        </div>
    </div>

    {{-- Quick Tips --}}
    <x-card class="bg-blue-50/50 dark:bg-blue-950/20 border-2 border-[#457B9D]/30">
        <h3 class="font-bold text-gray-900 dark:text-white mb-3">💡 Tips for Service Notes</h3>
        <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
            <li>• Be specific about work completed and current status</li>
            <li>• Note any issues or concerns discovered during service</li>
            <li>• Include details that help track progress over time</li>
            <li>• Mention parts used or materials required</li>
            <li>• These notes and photos are visible to customers for transparency</li>
        </ul>
    </x-card>

    {{-- Photo Lightbox --}}
    <div
        x-show="lightboxPhoto"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/95 flex items-center justify-center z-50 p-4"
        style="display:none;"
        @click="closeLightbox()"
        @keydown.escape.window="closeLightbox()"
    >
        <button
            class="absolute top-4 right-4 text-white hover:text-gray-300 cursor-pointer transition-colors"
            @click="closeLightbox()"
        >
            <x-icon name="x" class="w-8 h-8" />
        </button>
        <img
            :src="lightboxPhoto"
            alt="Full size preview"
            class="max-w-full max-h-full object-contain rounded-lg shadow-2xl"
            @click.stop
        />
    </div>
</div>
@endsection

@push('scripts')
<script>
    function mechanicNotes() {
        return {
            showAddForm: false,
            jobs: @json($jobs),
            notes: @json($notes),
            lightboxPhoto: null,
            formData: {
                jobId: '',
                note: ''
            },

            init() {
                // Pre-select job_id from query string if present
                const params = new URLSearchParams(window.location.search);
                const jobId  = params.get('job_id');
                if (jobId) {
                    this.formData.jobId = jobId;
                    this.showAddForm    = true;
                }
            },

            openLightbox(url) {
                this.lightboxPhoto = url;
                document.body.classList.add('overflow-hidden');
            },

            closeLightbox() {
                this.lightboxPhoto = null;
                document.body.classList.remove('overflow-hidden');
            },

            handleSubmit() {
                if (!this.formData.jobId || !this.formData.note.trim()) {
                    showToast.error('Please select a job and enter a note');
                    return;
                }
                fetch('/mechanic/notes', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.formData)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.notes.unshift(data.note);
                        showToast.success('Service note added successfully!');
                        this.formData    = { jobId: '', note: '' };
                        this.showAddForm = false;
                    } else {
                        showToast.error('Failed to add note: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(() => showToast.error('An error occurred.'));
            }
        };
    }
</script>
@endpush

