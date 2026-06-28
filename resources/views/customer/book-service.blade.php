@extends('layouts.dashboard')

@section('title', 'Book Service | AutoProject+')

@section('content')
@php
    $servicesPayload = $services->map(fn ($service) => [
        'id' => $service->id,
        'name' => $service->name,
        'category' => $service->category->slug,
        'estimatedPrice' => ['min' => (float) $service->min_cost, 'max' => (float) $service->max_cost],
        'description' => $service->description,
        'brands' => $service->brands->pluck('name')->values(),
    ])->values();

    $categoriesPayload = $serviceCategories->map(fn ($category) => [
        'id' => $category->slug,
        'name' => $category->name,
        'icon' => $category->icon,
        'color' => $category->color,
    ])->values();

    $fee = \App\Models\BusinessSetting::getValue('reservation_fee', 200.00);
    $gcashNumber = \App\Models\BusinessSetting::getValue('gcash_account_number', '0912-345-6789');
    $mayaNumber = \App\Models\BusinessSetting::getValue('maya_account_number', '0917-888-9999');
@endphp

<div 
    x-data="{
        currentStep: 1,
        formData: {
            customerName: @js(old('customer_name', $user->name)),
            contactNumber: @js(old('contact_number', $user->phone ?? '')),
            vehicleMake: @js(old('vehicle_make', '')),
            vehicleModel: @js(old('vehicle_model', '')),
            vehicleYear: @js(old('vehicle_year', '')),
            plateNumber: @js(old('plate_number', '')),
            preferredDate: @js(old('preferred_date', '')),
            preferredTime: @js(old('preferred_time', '')),
            notes: @js(old('notes', ''))
        },
        selectedServices: @js(array_map('intval', old('service_ids', []))),
        selectedBrands: @js(old('brands', [])),
        expandedCategories: @js($categoriesPayload->take(2)->pluck('id')),
        paymentMethod: @js(old('payment_method', '')),
        referenceNumber: @js(old('reference_number', '')),
        uploadedFile: null,
        gcashNumber: @js($gcashNumber),
        mayaNumber: @js($mayaNumber),
        reservationFee: @js('₱' . number_format($fee, 2)),
        handleFileUpload(e) {
            if (e.target.files && e.target.files[0]) {
                this.uploadedFile = e.target.files[0].name;
                document.getElementById('hidden-payment-screenshot').files = e.target.files;
            }
        },
        agreedToTerms: false,
        showTerms: false,
        selectedTimeSlot: @js(old('preferred_time', '')),
        slotAvailability: {},

        services: @js($servicesPayload),
        serviceCategories: @js($categoriesPayload),

        timeSlots: [
            '08:00 AM', '09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM',
            '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM'
        ],

        toggleService(id) {
            id = Number(id);
            if (this.selectedServices.includes(id)) {
                this.selectedServices = this.selectedServices.filter(x => x !== id);
                delete this.selectedBrands[id];
            } else {
                this.selectedServices.push(id);
            }
        },

        toggleCategory(id) {
            if (this.expandedCategories.includes(id)) {
                this.expandedCategories = this.expandedCategories.filter(x => x !== id);
            } else {
                this.expandedCategories.push(id);
            }
        },

        handleBrandSelection(serviceId, brand) {
            this.selectedBrands[serviceId] = brand;
        },

        getEstimatedCost() {
            if (this.selectedServices.length === 0) return null;
            let min = 0;
            let max = 0;
            this.selectedServices.forEach(id => {
                let svc = this.services.find(s => s.id === id);
                if (svc) {
                    min += svc.estimatedPrice.min;
                    max += svc.estimatedPrice.max;
                }
            });
            return {
                min: min,
                max: max,
                display: '₱' + min.toLocaleString() + ' - ₱' + max.toLocaleString()
            };
        },

        async fetchAvailability(date) {
            if (!date) return;
            const response = await fetch(`{{ route('customer.schedule.availability') }}?date=${date}`);
            this.slotAvailability[date] = await response.json();
        },

        getDateAvailability(date) {
            const data = this.slotAvailability[date];
            if (!data) {
                return { isFullyBooked: false, availableSlots: this.timeSlots, bookedSlots: [] };
            }
            return {
                isFullyBooked: data.is_fully_booked,
                availableSlots: data.available_slots,
                bookedSlots: data.booked_slots
            };
        },

        isSunday(dateStr) {
            if (!dateStr) return false;
            let date = new Date(dateStr + 'T00:00:00');
            return date.getDay() === 0;
        },

        handleProceedToDetails() {
            if (this.selectedServices.length === 0) {
                showToast.error('Please select at least one service');
                return;
            }
            this.currentStep = 2;
            window.scrollTo(0, 0);
        },

        async handleProceedToPayment() {
            if (this.isSunday(this.formData.preferredDate)) {
                showToast.error('We are closed on Sundays. Please select a weekday (Monday-Saturday).');
                return;
            }
            await this.fetchAvailability(this.formData.preferredDate);
            let avail = this.getDateAvailability(this.formData.preferredDate);
            if (avail.isFullyBooked) {
                showToast.error('Selected date is fully booked. Please choose another date.');
                return;
            }
            if (!this.selectedTimeSlot) {
                showToast.error('Please select a time slot for your appointment');
                return;
            }
            this.currentStep = 3;
            window.scrollTo(0, 0);
        },

        handleSubmit() {
            if (!this.paymentMethod) {
                showToast.error('Please select a payment method');
                return;
            }
            if (!this.referenceNumber.trim()) {
                showToast.error('Please enter payment reference number');
                return;
            }
            if (!this.uploadedFile) {
                showToast.error('Please upload payment screenshot');
                return;
            }
            if (!this.agreedToTerms) {
                showToast.error('Please agree to the terms and conditions');
                return;
            }

            const form = document.getElementById('booking-submit-form');
            const fields = {
                customer_name: this.formData.customerName,
                contact_number: this.formData.contactNumber,
                vehicle_make: this.formData.vehicleMake,
                vehicle_model: this.formData.vehicleModel,
                vehicle_year: this.formData.vehicleYear,
                plate_number: this.formData.plateNumber,
                preferred_date: this.formData.preferredDate,
                preferred_time: this.selectedTimeSlot,
                notes: this.formData.notes,
                payment_method: this.paymentMethod,
                reference_number: this.referenceNumber,
            };

            Object.entries(fields).forEach(([name, value]) => {
                const input = form.querySelector(`[name='${name}']`);
                if (input) input.value = value ?? '';
            });

            form.querySelectorAll('[data-dynamic-input]').forEach(el => el.remove());

            this.selectedServices.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'service_ids[]';
                input.value = id;
                input.setAttribute('data-dynamic-input', 'true');
                form.appendChild(input);
            });

            Object.entries(this.selectedBrands).forEach(([serviceId, brand]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `brands[${serviceId}]`;
                input.value = brand;
                input.setAttribute('data-dynamic-input', 'true');
                form.appendChild(input);
            });

            const terms = form.querySelector('[name=agreed_to_terms]');
            if (terms) terms.checked = this.agreedToTerms;

            form.submit();
        }
    }"
    class="max-w-7xl mx-auto space-y-6 animate-fade-in"
>
    @if ($errors->any())
        <x-card class="border-red-500/30 bg-red-50 dark:bg-red-950/20">
            <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-card>
    @endif

    <form id="booking-submit-form" method="POST" action="{{ route('customer.bookings.store') }}" enctype="multipart/form-data" class="hidden">
        @csrf
        <input type="hidden" name="customer_name" />
        <input type="hidden" name="contact_number" />
        <input type="hidden" name="vehicle_make" />
        <input type="hidden" name="vehicle_model" />
        <input type="hidden" name="vehicle_year" />
        <input type="hidden" name="plate_number" />
        <input type="hidden" name="preferred_date" />
        <input type="hidden" name="preferred_time" />
        <input type="hidden" name="notes" />
        <input type="hidden" name="payment_method" />
        <input type="hidden" name="reference_number" />
        <input type="file" name="payment_screenshot" id="hidden-payment-screenshot" class="hidden" />
        <input type="checkbox" name="agreed_to_terms" value="1" />
    </form>
    {{-- Progress Indicator --}}
    <div class="flex items-center justify-center gap-3 mb-6">
        <div class="flex items-center gap-2">
            <div 
                :class="currentStep === 1 ? 'bg-[#E63946] text-white' : 'bg-green-500 text-white'"
                class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all duration-300"
            >
                <template x-if="currentStep === 1"><span>1</span></template>
                <template x-if="currentStep > 1">
                    <x-icon name="check-square" class="w-5 h-5 text-white" />
                </template>
            </div>
            <span :class="currentStep === 1 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'" class="font-medium text-sm">
                Services
            </span>
        </div>
        <div class="w-12 h-1 bg-gray-300 dark:bg-gray-700"></div>
        <div class="flex items-center gap-2">
            <div 
                :class="currentStep === 2 ? 'bg-[#E63946] text-white' : (currentStep > 2 ? 'bg-green-500 text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-600 dark:text-gray-400')"
                class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all duration-300"
            >
                <template x-if="currentStep <= 2"><span>2</span></template>
                <template x-if="currentStep > 2">
                    <x-icon name="check-square" class="w-5 h-5 text-white" />
                </template>
            </div>
            <span :class="currentStep === 2 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'" class="font-medium text-sm">
                Details
            </span>
        </div>
        <div class="w-12 h-1 bg-gray-300 dark:bg-gray-700"></div>
        <div class="flex items-center gap-2">
            <div 
                :class="currentStep === 3 ? 'bg-[#E63946] text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-600 dark:text-gray-400'"
                class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-all duration-300"
            >
                <span>3</span>
            </div>
            <span :class="currentStep === 3 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'" class="font-medium text-sm">
                Payment
            </span>
        </div>
    </div>

    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white" x-text="currentStep === 1 ? 'Select Your Services' : currentStep === 2 ? 'Booking Information' : 'Payment & Confirmation'"></h1>
        <p class="text-gray-700 dark:text-gray-300" x-text="currentStep === 1 ? 'Choose the services you need for your vehicle.' : currentStep === 2 ? 'Fill in your customer, vehicle, and appointment details.' : 'Review terms and complete the ₱200 reservation fee payment.'"></p>
    </div>

    {{-- STEP 1: Service Selection --}}
    <div x-show="currentStep === 1" class="space-y-6">
        <x-card class="bg-amber-50 dark:bg-amber-950/30 border-l-4 border-amber-500">
            <div class="flex gap-3">
                <x-icon name="info" class="text-amber-600 dark:text-amber-500 flex-shrink-0 w-6 h-6" />
                <div class="text-sm">
                    <p class="font-bold mb-2 text-gray-900 dark:text-white">Pricing Information</p>
                    <ul class="space-y-1 list-disc list-inside text-gray-700 dark:text-gray-300">
                        <li><strong>Estimated prices</strong> shown below are for reference only.</li>
                        <li><strong>Final pricing</strong> will be manually calculated by our staff after vehicle inspection.</li>
                        <li><strong>AutoProject-D Custom Garage</strong> uses branded, quality parts and materials.</li>
                        <li>The <strong>brand and exact price</strong> of parts used will be provided in your detailed quote.</li>
                    </ul>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center gap-2 mb-4">
                <x-icon name="wrench" class="text-[#E63946] w-6 h-6" />
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                    Select Services (<span x-text="selectedServices.length"></span> selected)
                </h2>
            </div>
            <p class="text-sm mb-4 text-gray-700 dark:text-gray-300">
                Choose one or multiple services that you need. You can select services from different categories.
            </p>

            <div class="space-y-4">
                <template x-for="category in serviceCategories" :key="category.id">
                    <div class="border-2 border-gray-200 dark:border-white/10 rounded-xl overflow-hidden">
                        <button
                            type="button"
                            @click="toggleCategory(category.id)"
                            class="w-full flex items-center justify-between p-4 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors cursor-pointer"
                            :style="`border-left: 4px solid ${category.color}`"
                        >
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg text-white" :style="`background-color: ${category.color}`">
                                    <x-icon name="wrench" class="w-5 h-5 text-white" />
                                </div>
                                <div class="text-left">
                                    <h3 class="font-bold text-gray-900 dark:text-white" x-text="category.name"></h3>
                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                        <span x-text="services.filter(s => s.category === category.id).length"></span> services available
                                    </p>
                                </div>
                            </div>
                            <x-icon name="chevron-right" class="text-gray-400 w-5 h-5 transform transition-transform" ::class="expandedCategories.includes(category.id) ? 'rotate-90' : ''" />
                        </button>

                        <div 
                            x-show="expandedCategories.includes(category.id)" 
                            x-collapse
                            class="p-4 bg-white dark:bg-[#0B0B0B] grid grid-cols-1 md:grid-cols-2 gap-3"
                        >
                            <template x-for="service in services.filter(s => s.category === category.id)" :key="service.id">
                                <button
                                    type="button"
                                    @click="toggleService(service.id)"
                                    class="text-left p-4 rounded-xl border-2 transition-all cursor-pointer"
                                    :class="selectedServices.includes(service.id) ? 'border-[#457B9D] bg-blue-50 dark:bg-blue-950/30' : 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20 bg-white dark:bg-[#151515]'"
                                >
                                    <div class="flex items-start gap-3">
                                        <div 
                                            class="mt-1 w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0"
                                            :class="selectedServices.includes(service.id) ? 'border-[#457B9D] bg-[#457B9D]' : 'border-gray-300 dark:border-gray-600'"
                                        >
                                            <template x-if="selectedServices.includes(service.id)">
                                                <x-icon name="check-square" class="w-3.5 h-3.5 text-white" />
                                            </template>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="font-bold mb-1 text-gray-900 dark:text-white text-base" x-text="service.name"></h4>
                                            <p class="text-xs mb-2 text-gray-600 dark:text-gray-400" x-text="service.description"></p>
                                            <p class="text-sm font-bold text-[#E63946]">
                                                ₱<span x-text="service.estimatedPrice.min.toLocaleString()"></span> - ₱<span x-text="service.estimatedPrice.max.toLocaleString()"></span>
                                            </p>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </x-card>

        {{-- Brand Selection --}}
        <template x-if="selectedServices.length > 0 && services.filter(s => selectedServices.includes(s.id) && s.brands && s.brands.length > 0).length > 0">
            <x-card class="border-2 border-[#457B9D]">
                <div class="flex items-center gap-2 mb-4">
                    <x-icon name="shield" class="text-[#457B9D] w-6 h-6" />
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Select Preferred Brands (Optional)</h2>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                    Choose your preferred brands for the selected services. If no brand is selected, our staff will recommend the best option based on your vehicle and budget.
                </p>

                <div class="space-y-6">
                    <template x-for="service in services.filter(s => selectedServices.includes(s.id) && s.brands && s.brands.length > 0)" :key="service.id">
                        <div class="bg-gray-50 dark:bg-[#151515] rounded-xl p-4">
                            <div class="flex items-start gap-3 mb-3">
                                <x-icon name="check-square" class="text-[#457B9D] flex-shrink-0 mt-0.5 w-5 h-5" />
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 dark:text-white mb-1" x-text="service.name"></h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Available brands:</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2 ml-8">
                                <template x-for="brand in service.brands" :key="brand">
                                    <button
                                        type="button"
                                        @click="handleBrandSelection(service.id, brand)"
                                        class="px-3 py-2 rounded-lg text-sm border-2 transition-all cursor-pointer"
                                        :class="selectedBrands[service.id] === brand ? 'bg-[#457B9D] text-white border-[#457B9D]' : 'bg-white dark:bg-[#0B0B0B] text-gray-700 dark:text-gray-300 border-gray-300 dark:border-white/20 hover:border-[#457B9D]'"
                                        x-text="brand"
                                    ></button>
                                </template>
                            </div>
                            <template x-if="selectedBrands[service.id]">
                                <div class="ml-8 mt-3 p-2 bg-blue-50 dark:bg-blue-950/30 border-l-4 border-[#457B9D] rounded">
                                    <p class="text-xs text-gray-700 dark:text-gray-300">
                                        <strong>Selected:</strong> <span x-text="selectedBrands[service.id]"></span>
                                    </p>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </x-card>
        </template>

        {{-- Cost Summary --}}
        <template x-if="getEstimatedCost()">
            <x-card class="bg-gradient-to-r from-[#E63946] to-[#D62839] text-white">
                <div class="flex items-start gap-4">
                    <div class="p-3 bg-white/20 rounded-xl">
                        <x-icon name="dollar-sign" class="w-8 h-8 text-white" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold mb-2">Estimated Total Cost Range</h3>
                        <p class="text-3xl font-extrabold mb-3" x-text="getEstimatedCost().display"></p>
                        <div class="bg-white/20 rounded-xl p-3 text-sm space-y-2">
                            <p class="font-medium mb-1">Selected Services (<span x-text="selectedServices.length"></span>):</p>
                            <ul class="space-y-1 opacity-90">
                                <template x-for="id in selectedServices" :key="id">
                                    <li class="flex items-start gap-2">
                                        <span>•</span>
                                        <div class="flex-1">
                                            <span x-text="services.find(s => s.id === id).name"></span>
                                            <template x-if="selectedBrands[id]">
                                                <span class="ml-2 text-xs bg-white/20 px-2 py-0.5 rounded" x-text="selectedBrands[id]"></span>
                                            </template>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <p class="text-sm mt-3 text-white/90">
                            ⚠️ Final pricing will be determined after vehicle inspection by our qualified staff. Prices include quality branded parts and labor.
                        </p>
                    </div>
                </div>
            </x-card>
        </template>

        {{-- Actions --}}
        <div class="flex gap-4 justify-end pb-8">
            <a href="{{ url('/customer') }}">
                <x-button type="button" variant="outline" class="border-red-500 text-red-500 hover:bg-red-500 hover:text-white">Cancel</x-button>
            </a>
            <x-button type="button" size="lg" variant="accent" @click="handleProceedToDetails()" class="text-white bg-green-600 hover:bg-green-700 border-green-600">
                Proceed to Booking Details
                <x-icon name="arrow-right" class="w-5 h-5 ml-2 inline-block text-white" />
            </x-button>
        </div>
    </div>

    {{-- STEP 2: Booking Details --}}
    <div x-show="currentStep === 2" class="space-y-6">
        <form @submit.prevent="handleProceedToPayment()" class="space-y-6">
            {{-- Customer Information --}}
            <x-card>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Customer Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Full Name"
                        x-model="formData.customerName"
                        required
                    />
                    <x-input
                        label="Contact Number"
                        type="tel"
                        x-model="formData.contactNumber"
                        required
                    />
                </div>
            </x-card>

            {{-- Vehicle Information --}}
            <x-card>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Vehicle Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input
                        label="Vehicle Make"
                        placeholder="e.g., Honda, Toyota"
                        x-model="formData.vehicleMake"
                        required
                    />
                    <x-input
                        label="Vehicle Model"
                        placeholder="e.g., Civic, Supra"
                        x-model="formData.vehicleModel"
                        required
                    />
                    <x-input
                        label="Vehicle Year"
                        type="number"
                        placeholder="e.g., 2020"
                        x-model="formData.vehicleYear"
                        required
                    />
                    <x-input
                        label="Plate Number"
                        placeholder="e.g., ABC 1234"
                        x-model="formData.plateNumber"
                        required
                    />
                </div>
            </x-card>

            {{-- Appointment Details --}}
            <x-card>
                <div class="flex items-center gap-2 mb-4">
                    <x-icon name="calendar" class="text-[#457B9D] w-6 h-6" />
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Appointment Details</h2>
                </div>

                <div class="space-y-6">
                    <div>
                        <x-input
                            label="Select Preferred Date"
                            type="date"
                            x-model="formData.preferredDate"
                            min="{{ date('Y-m-d') }}"
                            @change="selectedTimeSlot = ''; formData.preferredTime = ''; fetchAvailability(formData.preferredDate)"
                            required
                        />

                        <template x-if="formData.preferredDate">
                            <div 
                                class="mt-3 p-3 rounded-xl border"
                                :class="isSunday(formData.preferredDate) || getDateAvailability(formData.preferredDate).isFullyBooked ? 'bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800' : 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800'"
                            >
                                <div class="flex items-start gap-2">
                                    <span class="w-5 h-5 mt-0.5 flex-shrink-0" x-bind:class="isSunday(formData.preferredDate) || getDateAvailability(formData.preferredDate).isFullyBooked ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-400'">
                                        <x-icon name="info" class="w-5 h-5" />
                                    </span>
                                    <div class="text-sm">
                                        <template x-if="isSunday(formData.preferredDate)">
                                            <p class="text-red-600 dark:text-red-400 font-medium">We are closed on Sundays. Please select a weekday (Monday-Saturday).</p>
                                        </template>
                                        <template x-if="!isSunday(formData.preferredDate)">
                                            <div>
                                                <template x-if="getDateAvailability(formData.preferredDate).isFullyBooked">
                                                    <p class="text-red-600 dark:text-red-400 font-medium">This date is fully booked. Please select another date.</p>
                                                </template>
                                                <template x-if="!getDateAvailability(formData.preferredDate).isFullyBooked">
                                                    <div class="text-blue-800 dark:text-blue-200">
                                                        <p class="font-medium mb-1"><span x-text="getDateAvailability(formData.preferredDate).availableSlots.length"></span> time slots available</p>
                                                        <template x-if="getDateAvailability(formData.preferredDate).bookedSlots.length > 0">
                                                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                                                <span x-text="getDateAvailability(formData.preferredDate).bookedSlots.length"></span> slots already booked
                                                            </p>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Time Slots --}}
                    <template x-if="formData.preferredDate && !isSunday(formData.preferredDate) && !getDateAvailability(formData.preferredDate).isFullyBooked">
                        <div>
                            <label class="block text-sm font-medium mb-3 text-gray-900 dark:text-white">Select Preferred Time Slot *</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2">
                                <template x-for="slot in timeSlots" :key="slot">
                                    @php
                                        $bookedCheck = "getDateAvailability(formData.preferredDate).bookedSlots.includes(slot)";
                                    @endphp
                                    <button
                                        type="button"
                                        @click="if (!{{ $bookedCheck }}) { selectedTimeSlot = slot; formData.preferredTime = slot; }"
                                        :disabled="{{ $bookedCheck }}"
                                        class="p-3 rounded-xl border-2 text-sm font-bold transition-all cursor-pointer flex flex-col items-center justify-center gap-1"
                                        :class="{{ $bookedCheck }} ? 'bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-400 dark:text-gray-500 cursor-not-allowed' : (selectedTimeSlot === slot ? 'bg-green-600 border-green-600 text-white' : 'bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white hover:border-[#457B9D] hover:bg-blue-50 dark:hover:bg-blue-950/30')"
                                    >
                                        <div class="flex items-center gap-1">
                                            <x-icon name="calendar" class="w-4 h-4" />
                                            <span x-text="slot"></span>
                                        </div>
                                        <template x-if="{{ $bookedCheck }}">
                                            <span class="text-xs text-red-500 font-bold">Booked</span>
                                        </template>
                                        <template x-if="selectedTimeSlot === slot">
                                            <span class="text-xs text-white">Selected</span>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Legend --}}
                    <template x-if="formData.preferredDate">
                        <div class="flex flex-wrap gap-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-xl">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-white dark:bg-gray-900 border-2 border-gray-300 dark:border-gray-600"></div>
                                <span class="text-xs text-gray-700 dark:text-gray-300">Available</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-green-600"></div>
                                <span class="text-xs text-gray-700 dark:text-gray-300">Selected</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-gray-100 dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600"></div>
                                <span class="text-xs text-gray-700 dark:text-gray-300">Booked</span>
                            </div>
                        </div>
                    </template>

                    <x-textarea
                        label="Additional Notes (Optional)"
                        rows="4"
                        placeholder="Any specific requirements, preferred brands, or special instructions..."
                        x-model="formData.notes"
                    />
                </div>
            </x-card>

            {{-- Actions --}}
            <div class="flex gap-4 justify-between pb-8">
                <x-button type="button" variant="outline" @click="currentStep = 1">
                    <x-icon name="chevron-right" class="w-5 h-5 mr-2 inline-block transform rotate-180" />
                    Back to Services
                </x-button>
                <div class="flex gap-4">
                    <a href="{{ url('/customer') }}">
                        <x-button type="button" variant="outline" class="border-red-500 text-red-500 hover:bg-red-500 hover:text-white">Cancel</x-button>
                    </a>
                    <x-button type="submit" size="lg" variant="accent" class="text-white bg-green-600 hover:bg-green-700 border-green-600">
                        Continue to Payment
                        <x-icon name="arrow-right" class="w-5 h-5 ml-2 inline-block text-white" />
                    </x-button>
                </div>
            </div>
        </form>
    </div>

    {{-- STEP 3: Payment & Terms --}}
    <div x-show="currentStep === 3" class="space-y-6">
        <form @submit.prevent="handleSubmit()" class="space-y-6">
            {{-- Booking Summary --}}
            <x-card>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Booking Summary</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Services</span>
                        <span class="font-bold text-gray-900 dark:text-white text-right" x-text="selectedServices.map(id => services.find(s => s.id === id)?.name).join(', ')"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Vehicle</span>
                        <span class="font-bold text-gray-900 dark:text-white" x-text="`${formData.vehicleMake} ${formData.vehicleModel} ${formData.vehicleYear}`"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Plate Number</span>
                        <span class="font-bold text-gray-900 dark:text-white" x-text="formData.plateNumber"></span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 dark:border-white/10 pt-3">
                        <span class="text-gray-600 dark:text-gray-400">Total Estimate</span>
                        <span class="font-bold text-gray-900 dark:text-white" x-text="getEstimatedCost()?.display"></span>
                    </div>
                    <div class="flex justify-between items-center bg-[#E63946]/10 p-4 rounded-xl">
                        <span class="font-extrabold text-gray-900 dark:text-white">Reservation Fee (Required)</span>
                        <span class="text-2xl font-black text-[#E63946]" x-text="reservationFee"></span>
                    </div>
                </div>
            </x-card>

            <x-card class="bg-blue-50 dark:bg-blue-950/30 border-l-4 border-[#457B9D]">
                <div class="flex gap-3">
                    <x-icon name="info" class="text-[#457B9D] flex-shrink-0 w-6 h-6" />
                    <div class="text-sm">
                        <p class="font-bold mb-1 text-gray-900 dark:text-white">Reservation Fee Required: <span x-text="reservationFee"></span></p>
                        <p class="text-gray-700 dark:text-gray-300">
                            A non-refundable reservation fee of <span x-text="reservationFee"></span> is required to secure your appointment. This fee ensures your commitment to arrive at the scheduled date and time.
                        </p>
                    </div>
                </div>
            </x-card>

            <x-card class="border-2 border-[#457B9D]">
                <div class="flex items-center gap-2 mb-4">
                    <x-icon name="shield" class="text-[#457B9D] w-6 h-6" />
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Reservation Fee Payment - <span x-text="reservationFee"></span></h2>
                </div>

                <div class="space-y-6">
                    <div class="space-y-4">
                        <label class="block">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 block">
                                Select Payment Method <span class="text-red-500">*</span>
                            </span>
                            <div class="grid grid-cols-2 gap-4">
                                <button
                                    type="button"
                                    @click="paymentMethod = 'gcash'"
                                    class="p-4 border-2 rounded-xl transition-all cursor-pointer"
                                    :class="paymentMethod === 'gcash' ? 'border-[#457B9D] bg-blue-50 dark:bg-blue-950/30 text-gray-900 dark:text-white' : 'border-gray-300 dark:border-white/20 hover:border-gray-400 dark:hover:border-white/30 text-gray-900 dark:text-white'"
                                >
                                    <div class="flex items-center justify-center gap-2">
                                        <x-icon name="message-square" class="w-5 h-5" />
                                        <span class="font-bold">GCash</span>
                                    </div>
                                </button>
                                <button
                                    type="button"
                                    @click="paymentMethod = 'maya'"
                                    class="p-4 border-2 rounded-xl transition-all cursor-pointer"
                                    :class="paymentMethod === 'maya' ? 'border-[#457B9D] bg-blue-50 dark:bg-blue-950/30 text-gray-900 dark:text-white' : 'border-gray-300 dark:border-white/20 hover:border-gray-400 dark:hover:border-white/30 text-gray-900 dark:text-white'"
                                >
                                    <div class="flex items-center justify-center gap-2">
                                        <x-icon name="message-square" class="w-5 h-5" />
                                        <span class="font-bold">Maya</span>
                                    </div>
                                </button>
                            </div>
                        </label>

                        {{-- GCash Payment Guide --}}
                        <div x-show="paymentMethod === 'gcash'" class="bg-white dark:bg-[#0B0B0B] rounded-xl p-6 border-2 border-[#457B9D]">
                            <div class="text-center space-y-4">
                                <div class="inline-block bg-blue-50 dark:bg-blue-950/30 px-4 py-2 rounded-lg">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Scan QR Code to Pay <span x-text="reservationFee"></span></p>
                                </div>
                                <div class="flex justify-center">
                                    <div class="bg-white p-4 rounded-2xl shadow-lg">
                                        <img src="{{ asset('gcash-qr.png') }}" alt="GCash QR Code" class="w-64 h-64 object-contain" />
                                    </div>
                                </div>
                                <div class="bg-gray-50 dark:bg-[#151515] rounded-xl p-4">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 font-bold mb-2">Payment Instructions:</p>
                                    <ol class="text-xs text-gray-600 dark:text-gray-400 space-y-1 text-left list-decimal list-inside">
                                        <li>Open your GCash app</li>
                                        <li>Scan the QR code above</li>
                                        <li>Confirm payment of <span x-text="reservationFee"></span></li>
                                        <li>Take a screenshot of your receipt</li>
                                        <li>Enter your reference number below</li>
                                    </ol>
                                </div>
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    <strong>Account Name:</strong> AutoProject-D Custom Garage <br>
                                    <strong>GCash Number:</strong> <span x-text="gcashNumber"></span>
                                </p>
                            </div>
                        </div>

                        {{-- Maya Payment Guide --}}
                        <div x-show="paymentMethod === 'maya'" class="bg-white dark:bg-[#0B0B0B] rounded-xl p-6 border-2 border-[#457B9D]">
                            <div class="text-center space-y-4">
                                <div class="inline-block bg-blue-50 dark:bg-blue-950/30 px-4 py-2 rounded-lg">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white">Send <span x-text="reservationFee"></span> to Maya Account</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-[#151515] rounded-xl p-4">
                                    <p class="text-lg font-extrabold text-gray-900 dark:text-white mb-2" x-text="mayaNumber"></p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Account Name: AutoProject-D Custom Garage</p>
                                </div>
                                <div class="bg-gray-50 dark:bg-[#151515] rounded-xl p-4">
                                    <p class="text-sm text-gray-700 dark:text-gray-300 font-bold mb-2">Payment Instructions:</p>
                                    <ol class="text-xs text-gray-600 dark:text-gray-400 space-y-1 text-left list-decimal list-inside">
                                        <li>Open your Maya app</li>
                                        <li>Select "Send Money"</li>
                                        <li>Enter the mobile number above</li>
                                        <li>Send <span x-text="reservationFee"></span></li>
                                        <li>Take a screenshot of your receipt</li>
                                        <li>Enter your reference number below</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <x-input
                            label="Payment Reference Number"
                            placeholder="e.g., 1234567890123"
                            x-model="referenceNumber"
                            required
                            helperText="Enter the reference number from your payment receipt"
                        />

                        {{-- Upload Payment Screenshot --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Upload Payment Screenshot <span class="text-red-500">*</span>
                            </label>
                            <label class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 dark:border-white/15 rounded-xl cursor-pointer hover:border-[#E63946] transition-colors bg-gray-50 dark:bg-white/5">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center">
                                    <x-icon name="check-square" class="w-12 h-12 text-gray-400 mb-3" />
                                    <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-bold">Click to upload</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500 mb-2">PNG, JPG or JPEG (MAX. 5MB)</p>
                                    <template x-if="uploadedFile">
                                        <div class="mt-2 flex items-center gap-2 text-green-600 justify-center">
                                            <x-icon name="check-square" class="w-5 h-5 text-green-600" />
                                            <span class="font-bold" x-text="uploadedFile"></span>
                                        </div>
                                    </template>
                                </div>
                                <input
                                    type="file"
                                    class="hidden"
                                    accept="image/*"
                                    @change="handleFileUpload"
                                />
                            </label>
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Terms & Conditions --}}
            <x-card class="bg-amber-50 dark:bg-amber-950/30 border-l-4 border-amber-500">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <x-icon name="info" class="text-amber-500 w-6 h-6" />
                    Terms and Conditions
                </h2>

                <div class="space-y-4">
                    <div class="bg-white dark:bg-[#151515] rounded-xl p-4 space-y-3">
                        <p class="font-bold text-gray-900 dark:text-white">Please read and agree to the following:</p>
                        <ul class="space-y-2 text-sm text-gray-700 dark:text-gray-300">
                            <li class="flex gap-2">
                                <x-icon name="check-square" class="text-green-600 flex-shrink-0 mt-0.5 w-4 h-4" />
                                <span>The <strong>₱200 reservation fee</strong> is required to secure your appointment and confirm your commitment.</span>
                            </li>
                            <li class="flex gap-2">
                                <x-icon name="check-square" class="text-green-600 flex-shrink-0 mt-0.5 w-4 h-4" />
                                <span>You must arrive at the <strong>exact scheduled date and time</strong>.</span>
                            </li>
                            <li class="flex gap-2">
                                <x-icon name="check-square" class="text-green-600 flex-shrink-0 mt-0.5 w-4 h-4" />
                                <span>A <strong>30-minute grace period</strong> is allowed if you are delayed.</span>
                            </li>
                            <li class="flex gap-2">
                                <x-icon name="info" class="text-amber-500 flex-shrink-0 mt-0.5 w-4 h-4" />
                                <span>If you <strong>do not arrive within 30 minutes</strong> of your scheduled time, the reservation fee is <strong>non-refundable</strong>.</span>
                            </li>
                            <li class="flex gap-2">
                                <x-icon name="check-square" class="text-green-600 flex-shrink-0 mt-0.5 w-4 h-4" />
                                <span>Final service pricing will be determined after vehicle inspection by our staff.</span>
                            </li>
                        </ul>
                    </div>

                    <div class="flex items-start gap-3 p-4 bg-white dark:bg-[#151515] rounded-xl border-2 border-gray-300 dark:border-white/20">
                        <input
                            type="checkbox"
                            x-model="agreedToTerms"
                            id="terms-checkbox"
                            class="mt-1 w-4 h-4 cursor-pointer"
                            required
                        />
                        <label for="terms-checkbox" class="text-sm cursor-pointer select-none">
                            <span class="font-bold text-gray-900 dark:text-white">I have read and agree to the terms and conditions.</span>
                            <span class="block text-gray-600 dark:text-gray-400 mt-1">
                                I understand that the ₱200 reservation fee is non-refundable if I fail to arrive within 30 minutes of my scheduled appointment.
                            </span>
                        </label>
                    </div>

                    <button
                        type="button"
                        @click="showTerms = !showTerms"
                        class="text-[#457B9D] text-sm font-bold hover:underline cursor-pointer"
                        x-text="showTerms ? 'Hide Full Terms and Conditions' : 'View Full Terms and Conditions'"
                    ></button>

                    <div x-show="showTerms" class="bg-white dark:bg-[#151515] rounded-xl p-4 border border-gray-300 dark:border-white/20 text-sm text-gray-700 dark:text-gray-300 space-y-3">
                        <h3 class="font-bold text-gray-900 dark:text-white">Full Terms and Conditions</h3>
                        <p><strong>1. Reservation Fee Policy</strong>: The reservation fee of ₱200 is mandatory to secure your appointment.</p>
                        <p><strong>2. Arrival Policy</strong>: Customers are expected to arrive at their exact scheduled time. A 30-minute grace period is provided. Failure to arrive results in fee forfeiture.</p>
                        <p><strong>3. Service Pricing</strong>: The estimated cost ranges provided are for reference only. Final pricing will be determined after a thorough inspection of your vehicle by our qualified staff.</p>
                    </div>
                </div>
            </x-card>

            {{-- Actions --}}
            <div class="flex gap-4 justify-between pb-8">
                <x-button type="button" variant="outline" @click="currentStep = 2">
                    <x-icon name="chevron-right" class="w-5 h-5 mr-2 inline-block transform rotate-180" />
                    Back to Details
                </x-button>
                <div class="flex gap-4">
                    <a href="{{ url('/customer') }}">
                        <x-button type="button" variant="outline" class="border-red-500 text-red-500 hover:bg-red-500 hover:text-white">Cancel</x-button>
                    </a>
                    <x-button type="submit" size="lg" variant="accent" class="text-white bg-green-600 hover:bg-green-700 border-green-600">
                        <x-icon name="check-square" class="w-5 h-5 mr-2 inline-block text-white" />
                        Submit Booking & Payment
                    </x-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
