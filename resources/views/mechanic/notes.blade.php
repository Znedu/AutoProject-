@extends('layouts.dashboard')

@section('title', 'Service Notes | AutoProject+')

@section('content')
<div 
    x-data="{
        showAddForm: false,
        formData: {
            jobId: '',
            note: ''
        },
        jobs: [
            { value: '', label: 'Select a job...' },
            { value: '1', label: 'Paint Job - Toyota Supra 2021' },
            { value: '2', label: 'Engine Customization - Honda Civic 2020' }
        ],
        notes: [
            {
                id: 1,
                job: 'Paint Job - Toyota Supra 2021',
                note: 'Surface preparation completed. Starting primer application tomorrow. All rust spots have been treated.',
                date: 'March 30, 2026 - 3:45 PM',
                mechanic: 'You'
            },
            {
                id: 2,
                job: 'Paint Job - Toyota Supra 2021',
                note: 'Vehicle inspection completed. Beginning disassembly and masking. Minor dent on rear bumper needs attention.',
                date: 'March 29, 2026 - 10:15 AM',
                mechanic: 'You'
            },
            {
                id: 3,
                job: 'Turbo Installation - Subaru WRX 2022',
                note: 'Turbo installation completed. Performed test runs. All systems functioning optimally. Customer notified.',
                date: 'March 20, 2026 - 4:30 PM',
                mechanic: 'You'
            }
        ],

        handleSubmit() {
            if (!this.formData.jobId || !this.formData.note.trim()) {
                showToast.error('Please select a job and enter a note');
                return;
            }
            const selectedJob = this.jobs.find(j => j.value === this.formData.jobId);
            this.notes.unshift({
                id: this.notes.length + 1,
                job: selectedJob ? selectedJob.label : '',
                note: this.formData.note,
                date: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) + ' - ' + new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' }),
                mechanic: 'You'
            });
            showToast.success('Service note added successfully!');
            this.formData = { jobId: '', note: '' };
            this.showAddForm = false;
        }
    }"
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
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Notes History</h2>
        <div class="space-y-4">
            <template x-for="note in notes" :key="note.id">
                <x-card>
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-[#E63946]/10 rounded-xl flex-shrink-0">
                            <x-icon name="clipboard-list" class="w-6 h-6 text-[#E63946]" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-3">
                                <div>
                                    <h3 class="font-bold text-gray-900 dark:text-white mb-1" x-text="note.job"></h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        <span x-text="note.date"></span> • By <span x-text="note.mechanic"></span>
                                    </p>
                                </div>
                            </div>
                            <p class="text-gray-700 dark:text-gray-300 leading-relaxed" x-text="note.note"></p>
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
            <li>• These notes are visible to customers for transparency</li>
        </ul>
    </x-card>
</div>
@endsection
