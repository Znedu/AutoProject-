@extends('layouts.dashboard')

@section('title', 'My Bookings | AutoProject+')

@section('content')
<div 
    x-data="{
        selectedFilter: 'all',
        canceledBookings: [],
        bookings: [
            { id: 1, service: 'Engine Customization', vehicle: 'Honda Civic 2020', plateNumber: 'ABC 1234', date: 'April 5, 2026', time: '10:00 AM', status: 'confirmed', estimatedCost: '₱75,000', reservationFeePaid: true, reservationFeeVerified: true },
            { id: 2, service: 'Paint Job', vehicle: 'Toyota Supra 2021', plateNumber: 'XYZ 5678', date: 'March 28, 2026', time: '2:00 PM', status: 'in-progress', estimatedCost: '₱45,000', reservationFeePaid: true, reservationFeeVerified: true },
            { id: 3, service: 'Body Kit Installation', vehicle: 'Mazda RX-7 2019', plateNumber: 'DEF 9012', date: 'March 25, 2026', time: '9:00 AM', status: 'pending', estimatedCost: '₱55,000', reservationFeePaid: true, reservationFeeVerified: false },
            { id: 4, service: 'Turbo Installation', vehicle: 'Subaru WRX 2022', plateNumber: 'GHI 3456', date: 'March 20, 2026', time: '11:00 AM', status: 'completed', estimatedCost: '₱120,000', reservationFeePaid: true, reservationFeeVerified: true },
            { id: 5, service: 'Exhaust Fabrication', vehicle: 'Nissan Skyline 2020', plateNumber: 'JKL 7890', date: 'March 15, 2026', time: '3:00 PM', status: 'rejected', estimatedCost: '₱28,000', reservationFeePaid: false, reservationFeeVerified: false }
        ],

        getStatusDisplay(status) {
            const displays = {
                'pending': 'Pending Verification',
                'approved': 'Approved',
                'rejected': 'Rejected',
                'waiting-payment': 'Waiting Payment',
                'confirmed': 'Confirmed',
                'in-progress': 'In Progress',
                'completed': 'Completed'
            };
            return displays[status] || status;
        },

        handleCancelBooking(id) {
            if (confirm('Are you sure you want to cancel this booking?')) {
                this.canceledBookings.push(id);
                showToast.success('Booking canceled successfully');
            }
        },

        handleContactSupport(id, serviceName) {
            window.location.href = '{{ url('/customer/support') }}?subject=' + encodeURIComponent('Issue with Booking #' + id + ' - ' + serviceName);
        },

        getFilteredBookings() {
            let active = this.bookings.filter(b => !this.canceledBookings.includes(b.id));
            if (this.selectedFilter === 'all') return active;
            return active.filter(b => b.status === this.selectedFilter);
        }
    }"
    class="space-y-6 animate-fade-in"
>
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">My Bookings</h1>
            <p class="text-gray-600 dark:text-gray-400">View and manage all your service bookings.</p>
        </div>
        <a href="{{ url('/customer/book-service') }}">
            <x-button variant="accent" class="text-white">New Booking</x-button>
        </a>
    </div>

    {{-- Filters --}}
    <x-card>
        <div class="flex flex-wrap gap-2">
            <template x-for="filter in ['all', 'pending', 'confirmed', 'in-progress', 'completed']" :key="filter">
                <x-button
                    ::variant="selectedFilter === filter ? 'primary' : 'ghost'"
                    size="sm"
                    @click="selectedFilter = filter"
                    class="capitalize"
                    x-text="filter === 'all' ? 'All' : (filter === 'in-progress' ? 'In Progress' : filter)"
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
            <x-card hover>
                <div class="flex flex-col lg:flex-row gap-6">
                    <div class="flex-1 space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-bold mb-1 text-gray-900 dark:text-white" x-text="booking.service"></h3>
                                <p class="text-gray-700 dark:text-gray-300">
                                    <span x-text="booking.vehicle"></span> • <span x-text="booking.plateNumber"></span>
                                </p>
                            </div>
                            <x-status-badge ::status="booking.status">
                                <span x-text="getStatusDisplay(booking.status)"></span>
                            </x-status-badge>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Date</p>
                                <p class="font-medium text-gray-900 dark:text-white" x-text="booking.date"></p>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Time</p>
                                <p class="font-medium text-gray-900 dark:text-white" x-text="booking.time"></p>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400">Estimated Cost</p>
                                <p class="font-medium text-[#E63946]" x-text="booking.estimatedCost"></p>
                            </div>
                        </div>

                        {{-- Reservation Fee Status --}}
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-700 dark:text-gray-300">Reservation Fee (₱200):</span>
                                <template x-if="booking.reservationFeePaid">
                                    <template x-if="booking.reservationFeeVerified">
                                        <span class="flex items-center gap-1 text-green-600 font-medium">
                                            <x-icon name="check-square" class="w-4 h-4 text-green-600 inline" />
                                            Paid & Verified
                                        </span>
                                    </template>
                                </template>
                                <template x-if="booking.reservationFeePaid && !booking.reservationFeeVerified">
                                    <span class="flex items-center gap-1 text-amber-600 font-medium">
                                        <x-icon name="info" class="w-4 h-4 text-amber-600 inline" />
                                        Pending Verification
                                    </span>
                                </template>
                                <template x-if="!booking.reservationFeePaid">
                                    <span class="flex items-center gap-1 text-red-600 font-medium">
                                        <x-icon name="x" class="w-4 h-4 text-red-600 inline" />
                                        Not Paid
                                    </span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="flex lg:flex-col gap-2 justify-end">
                        <a :href="'{{ url('/customer/track') }}'">
                            <x-button variant="secondary" size="sm" class="whitespace-nowrap w-full">
                                Track Status
                            </x-button>
                        </a>

                        <x-button
                            variant="secondary"
                            size="sm"
                            class="whitespace-nowrap"
                            @click="handleContactSupport(booking.id, booking.service)"
                        >
                            <x-icon name="message-square" class="w-4 h-4 mr-1 inline text-gray-500" />
                            Contact Support
                        </x-button>

                        <template x-if="booking.status === 'approved'">
                            <a :href="'{{ url('/customer/payment') }}/' + booking.id">
                                <x-button variant="accent" size="sm" class="whitespace-nowrap bg-green-600 hover:bg-green-700 text-white w-full border-green-600">
                                    Pay Now
                                </x-button>
                            </a>
                        </template>

                        <template x-if="booking.status === 'pending'">
                            <x-button
                                variant="outline"
                                size="sm"
                                class="whitespace-nowrap bg-red-600 hover:bg-red-700 text-white border-red-600 hover:border-red-700"
                                @click="handleCancelBooking(booking.id)"
                            >
                                Cancel
                            </x-button>
                        </template>
                    </div>
                </div>
            </x-card>
        </template>
    </div>
</div>
@endsection
