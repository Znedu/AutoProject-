@extends('layouts.dashboard')

@section('title', 'Support Tickets | AutoProject+')

@section('content')
<div 
    x-data="{
        showCreateForm: new URLSearchParams(window.location.search).has('subject'),
        selectedFilter: 'all',
        viewingTicket: null,
        replyMessage: '',
        formData: {
            subject: new URLSearchParams(window.location.search).get('subject') || '',
            message: ''
        },
        tickets: @js($tickets),
        ticketReplies: @js($ticketReplies),

        handleSubmitTicket() {
            fetch('/customer/support', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    subject: this.formData.subject,
                    message: this.formData.message
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.tickets.unshift(data.ticket);
                    showToast.success('Support ticket created successfully!');
                    this.formData = { subject: '', message: '' };
                    this.showCreateForm = false;
                    if (history.pushState) {
                        history.pushState(null, '', window.location.pathname);
                    }
                } else {
                    showToast.error('Failed to create ticket: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                showToast.error('An error occurred.');
            });
        },

        handleSubmitReply() {
            if (!this.viewingTicket || !this.replyMessage.trim()) return;
            fetch('/customer/support/' + this.viewingTicket + '/reply', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    message: this.replyMessage
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (!this.ticketReplies[this.viewingTicket]) {
                        this.ticketReplies[this.viewingTicket] = [];
                    }
                    this.ticketReplies[this.viewingTicket].push(data.reply);
                    const ticket = this.tickets.find(t => t.id === this.viewingTicket);
                    if (ticket) {
                        ticket.replies++;
                    }
                    this.replyMessage = '';
                    showToast.success('Reply sent successfully!');
                } else {
                    showToast.error('Failed to send reply: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(err => {
                showToast.error('An error occurred.');
            });
        },

        getFilteredTickets() {
            if (this.selectedFilter === 'all') return this.tickets;
            return this.tickets.filter(t => t.status === this.selectedFilter);
        }
    }"
    class="max-w-4xl mx-auto space-y-6 animate-fade-in"
>
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
                    <x-status-badge ::status="({{ $currentTicketData }}).status">
                        <span x-text="({{ $currentTicketData }}).status === 'open' ? 'Open' : (({{ $currentTicketData }}).status === 'in-progress' ? 'In Progress' : 'Resolved')"></span>
                    </x-status-badge>
                </div>

                {{-- Original Message --}}
                <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-xl">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-bold text-gray-900 dark:text-white">You</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">opened this ticket</span>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300" x-text="({{ $currentTicketData }}).message"></p>
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
            <template x-if="({{ $currentTicketData }}).status !== 'resolved'">
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
                            <x-button type="button" variant="outline" @click="replyMessage = ''" class="text-white bg-red-600 border-red-600 hover:bg-red-700">
                                Clear
                            </x-button>
                        </div>
                    </form>
                </x-card>
            </template>

            <template x-if="({{ $currentTicketData }}).status === 'resolved'">
                <x-card>
                    <div class="text-center py-6">
                        <p class="text-gray-600 dark:text-gray-400">This ticket has been resolved and is now closed.</p>
                    </div>
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
                            <button
                                type="button"
                                class="flex items-center gap-2 px-4 py-2 border-2 border-dashed border-gray-300 dark:border-white/10 rounded-xl hover:border-[#E63946] dark:hover:border-[#E63946] transition-colors cursor-pointer bg-white/5"
                            >
                                <x-icon name="message-square" class="w-5 h-5" />
                                <span>Choose file...</span>
                            </button>
                        </div>
                        <div class="flex gap-3">
                            <x-button type="submit" variant="accent" class="text-white bg-green-600 border-green-600 hover:bg-green-700">Submit Ticket</x-button>
                            <x-button type="button" variant="outline" @click="showCreateForm = false; formData.subject = ''; formData.message = '';" class="text-white bg-red-600 border-red-600 hover:bg-red-700">
                                Cancel
                            </x-button>
                        </div>
                    </form>
                </x-card>
            </div>

            {{-- Filter Filters --}}
            <x-card>
                <div class="flex flex-wrap gap-2">
                    <template x-for="filter in ['all', 'open', 'in-progress', 'resolved']" :key="filter">
                        <x-button
                            ::variant="selectedFilter === filter ? 'primary' : 'ghost'"
                            size="sm"
                            @click="selectedFilter = filter"
                            class="capitalize"
                            x-text="filter === 'all' ? 'All Tickets' : (filter === 'in-progress' ? 'In Progress' : filter)"
                        ></x-button>
                    </template>
                </div>
            </x-card>

            {{-- Tickets Grid/List --}}
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
                                    </div>
                                </div>
                                <x-status-badge ::status="ticket.status">
                                    <span x-text="ticket.status === 'open' ? 'Open' : (ticket.status === 'in-progress' ? 'In Progress' : 'Resolved')"></span>
                                </x-status-badge>
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
