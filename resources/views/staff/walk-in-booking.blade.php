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

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Walk-In Booking</h1>
            <p class="text-gray-600 dark:text-gray-400">Create a booking for a walk-in customer at the counter.</p>
        </div>
        <a href="{{ route('staff.booking-queue') }}">
            <x-button variant="ghost" size="sm">← Back to Queue</x-button>
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-700/50 p-4 flex items-start gap-3">
            <x-icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0 mt-0.5" />
            <p class="text-green-800 dark:text-green-300 text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700/50 p-4 flex items-start gap-3">
            <x-icon name="info" class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0 mt-0.5" />
            <p class="text-red-800 dark:text-red-300 text-sm font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('staff.walk-in-booking.store') }}">
        @csrf

        {{-- ── Customer ── --}}
        <x-card class="mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-[#D2781A]/10">
                    <x-icon name="user-plus" class="w-5 h-5 text-[#D2781A]" />
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Customer</h2>
            </div>

            {{-- Type Toggle --}}
            <div class="flex gap-2 mb-6 p-1 bg-gray-100 dark:bg-white/5 rounded-xl w-fit">
                <button type="button"
                    @click="bookingType = 'existing'"
                    :class="bookingType === 'existing'
                        ? 'bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white shadow-sm border border-gray-200 dark:border-white/10'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                    class="px-5 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                    Existing Customer
                </button>
                <button type="button"
                    @click="bookingType = 'new'"
                    :class="bookingType === 'new'
                        ? 'bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white shadow-sm border border-gray-200 dark:border-white/10'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                    class="px-5 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                    New Customer
                </button>
            </div>

            <input type="hidden" name="booking_type" :value="bookingType">

            {{-- Existing Customer Search --}}
            <div x-show="bookingType === 'existing'" x-cloak class="space-y-5">
                <div class="relative">
                    <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
                        Search Customer <span class="text-[#E63946] ml-1">*</span>
                    </label>
                    <div class="relative">
                        <x-icon name="user" class="w-4 h-4 text-gray-400 dark:text-gray-500 absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" />
                        <input type="text"
                            x-model="customerSearch"
                            @input.debounce.400ms="searchCustomers()"
                            placeholder="Search by name, email or phone..."
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all">
                    </div>
                    <input type="hidden" name="customer_id" :value="selectedCustomer?.id">

                    {{-- Loading spinner --}}
                    <div x-show="isSearching" x-cloak class="absolute right-4 top-1/2 -translate-y-1/2 mt-4">
                        <div class="w-4 h-4 border-2 border-[#E63946] border-t-transparent rounded-full animate-spin"></div>
                    </div>

                    {{-- Dropdown Results --}}
                    <ul x-show="customerResults.length > 0" x-cloak
                        class="absolute z-20 mt-1 w-full bg-white dark:bg-[#1A1A1A] border border-gray-200 dark:border-white/10 rounded-xl shadow-2xl overflow-hidden">
                        <template x-for="c in customerResults" :key="c.id">
                            <li @click="selectCustomer(c)"
                                class="px-4 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 transition-colors flex items-center gap-3 text-sm border-b border-gray-100 dark:border-white/5 last:border-0">
                                <div class="w-8 h-8 rounded-full bg-[#E63946]/10 dark:bg-[#E63946]/20 flex items-center justify-center flex-shrink-0">
                                    <x-icon name="user" class="w-4 h-4 text-[#E63946]" />
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white" x-text="c.name"></p>
                                    <p class="text-gray-500 dark:text-gray-400 text-xs" x-text="c.email"></p>
                                </div>
                            </li>
                        </template>
                    </ul>
                </div>

                {{-- Selected Customer Badge --}}
                <div x-show="selectedCustomer" x-cloak
                    class="flex items-center gap-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700/50 rounded-xl">
                    <x-icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" />
                    <p class="text-sm text-green-800 dark:text-green-300 font-medium">Customer selected: <span class="font-bold" x-text="selectedCustomer?.name"></span></p>
                    <button type="button" @click="selectedCustomer = null; customerSearch = ''; vehicles = []; selectedVehicle = null; useExistingVehicle = false;"
                        class="ml-auto text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 transition-colors">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>

                {{-- Customer's Existing Vehicles --}}
                <div x-show="vehicles.length > 0" x-cloak>
                    <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">Customer's Vehicles</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="v in vehicles" :key="v.id">
                            <div @click="selectVehicle(v)"
                                :class="selectedVehicle?.id === v.id
                                    ? 'border-[#E63946] bg-[#E63946]/5 dark:bg-[#E63946]/10'
                                    : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20'"
                                class="p-4 border-2 rounded-xl cursor-pointer transition-all duration-200 group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white group-hover:text-[#E63946] transition-colors"
                                           x-text="`${v.year} ${v.make} ${v.model}`"></p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5" x-text="v.plate_number"></p>
                                    </div>
                                    <div :class="selectedVehicle?.id === v.id ? 'bg-[#E63946]' : 'bg-gray-200 dark:bg-white/10'"
                                        class="w-5 h-5 rounded-full flex-shrink-0 transition-all duration-200 flex items-center justify-center">
                                        <template x-if="selectedVehicle?.id === v.id">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Select a vehicle above, or fill in vehicle details manually below.</p>
                </div>
            </div>

            {{-- New Customer Fields --}}
            <div x-show="bookingType === 'new'" x-cloak class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
                            Email <span class="text-[#E63946] ml-1">*</span>
                        </label>
                        <input type="email" name="new_email" placeholder="customer@email.com"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all">
                        @error('new_email') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
                            Temporary Password <span class="text-[#E63946] ml-1">*</span>
                        </label>
                        <input type="password" name="new_password"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all">
                        @error('new_password') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Common: Customer Name & Contact --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
                <div>
                    <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
                        Customer Name <span class="text-[#E63946] ml-1">*</span>
                    </label>
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                        placeholder="Full name"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all">
                    @error('customer_name') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
                        Contact Number <span class="text-[#E63946] ml-1">*</span>
                    </label>
                    <input type="text" name="contact_number" value="{{ old('contact_number') }}" required
                        placeholder="09XX XXX XXXX"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all">
                    @error('contact_number') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
        </x-card>

        {{-- ── Vehicle ── --}}
        <x-card class="mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-[#D2781A]/10">
                    <x-icon name="wrench" class="w-5 h-5 text-[#D2781A]" />
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Vehicle Details</h2>
            </div>

            <div x-show="!useExistingVehicle || bookingType === 'new'">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4"
                   x-show="bookingType === 'existing' && vehicles.length > 0" x-cloak>
                    Or enter details for a different vehicle:
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
                            Make <span class="text-[#E63946] ml-1">*</span>
                        </label>
                        <input type="text" name="vehicle_make" value="{{ old('vehicle_make') }}" required
                            :disabled="useExistingVehicle && bookingType === 'existing'"
                            placeholder="e.g. Toyota"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        @error('vehicle_make') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
                            Model <span class="text-[#E63946] ml-1">*</span>
                        </label>
                        <input type="text" name="vehicle_model" value="{{ old('vehicle_model') }}" required
                            :disabled="useExistingVehicle && bookingType === 'existing'"
                            placeholder="e.g. Vios"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        @error('vehicle_model') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
                            Year <span class="text-[#E63946] ml-1">*</span>
                        </label>
                        <input type="number" name="vehicle_year" value="{{ old('vehicle_year', date('Y')) }}" required
                            :disabled="useExistingVehicle && bookingType === 'existing'"
                            min="1990" max="{{ date('Y') + 1 }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        @error('vehicle_year') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
                            Plate Number <span class="text-[#E63946] ml-1">*</span>
                        </label>
                        <input type="text" name="plate_number" value="{{ old('plate_number') }}" required
                            :disabled="useExistingVehicle && bookingType === 'existing'"
                            placeholder="ABC 123"
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        @error('plate_number') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Pre-fill from selected vehicle --}}
            <div x-show="useExistingVehicle && bookingType === 'existing'" x-cloak>
                <input type="hidden" name="vehicle_make"  :value="selectedVehicle?.make"  :disabled="!useExistingVehicle || bookingType !== 'existing'">
                <input type="hidden" name="vehicle_model" :value="selectedVehicle?.model" :disabled="!useExistingVehicle || bookingType !== 'existing'">
                <input type="hidden" name="vehicle_year"  :value="selectedVehicle?.year"  :disabled="!useExistingVehicle || bookingType !== 'existing'">
                <input type="hidden" name="plate_number"  :value="selectedVehicle?.plate_number" :disabled="!useExistingVehicle || bookingType !== 'existing'">
                <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700/50 rounded-xl">
                    <div class="flex items-center gap-3">
                        <x-icon name="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" />
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white"
                               x-text="`${selectedVehicle?.year} ${selectedVehicle?.make} ${selectedVehicle?.model}`"></p>
                            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedVehicle?.plate_number"></p>
                        </div>
                    </div>
                    <button type="button" @click="useExistingVehicle = false; selectedVehicle = null"
                        class="text-sm font-medium text-[#E63946] hover:text-[#c1323e] dark:text-[#E63946] dark:hover:text-red-400 transition-colors">
                        Use different vehicle
                    </button>
                </div>
            </div>
        </x-card>

        {{-- ── Services ── --}}
        <x-card class="mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-[#D2781A]/10">
                    <x-icon name="clipboard-list" class="w-5 h-5 text-[#D2781A]" />
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Services</h2>
            </div>

            @foreach ($serviceCategories as $category)
                <div class="mb-6 last:mb-0">
                    <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-3">
                        {{ $category->name }}
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach ($category->services as $service)
                            <div @click="toggleService({{ $service->id }})"
                                :class="isServiceSelected({{ $service->id }})
                                    ? 'border-[#E63946] bg-[#E63946]/5 dark:bg-[#E63946]/10'
                                    : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20'"
                                class="p-4 border-2 rounded-xl cursor-pointer transition-all duration-200 select-none group">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="font-semibold text-gray-900 dark:text-white text-sm">{{ $service->name }}</p>
                                    <div :class="isServiceSelected({{ $service->id }}) ? 'bg-[#E63946] border-[#E63946]' : 'bg-transparent border-gray-300 dark:border-white/20'"
                                        class="w-5 h-5 rounded-full flex-shrink-0 border-2 mt-0.5 transition-all duration-200 flex items-center justify-center">
                                        <template x-if="isServiceSelected({{ $service->id }})">
                                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                        </template>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 font-medium">
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

            @error('service_ids')
                <p class="mt-3 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </x-card>

        {{-- ── Preferred Schedule ── --}}
        <x-card class="mb-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="p-2 rounded-lg bg-[#D2781A]/10">
                    <x-icon name="calendar" class="w-5 h-5 text-[#D2781A]" />
                </div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Preferred Schedule</h2>
            </div>

            <div class="space-y-5">
                {{-- Date --}}
                <div>
                    <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
                        Date <span class="text-[#E63946] ml-1">*</span>
                    </label>
                    <input type="date" name="preferred_date" value="{{ old('preferred_date', date('Y-m-d')) }}" required
                        min="{{ date('Y-m-d') }}"
                        class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all">
                    @error('preferred_date') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Time Slot Grid --}}
                <div x-data="{ selectedTimeSlot: '{{ old('preferred_time', '') }}' }">
                    <label class="block mb-3 text-gray-700 dark:text-[#B8B8B8] font-medium">
                        Time Slot <span class="text-[#E63946] ml-1">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                        @foreach (['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00', '17:00'] as $timeOption)
                            @php $label = \Carbon\Carbon::createFromFormat('H:i', $timeOption)->format('g:i A'); @endphp
                            <button
                                type="button"
                                @click="selectedTimeSlot = '{{ $timeOption }}'"
                                :class="selectedTimeSlot === '{{ $timeOption }}'
                                    ? 'bg-[#E63946] border-[#E63946] text-white shadow-lg shadow-[#E63946]/30'
                                    : 'bg-white dark:bg-[#1F1F1F] border-gray-200 dark:border-white/10 text-gray-900 dark:text-white hover:border-[#E63946]/60 hover:bg-[#E63946]/5 dark:hover:bg-[#E63946]/10'"
                                class="p-3 rounded-xl border-2 text-sm font-semibold transition-all duration-200 cursor-pointer flex flex-col items-center justify-center gap-1"
                            >
                                <x-icon name="clock" class="w-4 h-4" />
                                <span>{{ $label }}</span>
                                <template x-if="selectedTimeSlot === '{{ $timeOption }}'">
                                    <span class="text-xs text-white/80 font-normal">Selected</span>
                                </template>
                            </button>
                        @endforeach
                    </div>

                    {{-- Legend --}}
                    <div class="flex flex-wrap gap-4 mt-3 p-3 bg-gray-50 dark:bg-white/5 rounded-xl">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded bg-white dark:bg-[#1F1F1F] border-2 border-gray-200 dark:border-white/10"></div>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Available</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded bg-[#E63946]"></div>
                            <span class="text-xs text-gray-600 dark:text-gray-400">Selected</span>
                        </div>
                    </div>

                    {{-- Hidden input that submits the value --}}
                    <input type="hidden" name="preferred_time" :value="selectedTimeSlot" required>
                    @error('preferred_time') <p class="mt-2 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-5">
                <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">Notes <span class="text-gray-400 dark:text-gray-500 font-normal text-sm">(optional)</span></label>
                <textarea name="notes" rows="3"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all resize-none"
                    placeholder="Any special instructions or notes for the mechanic...">{{ old('notes') }}</textarea>
            </div>
        </x-card>

        {{-- ── Submit Actions ── --}}
        <div class="flex gap-4 justify-end">
            <a href="{{ route('staff.booking-queue') }}">
                <x-button type="button" variant="ghost">Cancel</x-button>
            </a>
            <x-button type="submit" variant="accent">Create Walk-In Booking</x-button>
        </div>

    </form>
</div>
@endsection
