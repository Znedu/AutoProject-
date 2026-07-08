@extends('layouts.dashboard')

@section('title', 'Booking Queue | AutoProject+')

@section('content')
<div 
    x-data="bookingQueue()"
    class="space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Booking Queue</h1>
        <p class="text-gray-600 dark:text-gray-400">Review and schedule customer booking requests.</p>
    </div>

    {{-- Filters --}}
    <x-card>
        <div class="flex flex-wrap gap-2">
            <template x-for="filter in ['all', 'pending', 'approved', 'scheduled', 'rejected']" :key="filter">
                <button
                    type="button"
                    @click="selectedFilter = filter"
                    class="inline-flex items-center justify-center rounded-xl font-semibold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer px-4 py-2 text-sm capitalize"
                    :class="(selectedFilter === filter ? 'bg-gray-200 dark:bg-[#151515] text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 hover:border-[#E63946] hover:shadow-lg hover:shadow-[#E63946]/20' : 'text-gray-600 dark:text-[#B8B8B8] hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5')"
                    x-text="filter === 'all' ? 'All Bookings' : (filter === 'pending' ? 'Pending Review' : filter)"
                ></button>
            </template>
        </div>
    </x-card>

    {{-- Bookings List --}}
    <div class="space-y-4">
        <template x-if="getFilteredBookings().length === 0">
            <x-card>
                <div class="text-center py-8">
                    <p class="text-gray-600 dark:text-gray-400">No bookings found for this filter.</p>
                </div>
            </x-card>
        </template>

        <template x-for="booking in getFilteredBookings()" :key="booking.id">
            <div :id="'booking-' + booking.id">
                <x-card ::class="selectedBookingId === booking.id ? 'ring-2 ring-[#E63946]' : ''">
                    <div class="space-y-4">
                        {{-- Header --}}
                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-3 mb-3">
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="booking.service"></h3>
                                    <x-status-badge ::status="booking.status">
                                        <span x-text="booking.status === 'pending' ? 'Pending Review' : (booking.status === 'approved' ? 'Approved' : (booking.status === 'scheduled' ? 'Scheduled' : 'Rejected'))"></span>
                                    </x-status-badge>
                                    <template x-if="booking.isWalkIn">
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded bg-[#D2781A]/10 text-[#D2781A] border border-[#D2781A]/20">Walk-In</span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Booking Details --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <h4 class="text-sm font-medium mb-2 text-gray-900 dark:text-white">Customer Details</h4>
                                <p class="font-bold text-gray-900 dark:text-white" x-text="booking.customer"></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="booking.contact"></p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium mb-2 text-gray-900 dark:text-white">Vehicle Details</h4>
                                <p class="font-bold text-gray-900 dark:text-white" x-text="booking.vehicle"></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Plate: <span x-text="booking.plateNumber"></span></p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium mb-2 text-gray-900 dark:text-white">Preferred Schedule</h4>
                                <p class="font-bold text-gray-900 dark:text-white" x-text="booking.preferredDate"></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400" x-text="booking.preferredTime"></p>
                            </div>
                        </div>

                        {{-- Reservation Fee (Read Only info) --}}
                        <div 
                            class="p-4 rounded-xl border transition-colors duration-300 bg-gray-50 dark:bg-white/5 border-gray-200 dark:border-white/10"
                        >
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-white dark:bg-gray-800 rounded-lg">
                                    <x-icon 
                                        name="dollar-sign" 
                                        class="w-6 h-6 flex-shrink-0" 
                                        ::class="booking.reservationFee.paid ? 'text-green-600' : 'text-red-600'"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4 mb-3">
                                        <div>
                                            <h4 class="text-sm font-bold mb-1 text-gray-900 dark:text-white">
                                                Reservation Fee (₱<span x-text="booking.reservationFee.amount"></span>)
                                            </h4>
                                            <div class="flex items-center gap-2">
                                                <template x-if="booking.reservationFee.paid">
                                                    <span class="text-sm font-medium text-green-600">Paid (Reference Verified / In-Store Collected)</span>
                                                </template>
                                                <template x-if="!booking.reservationFee.paid">
                                                    <span class="text-sm font-medium text-red-600">Pending Payment</span>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <template x-if="booking.reservationFee.paid">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400 mb-1">Payment Method</p>
                                                <p class="font-medium text-gray-900 dark:text-white" x-text="booking.reservationFee.paymentMethod"></p>
                                            </div>
                                            <div>
                                                <p class="text-gray-600 dark:text-gray-400 mb-1">Payment Date & Time</p>
                                                <p class="font-medium text-gray-900 dark:text-white">
                                                    <span x-text="booking.reservationFee.paymentDate"></span> • <span x-text="booking.reservationFee.paymentTime"></span>
                                                </p>
                                            </div>
                                            <div class="md:col-span-2">
                                                <p class="text-gray-600 dark:text-gray-400 mb-1">Reference Number</p>
                                                <div class="flex items-center gap-2">
                                                    <code class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 rounded font-mono text-sm text-gray-900 dark:text-white border border-gray-200 dark:border-white/10" x-text="booking.reservationFee.referenceNumber"></code>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Cost & Notes --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-200 dark:border-white/10">
                            <div>
                                <h4 class="text-sm font-medium mb-2 text-gray-900 dark:text-white">Estimated Cost</h4>
                                <p class="text-2xl font-bold text-[#E63946]" x-text="booking.estimatedCost"></p>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium mb-2 text-gray-900 dark:text-white">Additional Notes</h4>
                                <p class="text-gray-700 dark:text-gray-300" x-text="booking.notes"></p>
                            </div>
                        </div>

                        {{-- Inline Scheduler Form --}}
                        <div x-show="showSchedulerId === booking.id" x-cloak class="p-4 bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl space-y-4">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Schedule Confirmation</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Scheduled Date</label>
                                    <input type="date" x-model="scheduledDate" class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white text-sm focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">Scheduled Time</label>
                                    <select x-model="scheduledTime" class="w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white text-sm focus:outline-none">
                                        <option value="">Select time...</option>
                                        @foreach (['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00'] as $timeOption)
                                            <option value="{{ $timeOption }}">{{ \Carbon\Carbon::createFromFormat('H:i', $timeOption)->format('g:i A') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex gap-2 justify-end">
                                <x-button variant="ghost" size="sm" @click="showSchedulerId = null">Cancel</x-button>
                                <x-button variant="accent" size="sm" @click="handleSchedule(booking.id, booking)">Confirm Schedule</x-button>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200 dark:border-white/10" x-show="showSchedulerId !== booking.id">
                            <template x-if="booking.status !== 'scheduled' && booking.status !== 'rejected' && booking.status !== 'cancelled'">
                                <x-button
                                    variant="secondary"
                                    size="sm"
                                    @click="openScheduler(booking)"
                                >
                                    Schedule Service
                                </x-button>
                            </template>
                            <template x-if="booking.status === 'scheduled'">
                                <span class="text-sm font-semibold text-green-600 flex items-center gap-1">
                                    <x-icon name="check-square" class="w-4 h-4" /> Scheduled
                                </span>
                            </template>
                        </div>
                    </div>
                </x-card>
            </div>
        </template>
    </div>

    {{-- Stats Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-card class="text-center p-4">
            <p class="text-xs mb-1 text-gray-600 dark:text-gray-400 uppercase tracking-wider font-semibold">Pending Review</p>
            <p class="text-3xl font-extrabold text-[#E63946]" x-text="bookings.filter(b => b.status === 'pending').length"></p>
        </x-card>
        <x-card class="text-center p-4">
            <p class="text-xs mb-1 text-gray-600 dark:text-gray-400 uppercase tracking-wider font-semibold">Approved</p>
            <p class="text-3xl font-extrabold text-green-500" x-text="bookings.filter(b => b.status === 'approved').length"></p>
        </x-card>
        <x-card class="text-center p-4">
            <p class="text-xs mb-1 text-gray-600 dark:text-gray-400 uppercase tracking-wider font-semibold">Scheduled</p>
            <p class="text-3xl font-extrabold text-[#457B9D]" x-text="bookings.filter(b => b.status === 'scheduled').length"></p>
        </x-card>
        <x-card class="text-center p-4">
            <p class="text-xs mb-1 text-gray-600 dark:text-gray-400 uppercase tracking-wider font-semibold">Rejected</p>
            <p class="text-3xl font-extrabold text-gray-600 dark:text-gray-500" x-text="bookings.filter(b => b.status === 'rejected').length"></p>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function bookingQueue() {
        return {
            selectedFilter: new URLSearchParams(window.location.search).get('id') ? 'pending' : 'all',
            selectedBookingId: parseInt(new URLSearchParams(window.location.search).get('id')) || null,
            bookings: @json($bookings),
            showSchedulerId: null,
            scheduledDate: '',
            scheduledTime: '',

            init() {
                if (this.selectedBookingId) {
                    this.$nextTick(() => {
                        const el = document.getElementById('booking-' + this.selectedBookingId);
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    });
                }
            },

            openScheduler(booking) {
                this.showSchedulerId = booking.id;
                this.scheduledDate = booking.preferredDate || '';
                this.scheduledTime = booking.preferredTime || '';
            },

            handleSchedule(id, booking) {
                fetch('/staff/bookings/' + id + '/schedule', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        scheduled_date: this.scheduledDate,
                        scheduled_time: this.scheduledTime
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        booking.status = 'scheduled';
                        this.showSchedulerId = null;
                        showToast.success('Booking #' + id + ' scheduled successfully!');
                        booking.preferredDate = this.scheduledDate;
                        booking.preferredTime = this.scheduledTime;
                    } else {
                        showToast.error('Failed to schedule booking: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(err => showToast.error('An error occurred.'));
            },

            getFilteredBookings() {
                if (this.selectedFilter === 'all') return this.bookings;
                return this.bookings.filter(b => b.status === this.selectedFilter);
            }
        };
    }
</script>
@endpush
