@extends('layouts.dashboard')

@section('title', 'Support Tickets | AutoProject+')

@section('content')
<div
    x-data="customerSupport()"
    class="max-w-4xl mx-auto space-y-6 animate-fade-in"
>
    {{-- Full Image Viewer Modal --}}
    <div
        x-show="activeImageModal"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @keydown.escape.window="activeImageModal = null"
        class="fixed inset-0 z-50 flex flex-col p-4 sm:p-6 bg-black/95 backdrop-blur-md overflow-hidden"
        style="display: none;"
    >
        {{-- Top Navigation Bar with Back Button --}}
        <div class="flex-none w-full max-w-7xl mx-auto flex items-center justify-between mb-4">
            <button
                type="button"
                @click="activeImageModal = null"
                class="flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl font-semibold transition-colors cursor-pointer"
            >
                <svg class="w-5 h-5 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
                <span>Back</span>
            </button>
            <span class="text-sm text-gray-300 font-medium">Full Image View</span>
        </div>

        {{-- Full Image Container --}}
        <div class="flex-1 w-full min-h-0 flex items-center justify-center overflow-hidden">
            <img
                :src="activeImageModal"
                alt="Full View Attachment"
                class="max-w-full max-h-full object-contain rounded-xl shadow-2xl"
            />
        </div>
    </div>

    {{-- Ticket Details Overlay/View --}}
    <template x-if="viewingTicket">
        <div class="space-y-6">
            {{-- Back Button --}}
            <x-button variant="ghost" @click="viewingTicket = null" class="mb-4">
                <x-icon name="chevron-right" class="w-5 h-5 mr-2 inline-block transform rotate-180" />
                Back to Tickets
            </x-button>

            @php
                $currentTicketData = "tickets.find(t => t.id === viewingTicket)";
                $repliesData = "ticketReplies[viewingTicket] || []";
            @endphp

            {{-- Ticket Header Card --}}
            <x-card>
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold mb-2 text-gray-900 dark:text-white" x-text="({{ $currentTicketData }}).subject"></h1>
                        <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                            <span>Ticket #<span x-text="({{ $currentTicketData }}).id"></span></span>
                            <span>•</span>
                            <span x-text="({{ $currentTicketData }}).date"></span>
                        </div>
                </div>

                {{-- Original Message --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-bold text-gray-900 dark:text-white">You</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">opened this ticket</span>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300" x-text="({{ $currentTicketData }}).message"></p>
                    <template x-if="({{ $currentTicketData }}).attachment">
                        <div class="mt-3">
                            <p class="text-xs font-semibold text-gray-500 mb-1.5 dark:text-gray-400">Attached Image (Click for full view):</p>
                            <div 
                                @click="activeImageModal = ({{ $currentTicketData }}).attachment" 
                                class="group relative inline-block cursor-pointer overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-black/5"
                            >
                                <img 
                                    :src="({{ $currentTicketData }}).attachment" 
                                    alt="Ticket Attachment" 
                                    class="max-h-60 rounded-xl object-contain transition-transform duration-300 group-hover:scale-105" 
                                />
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white font-medium text-sm gap-2">
                                    <x-icon name="eye" class="w-5 h-5 text-white" />
                                    <span>View Full Image</span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </x-card>

            {{-- Replies Section --}}
            <div class="space-y-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Replies</h2>
                <template x-if="({{ $repliesData }}).length === 0">
                    <p class="text-gray-600 dark:text-gray-400 text-sm">No replies yet. Our customer assistance agents will respond shortly.</p>
                </template>
                <template x-for="reply in ({{ $repliesData }})" :key="reply.id">
                    <x-card>
                        <div class="flex items-start gap-4">
                            <div
                                class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white"
                                :class="reply.role === 'staff' ? 'bg-[#E63946]' : 'bg-gray-400 dark:bg-gray-600'"
                            >
                                <span x-text="reply.author.charAt(0).toUpperCase()"></span>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="font-bold text-gray-900 dark:text-white" x-text="reply.author"></span>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        <span x-text="reply.date"></span> at <span x-text="reply.time"></span>
                                    </span>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300" x-text="reply.message"></p>
                            </div>
                        </div>
                    </x-card>
                </template>
            </div>

            {{-- Reply Form --}}
            <template x-if="({{ $currentTicketData }}).status !== 'resolved' && ({{ $currentTicketData }}).status !== 'closed'">
                <x-card>
                    <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Add Reply</h3>
                    <form @submit.prevent="handleSubmitReply()" class="space-y-4">
                        <x-textarea
                            x-model="replyMessage"
                            placeholder="Type your reply here..."
                            rows="4"
                            required
                        />
                        <div class="flex gap-3">
                            <x-button type="submit" variant="accent" class="text-white bg-green-600 border-green-600 hover:bg-green-700">
                                Send Reply
                            </x-button>
                        </div>
                    </form>
                </x-card>
            </template>

            <template x-if="({{ $currentTicketData }}).status === 'resolved'">
                <x-card class="bg-green-50 dark:bg-green-950/20 border-2 border-green-500">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <p class="font-bold text-green-800 dark:text-green-300">This ticket has been resolved.</p>
                            <p class="text-sm text-green-600 dark:text-green-400">If your issue is solved, you can close this ticket. If you need further help, you may reopen it.</p>
                        </div>
                        <div class="flex gap-2">
                            <x-button variant="accent" @click="handleReopenTicket(viewingTicket)" class="bg-[#E63946] border-[#E63946] hover:bg-[#c1323e]">
                                Reopen Ticket
                            </x-button>
                            <x-button variant="outline" @click="handleCloseTicket(viewingTicket)" class="text-white bg-red-600 border-red-600 hover:bg-red-700">
                                Close Ticket
                            </x-button>
                        </div>
                    </div>
                </x-card>
            </template>

            <template x-if="({{ $currentTicketData }}).status === 'closed'">
                <x-card class="bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-center py-6">
                    <p class="text-gray-600 dark:text-gray-400 font-medium">This ticket is closed.</p>
                </x-card>
            </template>
        </div>
    </template>

    {{-- Support List & Create Views --}}
    <template x-if="!viewingTicket">
        <div class="space-y-6">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Support Tickets</h1>
                    <p class="text-gray-600 dark:text-gray-400">Get help with your bookings and services.</p>
                </div>
                <x-button variant="accent" @click="showCreateForm = !showCreateForm" class="text-white">
                    <x-icon name="check-square" class="w-5 h-5 mr-2 inline-block text-white" />
                    New Ticket
                </x-button>
            </div>

            {{-- Create Ticket Form --}}
            <div x-show="showCreateForm" x-collapse>
                <x-card>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Create Support Ticket</h2>
                    <form @submit.prevent="handleSubmitTicket()" class="space-y-4">
                        <x-input
                            label="Subject"
                            x-model="formData.subject"
                            placeholder="Brief description of your issue"
                            required
                        />
                        <x-textarea
                            label="Message"
                            x-model="formData.message"
                            placeholder="Provide detailed information about your concern..."
                            rows="5"
                            required
                        />
                        <div>
                            <label class="block mb-2 text-gray-900 dark:text-white font-medium">Attach Image (Optional)</label>
                            <input 
                                type="file" 
                                x-ref="fileInput" 
                                @change="handleFileSelect($event)" 
                                accept="image/png,image/jpeg,image/jpg,image/webp,image/gif" 
                                class="hidden" 
                            />
                            <div class="flex flex-wrap items-center gap-3">
                                <button
                                    type="button"
                                    @click="$refs.fileInput.click()"
                                    class="flex items-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 dark:border-white/10 rounded-xl hover:border-[#E63946] dark:hover:border-[#E63946] transition-colors cursor-pointer bg-white/5 text-gray-700 dark:text-gray-200"
                                >
                                    <x-icon name="message-square" class="w-5 h-5" />
                                    <span x-text="selectedFileName ? selectedFileName : 'Choose file...'"></span>
                                </button>
                                <template x-if="selectedFile">
                                    <div class="flex items-center gap-2">
                                        <template x-if="imagePreview">
                                            <img :src="imagePreview" class="w-10 h-10 object-cover rounded-lg border border-gray-300 dark:border-gray-700" alt="Preview" />
                                        </template>
                                        <button
                                            type="button"
                                            @click="clearFile()"
                                            class="px-2 py-1 text-xs text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-lg border border-red-200 dark:border-red-800 transition-colors font-medium"
                                        >
                                            Remove file
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <x-button type="submit" variant="accent" class="text-white bg-green-600 border-green-600 hover:bg-green-700">Submit Ticket</x-button>
                            <x-button type="button" variant="outline" @click="showCreateForm = false; formData.subject = ''; formData.message = ''; clearFile();" class="text-white bg-red-600 border-red-600 hover:bg-red-700">
                                Cancel
                            </x-button>
                        </div>
                    </form>
                </x-card>
            </div>

            {{-- Filters --}}
            <x-card>
                <div class="flex flex-wrap gap-2">
                    <template x-for="filter in ['all', 'open', 'in_progress', 'resolved']" :key="filter">
                        <x-button
                            ::variant="selectedFilter === filter ? 'primary' : 'ghost'"
                            size="sm"
                            @click="selectedFilter = filter"
                            class="capitalize"
                            x-text="filter === 'all' ? 'All Tickets' : (filter === 'in_progress' ? 'In Progress' : filter)"
                        ></x-button>
                    </template>
                </div>
            </x-card>

            {{-- Tickets List --}}
            <div class="space-y-4">
                <template x-if="getFilteredTickets().length === 0">
                    <x-card>
                        <div class="text-center py-8">
                            <p class="text-gray-600 dark:text-gray-400">No tickets found for this filter.</p>
                        </div>
                    </x-card>
                </template>

                <template x-for="ticket in getFilteredTickets()" :key="ticket.id">
                    <x-card hover>
                        <div class="flex flex-col gap-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-bold mb-2 text-gray-900 dark:text-white" x-text="ticket.subject"></h3>
                                    <p class="mb-3 text-gray-700 dark:text-gray-300" x-text="ticket.message"></p>
                                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                        <span>Ticket #<span x-text="ticket.id"></span></span>
                                        <span>•</span>
                                        <span x-text="ticket.date"></span>
                                        <span>•</span>
                                        <span><span x-text="ticket.replies"></span> reply/replies</span>
                                        <span>•</span>
                                        <span class="capitalize" x-text="'Status: ' + (ticket.status === 'open' ? 'Open' : (ticket.status === 'in_progress' ? 'In Progress' : (ticket.status === 'resolved' ? 'Resolved' : 'Closed')))"></span>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <x-button
                                    variant="secondary"
                                    size="sm"
                                    @click="viewingTicket = ticket.id; replyMessage = '';"
                                    x-text="ticket.status === 'resolved' ? 'View Details' : 'View & Reply'"
                                ></x-button>
                            </div>
                        </div>
                    </x-card>
                </template>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
    function customerSupport() {
        return {
            showCreateForm: new URLSearchParams(window.location.search).has('subject'),
            selectedFilter: 'all',
            viewingTicket: null,
            activeImageModal: null,
            replyMessage: '',
            formData: {
                subject: new URLSearchParams(window.location.search).get('subject') || '',
                message: ''
            },
            tickets: @json($tickets),
            ticketReplies: @json($ticketReplies),

            selectedFile: null,
            selectedFileName: '',
            imagePreview: null,

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    if (!file.type.startsWith('image/')) {
                        showToast.error('Please select an image file (JPG, PNG, WEBP, GIF)');
                        this.clearFile();
                        return;
                    }
                    if (file.size > 5 * 1024 * 1024) {
                        showToast.error('Image size must be less than 5MB');
                        this.clearFile();
                        return;
                    }
                    this.selectedFile = file;
                    this.selectedFileName = file.name;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imagePreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            clearFile() {
                this.selectedFile = null;
                this.selectedFileName = '';
                this.imagePreview = null;
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },

            handleSubmitTicket() {
                const bodyData = new FormData();
                bodyData.append('subject', this.formData.subject);
                bodyData.append('message', this.formData.message);
                if (this.selectedFile) {
                    bodyData.append('attachment', this.selectedFile);
                }

                fetch('/customer/support', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: bodyData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.tickets.unshift(data.ticket);
                        showToast.success('Support ticket created successfully!');
                        this.formData = { subject: '', message: '' };
                        this.clearFile();
                        this.showCreateForm = false;
                        if (history.pushState) {
                            history.pushState(null, '', window.location.pathname);
                        }
                    } else {
                        showToast.error('Failed to create ticket: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(() => showToast.error('An error occurred while creating ticket.'));
            },

            handleSubmitReply() {
                if (!this.viewingTicket || !this.replyMessage.trim()) return;
                fetch('/customer/support/' + this.viewingTicket + '/reply', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message: this.replyMessage })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (!this.ticketReplies[this.viewingTicket]) {
                            this.ticketReplies[this.viewingTicket] = [];
                        }
                        this.ticketReplies[this.viewingTicket].push(data.reply);
                        const ticket = this.tickets.find(t => t.id === this.viewingTicket);
                        if (ticket) ticket.replies++;
                        this.replyMessage = '';
                        showToast.success('Reply sent successfully!');
                    } else {
                        showToast.error('Failed to send reply: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(() => showToast.error('An error occurred.'));
            },

            getFilteredTickets() {
                if (this.selectedFilter === 'all') return this.tickets;
                return this.tickets.filter(t => t.status === this.selectedFilter);
            },

            handleReopenTicket(ticketId) {
                fetch('/customer/support/' + ticketId + '/reopen', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const ticket = this.tickets.find(t => t.id === ticketId);
                        if (ticket) ticket.status = data.status;
                        showToast.success('Ticket reopened successfully!');
                    } else {
                        showToast.error('Failed to reopen ticket.');
                    }
                })
                .catch(() => showToast.error('An error occurred.'));
            },

            handleCloseTicket(ticketId) {
                fetch('/customer/support/' + ticketId + '/close', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const ticket = this.tickets.find(t => t.id === ticketId);
                        if (ticket) ticket.status = data.status;
                        showToast.info('Ticket closed.');
                    } else {
                        showToast.error('Failed to close ticket.');
                    }
                })
                .catch(() => showToast.error('An error occurred.'));
            }
        };
    }
</script>
@endpush
