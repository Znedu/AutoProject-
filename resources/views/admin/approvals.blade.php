@extends('layouts.dashboard')

@section('title', 'Booking Approval | AutoProject+')

@section('content')
<div
    x-data="{
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
                estimatedCost: 55000,
                notes: 'Full body kit from reputable manufacturer. Customer provided reference images.',
                submittedDate: 'March 28, 2026',
                reservationFee: {
                    amount: 200,
                    paymentMethod: 'gcash',
                    referenceNumber: '1234567890123',
                    verified: false
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
                estimatedCost: 32000,
                notes: 'Custom stainless steel exhaust system with sport muffler.',
                submittedDate: 'March 29, 2026',
                reservationFee: {
                    amount: 200,
                    paymentMethod: 'maya',
                    referenceNumber: '9876543210987',
                    verified: false
                }
            },
            {
                id: 3,
                customer: 'Ricardo Santos',
                contact: '+63 918 777 8888',
                service: 'Engine Customization',
                vehicle: 'Mitsubishi Lancer 2018',
                plateNumber: 'MNO 1122',
                preferredDate: 'April 12, 2026',
                preferredTime: '9:00 AM',
                status: 'pending',
                estimatedCost: 95000,
                notes: 'Full engine rebuild with performance upgrades. Timing belt, pistons, and ECU tune.',
                submittedDate: 'March 30, 2026',
                reservationFee: {
                    amount: 200,
                    paymentMethod: 'gcash',
                    referenceNumber: '5555666677778',
                    verified: false
                }
            }
        ],
        handleVerifyPayment(id) {
            const booking = this.bookings.find(b => b.id === id);
            if (booking) {
                booking.reservationFee.verified = true;
                showToast.success('Reservation fee payment verified for Booking ' + id + '!');
            }
        },
        handleAdjustCost(id) {
            const booking = this.bookings.find(b => b.id === id);
            if (booking) {
                const newCost = prompt('Adjust estimated cost for Booking ' + id + ':', booking.estimatedCost);
                if (newCost && !isNaN(newCost)) {
                    booking.estimatedCost = Number(newCost);
                    showToast.success('Cost updated to ₱' + Number(newCost).toLocaleString());
                }
            }
        },
        handleApprove(id) {
            const booking = this.bookings.find(b => b.id === id);
            if (booking) {
                if (!booking.reservationFee.verified) {
                    showToast.error('Please verify the reservation fee payment first before approving the booking.');
                    return;
                }
                booking.status = 'approved';
                showToast.success('Booking ' + id + ' approved!\n\nEstimated Cost: ₱' + booking.estimatedCost.toLocaleString() + '\n\nCustomer will be notified.');
                this.bookings = this.bookings.filter(b => b.id !== id);
            }
        },
        handleReject(id) {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason) {
                showToast.error('Booking ' + id + ' rejected.\n\nReason: ' + reason + '\n\nCustomer will be notified.');
                this.bookings = this.bookings.filter(b => b.id !== id);
            }
        }
    }"
    class="space-y-6"
>
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Booking Approval</h1>
        <p class="text-gray-600 dark:text-gray-400">Review and approve customer booking requests.</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Pending Approval</p>
            <p class="text-3xl font-bold text-[#E63946]" x-text="bookings.length"></p>
        </x-card>
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Approved Today</p>
            <p class="text-3xl font-bold text-green-500">8</p>
        </x-card>
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Rejected</p>
            <p class="text-3xl font-bold text-gray-500">2</p>
        </x-card>
        <x-card class="text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Total This Week</p>
            <p class="text-3xl font-bold text-[#457B9D]">15</p>
        </x-card>
    </div>

    {{-- Filters --}}
    <x-card>
        <div class="flex flex-wrap gap-2">
            <x-button variant="primary" size="sm">Pending Approval</x-button>
            <x-button variant="ghost" size="sm" @click="showToast.info('Filter: All Bookings')">All Bookings</x-button>
            <x-button variant="ghost" size="sm" @click="showToast.info('Filter: Approved')">Approved</x-button>
            <x-button variant="ghost" size="sm" @click="showToast.info('Filter: Rejected')">Rejected</x-button>
        </div>
    </x-card>

    {{-- Bookings List --}}
    <div class="space-y-6">
        <template x-if="bookings.length === 0">
            <x-card class="text-center py-12">
                <p class="text-gray-500 dark:text-gray-400 text-lg">No pending booking approvals.</p>
            </x-card>
        </template>

        <template x-for="booking in bookings" :key="booking.id">
            <x-card>
                <div class="space-y-6">
                    {{-- Title Header --}}
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-3 mb-3">
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="booking.service"></h3>
                                <x-status-badge status="pending">Awaiting Approval</x-status-badge>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Submitted: <span x-text="booking.submittedDate"></span> • Booking ID: BK-<span x-text="booking.id"></span>
                            </p>
                        </div>
                    </div>

                    {{-- Info Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pb-6 border-b border-gray-200 dark:border-white/10">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-3">Customer Information</h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500">Name</p>
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="booking.customer"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Contact</p>
                                    <p class="text-gray-700 dark:text-gray-300" x-text="booking.contact"></p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-3">Vehicle Details</h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500">Vehicle</p>
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="booking.vehicle"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Plate Number</p>
                                    <p class="text-gray-700 dark:text-gray-300" x-text="booking.plateNumber"></p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-3">Preferred Schedule</h4>
                            <div class="space-y-2 text-sm">
                                <div>
                                    <p class="text-xs text-gray-500">Date</p>
                                    <p class="font-medium text-gray-900 dark:text-white" x-text="booking.preferredDate"></p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Time</p>
                                    <p class="text-gray-700 dark:text-gray-300" x-text="booking.preferredTime"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notes & Cost --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-3">Service Notes</h4>
                            <div class="bg-gray-50 dark:bg-black/20 rounded-xl p-4 border border-gray-200 dark:border-white/5">
                                <p class="text-gray-700 dark:text-gray-300 text-sm leading-relaxed" x-text="booking.notes"></p>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-3">Cost Estimation</h4>
                            <div class="bg-gradient-red rounded-xl p-6 text-white shadow-lg shadow-[#E63946]/10">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <x-icon name="dollar-sign" class="w-8 h-8" />
                                        <div>
                                            <p class="text-xs text-white/80">Estimated Cost</p>
                                            <p class="text-3xl font-bold">₱<span x-text="booking.estimatedCost.toLocaleString()"></span></p>
                                        </div>
                                    </div>
                                </div>
                                <x-button
                                    variant="outline"
                                    size="sm"
                                    class="bg-white text-[#E63946] border-white hover:bg-gray-100 dark:hover:bg-gray-100"
                                    @click="handleAdjustCost(booking.id)"
                                >
                                    Adjust Cost
                                </x-button>
                            </div>
                        </div>
                    </div>

                    {{-- Reservation Fee Verification Card --}}
                    <div class="bg-blue-500/5 dark:bg-blue-500/10 border-2 border-blue-500/30 rounded-xl p-6">
                        <div class="flex items-center gap-2 mb-4 flex-wrap">
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white">Reservation Fee Payment Verification</h4>
                            <div class="ml-auto">
                                <template x-if="booking.reservationFee.verified">
                                    <span class="flex items-center gap-1.5 px-3 py-1 bg-green-500/10 text-green-500 border border-green-500/20 rounded-full text-xs font-semibold">
                                        <x-icon name="check-circle" class="w-4 h-4" />
                                        Verified
                                    </span>
                                </template>
                                <template x-if="!booking.reservationFee.verified">
                                    <span class="flex items-center gap-1.5 px-3 py-1 bg-yellow-500/10 text-yellow-500 border border-yellow-500/20 rounded-full text-xs font-semibold">
                                        <x-icon name="info" class="w-4 h-4" />
                                        Pending Verification
                                    </span>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div class="bg-white dark:bg-[#1E1E1E] rounded-xl p-4 border border-gray-200 dark:border-white/5 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1">Amount</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">₱<span x-text="booking.reservationFee.amount"></span></p>
                            </div>
                            <div class="bg-white dark:bg-[#1E1E1E] rounded-xl p-4 border border-gray-200 dark:border-white/5 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1">Payment Method</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white capitalize" x-text="booking.reservationFee.paymentMethod"></p>
                            </div>
                            <div class="bg-white dark:bg-[#1E1E1E] rounded-xl p-4 border border-gray-200 dark:border-white/5 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1">Reference Number</p>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white break-all select-all" x-text="booking.reservationFee.referenceNumber"></p>
                            </div>
                        </div>

                        <div class="bg-amber-500/10 border-l-4 border-amber-500 p-4 rounded-r-xl mb-4 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                            <div class="flex gap-2">
                                <x-icon name="info" class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p class="font-bold text-gray-900 dark:text-white mb-2">Verification Instructions:</p>
                                    <ol class="list-decimal list-inside space-y-1.5">
                                        <li>Check your <span class="font-semibold uppercase" x-text="booking.reservationFee.paymentMethod"></span> account for incoming payment</li>
                                        <li>Verify the reference number: <strong class="text-gray-900 dark:text-white select-all" x-text="booking.reservationFee.referenceNumber"></strong></li>
                                        <li>Confirm the amount is exactly ₱<span x-text="booking.reservationFee.amount"></span></li>
                                        <li>Click "Verify Payment" button once confirmed</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <template x-if="!booking.reservationFee.verified">
                            <x-button
                                variant="primary"
                                class="w-full md:w-auto"
                                @click="handleVerifyPayment(booking.id)"
                            >
                                <x-icon name="check-circle" class="w-5 h-5 mr-2" />
                                Verify Payment
                            </x-button>
                        </template>
                    </div>

                    {{-- Actions Footer --}}
                    <div class="flex flex-wrap gap-3 pt-6 border-t border-gray-200 dark:border-white/10">
                        <x-button
                            variant="accent"
                            ::disabled="!booking.reservationFee.verified"
                            ::class="!booking.reservationFee.verified ? 'opacity-50 cursor-not-allowed' : ''"
                            @click="handleApprove(booking.id)"
                        >
                            <x-icon name="check-circle" class="w-5 h-5 mr-2" />
                            Approve Booking
                        </x-button>
                        <x-button
                            variant="outline"
                            class="text-red-500 border-red-500/20 hover:bg-red-500/10 hover:border-red-500"
                            @click="handleReject(booking.id)"
                        >
                            <x-icon name="close" class="w-5 h-5 mr-2" />
                            Reject Booking
                        </x-button>
                        <x-button variant="secondary" @click="showToast.info('Opening chat with ' + booking.customer)">
                            Contact Customer
                        </x-button>
                    </div>
                </div>
            </x-card>
        </template>
    </div>
</div>
@endsection
