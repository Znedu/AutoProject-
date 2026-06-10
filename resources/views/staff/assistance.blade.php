@extends('layouts.dashboard')

@section('title', 'Customer Assistance | AutoProject+')

@section('content')
<div 
    x-data="{
        selectedTicket: parseInt(new URLSearchParams(window.location.search).get('id')) || null,
        replyMessage: '',
        selectedFilter: 'all',
        tickets: [
            {
                id: 1,
                customer: 'Juan Dela Cruz',
                subject: 'Question about paint warranty',
                message: 'I would like to know more about the warranty coverage for the paint job.',
                status: 'open',
                date: 'March 30, 2026',
                replies: []
            },
            {
                id: 2,
                customer: 'Maria Santos',
                subject: 'Reschedule booking request',
                message: 'I need to reschedule my April 5 appointment to April 8. Is this possible?',
                status: 'in-progress',
                date: 'March 28, 2026',
                replies: [
                    {
                        from: 'Staff',
                        message: 'Hello Maria, we can accommodate your request. I will check the availability for April 8.',
                        date: 'March 28, 2026 - 2:30 PM'
                    },
                    {
                        from: 'Customer',
                        message: 'Thank you! I appreciate your help.',
                        date: 'March 28, 2026 - 3:15 PM'
                    }
                ]
            },
            {
                id: 3,
                customer: 'Pedro Lopez',
                subject: 'Invoice inquiry',
                message: 'Can I get a detailed breakdown of the service costs for my turbo installation?',
                status: 'resolved',
                date: 'March 25, 2026',
                replies: [
                    {
                        from: 'Staff',
                        message: 'Hello Pedro, I have attached the detailed invoice to your account. You can download it from your booking page.',
                        date: 'March 25, 2026 - 11:00 AM'
                    },
                    {
                        from: 'Customer',
                        message: 'Perfect, thank you!',
                        date: 'March 25, 2026 - 11:30 AM'
                    }
                ]
            }
        ],

        handleSendReply(ticketId) {
            if (!this.replyMessage.trim()) {
                showToast.error('Please enter a reply message');
                return;
            }
            const ticket = this.tickets.find(t => t.id === ticketId);
            if (ticket) {
                ticket.replies.push({
                    from: 'Staff',
                    message: this.replyMessage,
                    date: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) + ' - ' + new Date().toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' })
                });
                ticket.status = 'in-progress';
                showToast.success('Reply sent to ticket #' + ticketId);
                this.replyMessage = '';
            }
        },

        handleResolveTicket(ticketId) {
            const ticket = this.tickets.find(t => t.id === ticketId);
            if (ticket) {
                ticket.status = 'resolved';
                showToast.success('Ticket #' + ticketId + ' marked as resolved');
            }
        },

        getFilteredTickets() {
            if (this.selectedFilter === 'all') return this.tickets;
            return this.tickets.filter(t => t.status === this.selectedFilter);
        },

        getSelectedTicketData() {
            return this.tickets.find(t => t.id === this.selectedTicket);
        }
    }"
    class="space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Customer Assistance</h1>
        <p class="text-gray-600 dark:text-gray-400">Respond to customer support tickets and inquiries.</p>
    </div>

    {{-- Filters --}}
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

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Tickets List --}}
        <div class="lg:col-span-1 space-y-3">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-3">Support Tickets</h2>
            <template x-if="getFilteredTickets().length === 0">
                <x-card>
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center py-4">No tickets found.</p>
                </x-card>
            </template>
            <template x-for="ticket in getFilteredTickets()" :key="ticket.id">
                <div @click="selectedTicket = ticket.id">
                    <x-card 
                        hover
                        class="cursor-pointer transition-all p-4"
                        ::class="selectedTicket === ticket.id ? 'ring-2 ring-[#E63946]' : ''"
                    >
                        <div class="space-y-2">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-bold text-gray-900 dark:text-white text-sm truncate" x-text="ticket.subject"></h3>
                                <x-status-badge ::status="ticket.status">
                                    <span x-text="ticket.status === 'open' ? 'Open' : (ticket.status === 'in-progress' ? 'In Progress' : 'Resolved')"></span>
                                </x-status-badge>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400" x-text="ticket.customer"></p>
                            <p class="text-xs text-gray-500" x-text="ticket.date"></p>
                        </div>
                    </x-card>
                </div>
            </template>
        </div>

        {{-- Ticket Details & Reply --}}
        <div class="lg:col-span-2">
            <template x-if="selectedTicket !== null && getSelectedTicketData()">
                <div class="space-y-6">
                    {{-- Ticket Info --}}
                    <x-card>
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2" x-text="getSelectedTicketData().subject"></h2>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Ticket #<span x-text="getSelectedTicketData().id"></span> • <span x-text="getSelectedTicketData().customer"></span> • <span x-text="getSelectedTicketData().date"></span>
                                </p>
                            </div>
                            <x-status-badge ::status="getSelectedTicketData().status">
                                <span x-text="getSelectedTicketData().status === 'open' ? 'Open' : (getSelectedTicketData().status === 'in-progress' ? 'In Progress' : 'Resolved')"></span>
                            </x-status-badge>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#0B0B0B] rounded-xl p-4 border border-gray-200 dark:border-white/5">
                            <p class="text-gray-800 dark:text-gray-200" x-text="getSelectedTicketData().message"></p>
                        </div>
                    </x-card>

                    {{-- Conversation Thread --}}
                    <template x-if="getSelectedTicketData().replies.length > 0">
                        <x-card>
                            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Conversation</h3>
                            <div class="space-y-4">
                                <template x-for="(reply, index) in getSelectedTicketData().replies" :key="index">
                                    <div 
                                        class="p-4 rounded-xl border"
                                        :class="reply.from === 'Staff' 
                                            ? 'bg-blue-50/50 dark:bg-blue-950/20 border-blue-200 dark:border-blue-900/50' 
                                            : 'bg-gray-50 dark:bg-[#151515] border-gray-200 dark:border-white/5'"
                                    >
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-bold text-gray-900 dark:text-white" x-text="reply.from"></span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400" x-text="reply.date"></span>
                                        </div>
                                        <p class="text-gray-700 dark:text-gray-300" x-text="reply.message"></p>
                                    </div>
                                </template>
                            </div>
                        </x-card>
                    </template>

                    {{-- Reply Form --}}
                    <template x-if="getSelectedTicketData().status !== 'resolved'">
                        <x-card>
                            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Send Reply</h3>
                            <div class="space-y-4">
                                <x-textarea
                                    x-model="replyMessage"
                                    placeholder="Type your response to the customer..."
                                    rows="6"
                                />
                                <div class="flex gap-3">
                                    <x-button
                                        variant="accent"
                                        @click="handleSendReply(getSelectedTicketData().id)"
                                    >
                                        Send Reply
                                    </x-button>
                                    <x-button
                                        variant="secondary"
                                        @click="handleResolveTicket(getSelectedTicketData().id)"
                                    >
                                        Mark as Resolved
                                    </x-button>
                                </div>
                            </div>
                        </x-card>
                    </template>

                    {{-- Resolved Banner --}}
                    <template x-if="getSelectedTicketData().status === 'resolved'">
                        <x-card class="bg-green-50 dark:bg-green-950/20 border-2 border-green-500">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-green-500 rounded-full text-white">
                                    <x-icon name="check-square" class="w-5 h-5 text-white" />
                                </div>
                                <div>
                                    <p class="font-bold text-green-800 dark:text-green-300">This ticket has been resolved</p>
                                    <p class="text-sm text-green-600 dark:text-green-400">The customer has been notified.</p>
                                </div>
                            </div>
                        </x-card>
                    </template>
                </div>
            </template>

            <template x-if="selectedTicket === null || !getSelectedTicketData()">
                <x-card class="h-64 flex items-center justify-center border-dashed border-2">
                    <div class="text-center text-gray-500 dark:text-gray-400">
                        <x-icon name="message-square" class="w-10 h-10 mx-auto mb-2 opacity-50" />
                        <p>Select a ticket to view details and respond</p>
                    </div>
                </x-card>
            </template>
        </div>
    </div>
</div>
@endsection
