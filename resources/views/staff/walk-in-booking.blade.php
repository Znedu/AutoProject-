@extends('layouts.dashboard')

@section('title', 'Walk-In Booking | Staff | AutoProject+')

@section('content')
<div class="space-y-8 animate-fade-in"
     x-data="{
         bookingType: 'existing',
         selectedCustomer: null,
         customerSearch: '',
         customerResults: [],
         isSearching: false,
         vehicles: [],
         selectedVehicle: null,
         useExistingVehicle: false,
         selectedServices: [],
         async searchCustomers() {
             if (this.customerSearch.length < 2) { this.customerResults = []; return; }
             this.isSearching = true;
             try {
                 const res = await fetch(`{{ route('staff.customers.search') }}?q=${encodeURIComponent(this.customerSearch)}`, {
                     headers: { 'X-Requested-With': 'XMLHttpRequest' }
                 });
                 this.customerResults = await res.json();
             } finally { this.isSearching = false; }
         },
         async selectCustomer(c) {
             this.selectedCustomer = c;
             this.customerSearch = c.name;
             this.customerResults = [];
             this.vehicles = [];
             this.selectedVehicle = null;
             this.useExistingVehicle = false;
             const res = await fetch(`/staff/customers/${c.id}/vehicles`, {
                 headers: { 'X-Requested-With': 'XMLHttpRequest' }
             });
             this.vehicles = await res.json();
         },
         selectVehicle(v) {
             this.selectedVehicle = v;
             this.useExistingVehicle = true;
         },
         toggleService(id) {
             const idx = this.selectedServices.indexOf(id);
             if (idx >= 0) this.selectedServices.splice(idx, 1);
             else this.selectedServices.push(id);
         },
         isServiceSelected(id) { return this.selectedServices.includes(id); }
     }">

    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Walk-In Booking</h1>
        <p class="text-gray-600 dark:text-gray-400">Create a booking for a walk-in customer.</p>
    </div>

    @if (session('success'))
        <div class="rounded-xl bg-green-100 dark:bg-green-900/30 border border-green-300 dark:border-green-700 p-4 text-green-800 dark:text-green-200 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700 p-4 text-red-800 dark:text-red-200 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="{{ route('staff.walk-in-booking.store') }}">
        @csrf

        {{-- Customer Type Toggle --}}
        <x-card class="mb-6">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Customer</h2>

            <div class="flex gap-4 mb-6">
                <button type="button"
                    :class="bookingType === 'existing' ? 'bg-[#D2781A] text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300'"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition"
                    @click="bookingType = 'existing'">
                    Existing Customer
                </button>
                <button type="button"
                    :class="bookingType === 'new' ? 'bg-[#D2781A] text-white' : 'bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-gray-300'"
                    class="px-4 py-2 rounded-xl text-sm font-medium transition"
                    @click="bookingType = 'new'">
                    New Customer
                </button>
            </div>

            <input type="hidden" name="booking_type" :value="bookingType">

            {{-- Existing Customer Search --}}
            <div x-show="bookingType === 'existing'" x-cloak class="space-y-4">
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Customer</label>
                    <input type="text"
                        x-model="customerSearch"
                        @input.debounce.400ms="searchCustomers()"
                        placeholder="Search by name, email or phone..."
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#D2781A]/50">
                    <input type="hidden" name="customer_id" :value="selectedCustomer?.id">

                    <ul x-show="customerResults.length > 0" x-cloak
                        class="absolute z-20 mt-1 w-full bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-xl shadow-xl overflow-hidden">
                        <template x-for="c in customerResults" :key="c.id">
                            <li @click="selectCustomer(c)"
                                class="px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/10 text-sm">
                                <p class="font-medium text-gray-900 dark:text-white" x-text="c.name"></p>
                                <p class="text-gray-500 dark:text-gray-400" x-text="c.email"></p>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- Existing vehicles for selected customer --}}
                <div x-show="vehicles.length > 0" x-cloak>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Customer's Vehicles</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="v in vehicles" :key="v.id">
                            <div @click="selectVehicle(v)"
                                :class="selectedVehicle?.id === v.id ? 'border-[#D2781A] bg-[#D2781A]/10' : 'border-gray-200 dark:border-white/10'"
                                class="p-3 border-2 rounded-xl cursor-pointer transition">
                                <p class="font-medium text-gray-900 dark:text-white"
                                   x-text="`${v.year} ${v.make} ${v.model}`"></p>
                                <p class="text-sm text-gray-500 dark:text-gray-400"
                                   x-text="v.plate_number"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- New Customer Fields --}}
            <div x-show="bookingType === 'new'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" name="new_email" placeholder="customer@email.com"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#D2781A]/50">
                    @error('new_email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Temporary Password</label>
                    <input type="password" name="new_password"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#D2781A]/50">
                    @error('new_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Common customer name & contact --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Customer Name</label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#D2781A]/50">
                    @error('customer_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number') }}" required
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#D2781A]/50">
                    @error('contact_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-card>

        {{-- Vehicle Details --}}
        <x-card class="mb-6">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Vehicle</h2>

            <div x-show="!useExistingVehicle || bookingType === 'new'">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4"
                   x-show="bookingType === 'existing'" x-cloak>
                    Or enter vehicle details manually:
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Make</label>
                        <input type="text" name="vehicle_make" value="{{ old('vehicle_make') }}" required
                            class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none">
                        @error('vehicle_make') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Model</label>
                        <input type="text" name="vehicle_model" value="{{ old('vehicle_model') }}" required
                            class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none">
                        @error('vehicle_model') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Year</label>
                        <input type="number" name="vehicle_year" value="{{ old('vehicle_year', date('Y')) }}" required
                            class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none">
                        @error('vehicle_year') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plate Number</label>
                        <input type="text" name="plate_number" value="{{ old('plate_number') }}" required
                            class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none">
                        @error('plate_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Pre-fill from selected vehicle --}}
            <div x-show="useExistingVehicle && bookingType === 'existing'" x-cloak>
                <input type="hidden" name="vehicle_make"  :value="selectedVehicle?.make">
                <input type="hidden" name="vehicle_model" :value="selectedVehicle?.model">
                <input type="hidden" name="vehicle_year"  :value="selectedVehicle?.year">
                <input type="hidden" name="plate_number"  :value="selectedVehicle?.plate_number">
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white"
                           x-text="`${selectedVehicle?.year} ${selectedVehicle?.make} ${selectedVehicle?.model}`"></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedVehicle?.plate_number"></p>
                    </div>
                    <button type="button" @click="useExistingVehicle = false; selectedVehicle = null"
                        class="text-sm text-[#D2781A] hover:underline">
                        Use different vehicle
                    </button>
                </div>
            </div>
        </x-card>

        {{-- Services --}}
        <x-card class="mb-6">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Services</h2>

            @foreach ($serviceCategories as $category)
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">
                        {{ $category->name }}
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach ($category->services as $service)
                            <div @click="toggleService({{ $service->id }})"
                                :class="isServiceSelected({{ $service->id }}) ? 'border-[#D2781A] bg-[#D2781A]/10' : 'border-gray-200 dark:border-white/10'"
                                class="p-3 border-2 rounded-xl cursor-pointer transition select-none">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="font-medium text-gray-900 dark:text-white text-sm">{{ $service->name }}</p>
                                    <div :class="isServiceSelected({{ $service->id }}) ? 'bg-[#D2781A]' : 'bg-gray-200 dark:bg-white/20'"
                                        class="w-4 h-4 rounded-full flex-shrink-0 mt-0.5 transition"></div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    ₱{{ number_format($service->min_cost) }} – ₱{{ number_format($service->max_cost) }}
                                </p>
                            </div>
                            {{-- Hidden inputs for selected services --}}
                            <template x-if="isServiceSelected({{ $service->id }})">
                                <input type="hidden" name="service_ids[]" value="{{ $service->id }}">
                            </template>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @error('service_ids') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </x-card>

        {{-- Schedule --}}
        <x-card class="mb-6">
            <h2 class="text-xl font-bold mb-4 text-gray-900 dark:text-white">Preferred Schedule</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date</label>
                    <input type="date" name="preferred_date" value="{{ old('preferred_date', date('Y-m-d')) }}" required
                        min="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#D2781A]/50">
                    @error('preferred_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Time</label>
                    <select name="preferred_time" required
                        class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#D2781A]/50">
                        <option value="">Select time...</option>
                        @foreach (['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00'] as $timeOption)
                            <option value="{{ $timeOption }}" {{ old('preferred_time') === $timeOption ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::createFromFormat('H:i', $timeOption)->format('g:i A') }}
                            </option>
                        @endforeach
                    </select>
                    @error('preferred_time') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes (optional)</label>
                <textarea name="notes" rows="3"
                    class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#D2781A]/50"
                    placeholder="Any special instructions...">{{ old('notes') }}</textarea>
            </div>
        </x-card>

        {{-- Submit --}}
        <div class="flex gap-4 justify-end">
            <a href="{{ route('staff.booking-queue') }}">
                <x-button type="button" variant="ghost">Cancel</x-button>
            </a>
            <x-button type="submit" variant="accent">Create Walk-In Booking</x-button>
        </div>

    </form>
</div>
@endsection
