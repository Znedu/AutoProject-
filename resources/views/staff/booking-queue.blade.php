@extends('layouts.dashboard')

@section('title', 'Booking Queue | AutoProject+')

@section('content')
<div 
    x-data="{
        verifiedPayments: [],
        selectedFilter: new URLSearchParams(window.location.search).get('id') ? 'pending' : 'all',
        selectedBookingId: parseInt(new URLSearchParams(window.location.search).get('id')) || null,
        bookings: [
            {
                id: 1,
                customer: 'Carlos Reyes',
                contact: '+63 915 222 3333',
                service: 'Body Kit Installation',
                vehicle: 'Mazda RX-7 2019',
                plateNumber: 'DEF 9012',
                preferredDate: 'April 8, 2026',
                preferredTime: '10:00 AM',
                status: 'pending',
                estimatedCost: '₱55,000',
                notes: 'Full body kit from reputable manufacturer',
                reservationFee: {
                    amount: 200,
                    paid: true,
                    paymentMethod: 'GCash',
                    referenceNumber: 'GCASH-20260407-1234567890',
                    paymentDate: 'April 7, 2026',
                    paymentTime: '3:45 PM'
                }
            },
            {
                id: 2,
                customer: 'Ana Garcia',
                contact: '+63 920 444 5555',
                service: 'Exhaust Fabrication',
                vehicle: 'Nissan Skyline 2020',
                plateNumber: 'JKL 7890',
                preferredDate: 'April 10, 2026',
                preferredTime: '2:00 PM',
                status: 'pending',
                estimatedCost: '₱32,000',
                notes: 'Custom stainless steel exhaust system',
                reservationFee: {
                    amount: 200,
                    paid: true,
                    paymentMethod: 'Maya',
                    referenceNumber: 'MAYA-20260408-9876543210',
                    paymentDate: 'April 8, 2026',
                    paymentTime: '10:20 AM'
                }
            }
        ],

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

        handleVerifyPayment(id) {
            if (confirm('Have you verified that the reference number matches the payment received in AutoProject-D GCash/Maya account?')) {
                this.verifiedPayments.push(id);
                showToast.success('Payment verified! You can now approve this booking.');
            }
        },

        handleApprove(id, booking) {
            if (!this.verifiedPayments.includes(id)) {
                showToast.error('Please verify the reservation fee payment before approving the booking.');
                return;
            }
            booking.status = 'approved';
            showToast.success('Booking #' + id + ' approved! Customer will be notified.');
        },

        handleReject(id, booking) {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason) {
                booking.status = 'rejected';
                showToast.error('Booking #' + id + ' rejected. Reason: ' + reason);
            }
        },

        handleSchedule(id, booking) {
            booking.status = 'scheduled';
            showToast.info('Opening schedule editor for booking #' + id + '...');
        },

        getFilteredBookings() {
            if (this.selectedFilter === 'all') return this.bookings;
            return this.bookings.filter(b => b.status === this.selectedFilter);
        }
    }"
    class="space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">Booking Queue</h1>
        <p class="text-gray-600 dark:text-gray-400">Review and manage customer booking requests.</p>
        <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-xl">
            <div class="flex items-start gap-2">
                <x-icon name="info" class="text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0 w-5 h-5" />
                <p class="text-sm text-blue-800 dark:text-blue-200">
                    <strong>Payment Verification Required:</strong> Before approving any booking, verify that the customer's reference number matches the payment received in AutoProject-D's GCash/Maya account.
                </p>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <x-card>
        <div class="flex flex-wrap gap-2">
            <template x-for="filter in ['all', 'pending', 'approved', 'scheduled', 'rejected']" :key="filter">
                <x-button
                    ::variant="selectedFilter === filter ? 'primary' : 'ghost'"
                    size="sm"
                    @click="selectedFilter = filter"
                    class="capitalize"
                    x-text="filter === 'all' ? 'All Bookings' : (filter === 'pending' ? 'Pending Review' : filter)"
                ></x-button>
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

                        {{-- Payment Verification Section --}}
                        <div 
                            class="p-4 rounded-xl border-2 transition-colors duration-300"
                            :class="verifiedPayments.includes(booking.id)
                                ? 'bg-green-50 dark:bg-green-950/30 border-green-500'
                                : (booking.reservationFee.paid
                                    ? 'bg-amber-50 dark:bg-amber-950/30 border-amber-500'
                                    : 'bg-red-50 dark:bg-red-950/30 border-red-500')"
                        >
                            <div class="flex items-start gap-3">
                                <div class="p-2 bg-white dark:bg-gray-800 rounded-lg">
                                    <x-icon 
                                        name="dollar-sign" 
                                        class="w-6 h-6 flex-shrink-0" 
                                        ::class="verifiedPayments.includes(booking.id)
                                            ? 'text-green-600'
                                            : (booking.reservationFee.paid
                                                ? 'text-amber-600'
                                                : 'text-red-600')"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-4 mb-3">
                                        <div>
                                            <h4 class="text-sm font-bold mb-1 text-gray-900 dark:text-white">
                                                Reservation Fee (₱<span x-text="booking.reservationFee.amount"></span>)
                                            </h4>
                                            <template x-if="verifiedPayments.includes(booking.id)">
                                                <div class="flex items-center gap-2">
                                                    <x-icon name="check-square" class="w-4 h-4 text-green-600" />
                                                    <span class="text-sm font-medium text-green-600">Payment Verified</span>
                                                </div>
                                            </template>
                                            <template x-if="!verifiedPayments.includes(booking.id) && booking.reservationFee.paid">
                                                <div class="flex items-center gap-2">
                                                    <x-icon name="info" class="w-4 h-4 text-amber-600" />
                                                    <span class="text-sm font-medium text-amber-600">Pending Verification</span>
                                                </div>
                                            </template>
                                            <template x-if="!booking.reservationFee.paid">
                                                <div class="flex items-center gap-2">
                                                    <x-icon name="check-square" class="w-4 h-4 text-red-600" />
                                                    <span class="text-sm font-medium text-red-600">Not Paid</span>
                                                </div>
                                            </template>
                                        </div>
                                        <template x-if="booking.reservationFee.paid && !verifiedPayments.includes(booking.id)">
                                            <x-button
                                                size="sm"
                                                variant="outline"
                                                @click="handleVerifyPayment(booking.id)"
                                                class="text-green-600 border-green-600 hover:bg-green-50 dark:hover:bg-green-950/30 dark:text-green-400 dark:border-green-400"
                                            >
                                                Verify Payment
                                            </x-button>
                                        </template>
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
                                                    <x-button
                                                        size="sm"
                                                        variant="ghost"
                                                        @click="navigator.clipboard.writeText(booking.reservationFee.referenceNumber); showToast.success('Reference number copied to clipboard')"
                                                        class="text-xs"
                                                    >
                                                        Copy
                                                    </x-button>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="!verifiedPayments.includes(booking.id) && booking.reservationFee.paid">
                                        <div class="mt-3 p-3 bg-white dark:bg-[#0B0B0B] rounded-xl border border-amber-300 dark:border-amber-700">
                                            <p class="text-xs text-amber-800 dark:text-amber-200 font-medium">
                                                ⚠️ Important: Verify this reference number matches the payment received in AutoProject-D's <span x-text="booking.reservationFee.paymentMethod"></span> account before approving.
                                            </p>
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

                        {{-- Actions --}}
                        <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200 dark:border-white/10">
                            <x-button
                                variant="outline"
                                size="sm"
                                @click="handleApprove(booking.id, booking)"
                                ::disabled="!verifiedPayments.includes(booking.id)"
                                ::class="verifiedPayments.includes(booking.id)
                                    ? 'text-green-600 border-green-600 hover:bg-green-50 dark:hover:bg-green-950/30 dark:text-green-400 dark:border-green-400'
                                    : 'opacity-50 cursor-not-allowed border-gray-300 dark:border-white/10 text-gray-400'"
                            >
                                Approve Booking
                                <template x-if="!verifiedPayments.includes(booking.id)">
                                    <span class="ml-2 text-xs font-normal text-gray-500">(Verify Payment First)</span>
                                </template>
                            </x-button>
                            <x-button
                                variant="secondary"
                                size="sm"
                                @click="handleSchedule(booking.id, booking)"
                            >
                                Schedule Service
                            </x-button>
                            <x-button
                                variant="outline"
                                size="sm"
                                @click="handleReject(booking.id, booking)"
                                class="text-red-600 border-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 dark:text-red-400 dark:border-red-400"
                            >
                                Reject Booking
                            </x-button>
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
