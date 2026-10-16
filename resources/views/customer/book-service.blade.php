@extends('layouts.dashboard')

@section('title', 'Book Service | AutoProject+')

@section('content')
@php
    $paintPanelsPayload = ($paintPanels ?? \App\Models\PaintPanel::active()->ordered()->get())->map(fn ($p) => [
        'id' => $p->id,
        'code' => $p->code,
        'name' => $p->name,
        'group' => $p->group,
    ])->values();

    $servicesPayload = $services->map(fn ($service) => [
        'id' => $service->id,
        'code' => $service->code,
        'name' => $service->name,
        'category' => $service->category->slug,
        'isPaint' => $service->isPaintRelated(),
        'isPrimaryPaint' => $service->isPrimaryPaint(),
        'isTouchUp' => $service->isPaintTouchUp(),
        'isCustomPaint' => $service->isCustomPaint(),
        'isBodyRepair' => $service->isBodyRepair(),
        'isFullBuild' => $service->isFullCustomBuild(),
        'requiresDamagePhotos' => $service->requiresDamagePhotos(),
        'estimatedPrice' => ['min' => (float) $service->min_cost, 'max' => (float) $service->max_cost],
        'description' => $service->description,
        'brands' => $service->isPaintRelated() ? [] : $service->brands->map(fn ($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'price' => (float) $b->price,
            'short_description' => $b->short_description,
        ])->values(),
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

    $savedVehiclesJson = $vehicles->map(fn ($v) => [
        'id'           => $v->id,
        'make'         => $v->make,
        'model'        => $v->model,
        'year'         => $v->year,
        'plate_number' => $v->plate_number,
        'display_name' => $v->display_name,
    ]);

    $preselectedServicesArray = old('service_ids', ($preselectedServiceId ?? null) ? [$preselectedServiceId] : []);
    $preselectedBrandsArray = old('brands', (($preselectedServiceId ?? null) && ($preselectedBrandName ?? null)) ? [(int) $preselectedServiceId => $preselectedBrandName] : []);
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
        selectedServices: @js(array_map('intval', $preselectedServicesArray)),
        selectedBrands: @js($preselectedBrandsArray),
        expandedCategories: @js($categoriesPayload->take(2)->pluck('id')),
        paymentMethod: @js(old('payment_method', '')),
        referenceNumber: @js(old('reference_number', '')),
        uploadedFile: null,
        uploadedFilePreview: null,
        gcashNumber: @js($gcashNumber),
        mayaNumber: @js($mayaNumber),
        reservationFee: @js('₱' . number_format($fee, 2)),
        handleFileUpload(e) {
            if (e.target.files && e.target.files[0]) {
                const file = e.target.files[0];
                this.uploadedFile = file.name;
                if (file.type.startsWith('image/')) {
                    this.uploadedFilePreview = URL.createObjectURL(file);
                } else {
                    this.uploadedFilePreview = null;
                }
                document.getElementById('hidden-payment-screenshot').files = e.target.files;
            }
        },
        agreedToTerms: false,
        isSubmitting: false,
        showTerms: false,
        selectedTimeSlot: @js(old('preferred_time', '')),
        slotAvailability: {},

        services: @js($servicesPayload),
        serviceCategories: @js($categoriesPayload),
        savedVehicles: @js($savedVehiclesJson),
        selectedSavedVehicleId: '',
        handleSelectSavedVehicle() {
            if (!this.selectedSavedVehicleId) return;
            const v = this.savedVehicles.find(v => v.id == this.selectedSavedVehicleId);
            if (v) {
                this.formData.vehicleMake  = v.make;
                this.formData.vehicleModel = v.model;
                this.formData.vehicleYear  = String(v.year);
                this.formData.plateNumber  = v.plate_number;
            }
        },

        timeSlots: [
            '08:00 AM', '09:00 AM', '10:00 AM', '11:00 AM', '12:00 PM',
            '01:00 PM', '02:00 PM', '03:00 PM', '04:00 PM', '05:00 PM'
        ],

        paintPanels: @js($paintPanelsPayload),
        selectedPanels: @js(old('panels', [])),
        panelSeverity: @js(old('panel_severity', (object)[])),
        damagePhotoFiles: [],
        damagePhotoPreviews: [],
        serverEstimate: null,
        paintScope: 'custom_paint',

        hasPaintRelated() {
            return this.isPaintRelated();
        },

        isPaintRelated() {
            return this.selectedServices.some(id => {
                const s = this.services.find(svc => svc.id === id);
                return s && (s.isPaint || s.requiresDamagePhotos);
            });
        },

        requiresDamagePhotos() {
            return this.selectedServices.some(id => {
                const s = this.services.find(svc => svc.id === id);
                return s && (s.requiresDamagePhotos || s.isPaint);
            });
        },

        hasPaintTouchUp() {
            return this.selectedServices.some(id => {
                const s = this.services.find(svc => svc.id === id);
                return s && s.code === 'paint-001';
            });
        },

        hasCustomPaint() {
            return this.selectedServices.some(id => {
                const s = this.services.find(svc => svc.id === id);
                return s && s.code === 'paint-002';
            });
        },

        hasBodyRepair() {
            return this.selectedServices.some(id => {
                const s = this.services.find(svc => svc.id === id);
                return s && s.code === 'body-001';
            });
        },

        hasFullBuild() {
            return this.selectedServices.some(id => {
                const s = this.services.find(svc => svc.id === id);
                return s && s.code === 'pkg-001';
            });
        },

        currentScopeMode() {
            if (this.hasPaintTouchUp()) return 'touch_up';
            if (this.hasCustomPaint()) return 'custom_paint';
            if (this.hasFullBuild()) return 'full_build';
            if (this.hasBodyRepair()) return 'body_repair';
            return this.paintScope || 'custom_paint';
        },

        togglePanel(code) {
            if (this.selectedPanels.includes(code)) {
                this.selectedPanels = this.selectedPanels.filter(c => c !== code);
            } else {
                if (this.hasPaintTouchUp() && this.selectedPanels.length >= 2) {
                    showToast.error('Paint touch up is limited to maximum 2 panels.');
                    return;
                }
                this.selectedPanels.push(code);
                if (!this.panelSeverity[code]) {
                    this.panelSeverity[code] = 'paint_only';
                }
            }
            this.fetchPaintEstimate();
        },

        applyPanelPreset(preset) {
            if (this.hasPaintTouchUp()) {
                showToast.info('Touch up is limited to 1-2 panels. Please select individual panels.');
                return;
            }
            if (preset === 'front_bumper') {
                this.selectedPanels = ['front_bumper'];
            } else if (preset === 'rear_bumper') {
                this.selectedPanels = ['rear_bumper'];
            } else if (preset === 'both_bumpers') {
                this.selectedPanels = ['front_bumper', 'rear_bumper'];
            } else if (preset === 'hood') {
                this.selectedPanels = ['hood'];
            } else if (preset === 'all_doors') {
                this.selectedPanels = ['lf_door', 'rf_door', 'lr_door', 'rr_door'];
            } else if (preset === 'all_fenders') {
                this.selectedPanels = ['lf_fender', 'rf_fender', 'l_quarter', 'r_quarter'];
            } else if (preset === 'select_all') {
                this.selectedPanels = this.paintPanels.map(p => p.code);
            } else if (preset === 'clear_all') {
                this.selectedPanels = [];
            }
            this.selectedPanels.forEach(code => {
                if (!this.panelSeverity[code]) {
                    this.panelSeverity[code] = 'paint_only';
                }
            });
            this.fetchPaintEstimate();
        },

        setPanelSeverity(code, severity) {
            this.panelSeverity = { ...this.panelSeverity, [code]: severity };
            this.fetchPaintEstimate();
        },

        handleDamagePhotosUpload(e) {
            const files = Array.from(e.target.files || []);
            if (!files.length) return;

            for (let file of files) {
                if (!file.type.startsWith('image/')) {
                    showToast.error(`${file.name} is not an image.`);
                    continue;
                }
                if (file.size > 5 * 1024 * 1024) {
                    showToast.error(`${file.name} exceeds the 5MB file limit.`);
                    continue;
                }
                if (this.damagePhotoFiles.length >= 6) {
                    showToast.error('Maximum 6 photos allowed.');
                    break;
                }
                this.damagePhotoFiles.push(file);
                this.damagePhotoPreviews.push({
                    name: file.name,
                    size: (file.size / (1024 * 1024)).toFixed(2) + ' MB',
                    url: URL.createObjectURL(file)
                });
            }
            e.target.value = '';
            this.syncDamagePhotosInput();
        },

        removeDamagePhoto(index) {
            this.damagePhotoFiles.splice(index, 1);
            this.damagePhotoPreviews.splice(index, 1);
            this.syncDamagePhotosInput();
        },

        syncDamagePhotosInput() {
            const input = document.getElementById('hidden-damage-photos');
            if (!input) return;
            const dt = new DataTransfer();
            this.damagePhotoFiles.forEach(f => dt.items.add(f));
            input.files = dt.files;
        },

        async fetchPaintEstimate() {
            if (this.selectedServices.length === 0) {
                this.serverEstimate = null;
                return;
            }
            try {
                const res = await fetch('{{ route('customer.booking.paint-estimate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        service_ids: this.selectedServices,
                        brands: this.selectedBrands,
                        panels: this.selectedPanels,
                        panel_severity: this.panelSeverity,
                    })
                });
                if (res.ok) {
                    this.serverEstimate = await res.json();
                }
            } catch (e) {
                console.error('Failed to fetch estimate', e);
            }
        },

        toggleService(id) {
            id = Number(id);
            const targetService = this.services.find(s => s.id === id);
            if (this.selectedServices.includes(id)) {
                this.selectedServices = this.selectedServices.filter(x => x !== id);
                delete this.selectedBrands[id];
            } else {
                if (targetService && targetService.isPrimaryPaint) {
                    const existingPrimary = this.services.find(s => this.selectedServices.includes(s.id) && s.isPrimaryPaint && s.id !== id);
                    if (existingPrimary) {
                        this.selectedServices = this.selectedServices.filter(x => x !== existingPrimary.id);
                        delete this.selectedBrands[existingPrimary.id];
                        showToast.info(`Switched primary paint service to ${targetService.name}.`);
                    }
                }
                this.selectedServices.push(id);
            }
            this.fetchPaintEstimate();
        },

        toggleCategory(id) {
            if (this.expandedCategories.includes(id)) {
                this.expandedCategories = this.expandedCategories.filter(x => x !== id);
            } else {
                this.expandedCategories.push(id);
            }
        },

        handleBrandSelection(serviceId, brandName) {
            if (this.selectedBrands[serviceId] === brandName) {
                delete this.selectedBrands[serviceId];
            } else {
                this.selectedBrands[serviceId] = brandName;
            }
            this.fetchPaintEstimate();
        },

        getSelectedBrandObj(serviceId) {
            const brandName = this.selectedBrands[serviceId];
            if (!brandName) return null;
            const svc = this.services.find(s => s.id === serviceId);
            return svc && svc.brands ? svc.brands.find(b => b.name === brandName) : null;
        },

        getSelectedBrandPrice(serviceId) {
            const b = this.getSelectedBrandObj(serviceId);
            return b ? Number(b.price).toLocaleString('en-US', { minimumFractionDigits: 2 }) : '0.00';
        },

        getSelectedBrandDesc(serviceId) {
            const b = this.getSelectedBrandObj(serviceId);
            return b ? (b.short_description || '') : '';
        },

        getEstimatedCost() {
            if (this.serverEstimate) {
                return this.serverEstimate;
            }
            if (this.selectedServices.length === 0) return null;
            let min = 0;
            let max = 0;

            this.selectedServices.forEach(id => {
                let svc = this.services.find(s => s.id === id);
                if (svc) {
                    let brandObj = this.getSelectedBrandObj(id);
                    if (brandObj && Number(brandObj.price) > 0) {
                        min += Number(brandObj.price);
                        max += Number(brandObj.price);
                    } else {
                        min += svc.estimatedPrice.min;
                        max += svc.estimatedPrice.max;
                    }
                }
            });

            if (min === max) {
                return {
                    min: min,
                    max: max,
                    display: '₱' + min.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
                    isExact: true
                };
            }

            return {
                min: min,
                max: max,
                display: '₱' + min.toLocaleString('en-US') + ' - ₱' + max.toLocaleString('en-US'),
                isExact: false
            };
        },

        init() {
            this.$watch('formData.preferredDate', (newDate) => {
                this.selectedTimeSlot = '';
                this.formData.preferredTime = '';
                if (newDate) this.fetchAvailability(newDate);
            });
            if (this.formData.preferredDate) {
                this.fetchAvailability(this.formData.preferredDate);
            }

            const preselectedId = @js($preselectedServiceId);
            if (preselectedId && this.selectedServices.includes(Number(preselectedId))) {
                const svc = this.services.find(s => s.id == preselectedId);
                if (svc && svc.category && !this.expandedCategories.includes(svc.category)) {
                    this.expandedCategories.push(svc.category);
                }
                if (!@js(old('customer_name'))) {
                    this.handleProceedToDetails();
                }
            }
        },

        async fetchAvailability(date) {
            if (!date) return;
            try {
                const response = await fetch(`{{ route('customer.schedule.availability') }}?date=${date}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.slotAvailability = { ...this.slotAvailability, [date]: data };
                    if (this.selectedTimeSlot && (
                        (data.booked_slots && data.booked_slots.includes(this.selectedTimeSlot)) ||
                        (data.past_slots && data.past_slots.includes(this.selectedTimeSlot))
                    )) {
                        this.selectedTimeSlot = '';
                        this.formData.preferredTime = '';
                        showToast.error('The selected time slot is no longer available on this date. Please select an available slot.');
                    }
                }
            } catch (e) {
                console.error('Failed to fetch schedule availability', e);
            }
        },

        getDateAvailability(date) {
            const data = this.slotAvailability[date];
            if (!data) {
                return { isFullyBooked: false, availableSlots: this.timeSlots, bookedSlots: [], pastSlots: [], isLoading: true };
            }
            return {
                isFullyBooked: data.is_fully_booked || (data.available_slots && data.available_slots.length === 0),
                availableSlots: data.available_slots || [],
                bookedSlots: data.booked_slots || [],
                pastSlots: data.past_slots || [],
                isLoading: false
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
            if (this.formData.preferredDate) {
                this.fetchAvailability(this.formData.preferredDate);
            }
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

            if (this.requiresDamagePhotos()) {
                if (this.damagePhotoFiles.length === 0) {
                    showToast.error('Please upload at least 1 damage photo for paint/custom build inspection.');
                    return;
                }
                if (this.damagePhotoFiles.length > 6) {
                    showToast.error('Maximum 6 damage photos allowed.');
                    return;
                }
            }

            if (this.hasPaintTouchUp()) {
                if (this.selectedPanels.length < 1 || this.selectedPanels.length > 2) {
                    showToast.error('Paint touch up requires selecting exactly 1 to 2 panels.');
                    return;
                }
            } else if (this.hasCustomPaint()) {
                if (this.selectedPanels.length < 1) {
                    showToast.error('Please select at least 1 panel for custom paint job.');
                    return;
                }
            } else if (this.hasBodyRepair()) {
                if (this.selectedPanels.length < 1) {
                    showToast.error('Please select at least 1 panel for body repair.');
                    return;
                }
            }

            this.currentStep = 3;
            window.scrollTo(0, 0);
        },

        handleSubmit() {
            if (this.isSubmitting) {
                return;
            }

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

            this.isSubmitting = true;

            const submitBtn = document.getElementById('submit-booking-btn');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.style.pointerEvents = 'none';
            }

            this.syncDamagePhotosInput();

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

            if (this.isPaintRelated() || this.requiresDamagePhotos()) {
                const scopeInput = document.createElement('input');
                scopeInput.type = 'hidden';
                scopeInput.name = 'paint_scope';
                scopeInput.value = this.currentScopeMode();
                scopeInput.setAttribute('data-dynamic-input', 'true');
                form.appendChild(scopeInput);

                this.selectedPanels.forEach(panelCode => {
                    const pInput = document.createElement('input');
                    pInput.type = 'hidden';
                    pInput.name = 'panels[]';
                    pInput.value = panelCode;
                    pInput.setAttribute('data-dynamic-input', 'true');
                    form.appendChild(pInput);

                    if (this.panelSeverity[panelCode]) {
                        const sInput = document.createElement('input');
                        sInput.type = 'hidden';
                        sInput.name = `panel_severity[${panelCode}]`;
                        sInput.value = this.panelSeverity[panelCode];
                        sInput.setAttribute('data-dynamic-input', 'true');
                        form.appendChild(sInput);
                    }
                });
            }

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
        <input type="file" name="damage_photos[]" id="hidden-damage-photos" multiple class="hidden" />
        <input type="checkbox" name="agreed_to_terms" value="1" />
    </form>
    {{-- Progress Indicator --}}
    <div class="flex items-center justify-center gap-1.5 sm:gap-3 mb-6 flex-wrap sm:flex-nowrap">
        <div class="flex items-center gap-1.5 sm:gap-2">
            <div 
                :class="currentStep === 1 ? 'bg-[#E63946] text-white' : 'bg-green-500 text-white'"
                class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center font-bold text-xs sm:text-base transition-all duration-300 shrink-0"
            >
                <template x-if="currentStep === 1"><span>1</span></template>
                <template x-if="currentStep > 1">
                    <x-icon name="check-square" class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                </template>
            </div>
            <span :class="currentStep === 1 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'" class="font-medium text-xs sm:text-sm">
                Services
            </span>
        </div>
        <div class="w-6 sm:w-12 h-0.5 sm:h-1 bg-gray-300 dark:bg-gray-700"></div>
        <div class="flex items-center gap-1.5 sm:gap-2">
            <div 
                :class="currentStep === 2 ? 'bg-[#E63946] text-white' : (currentStep > 2 ? 'bg-green-500 text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-600 dark:text-gray-400')"
                class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center font-bold text-xs sm:text-base transition-all duration-300 shrink-0"
            >
                <template x-if="currentStep <= 2"><span>2</span></template>
                <template x-if="currentStep > 2">
                    <x-icon name="check-square" class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                </template>
            </div>
            <span :class="currentStep === 2 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'" class="font-medium text-xs sm:text-sm">
                Details
            </span>
        </div>
        <div class="w-6 sm:w-12 h-0.5 sm:h-1 bg-gray-300 dark:bg-gray-700"></div>
        <div class="flex items-center gap-1.5 sm:gap-2">
            <div 
                :class="currentStep === 3 ? 'bg-[#E63946] text-white' : 'bg-gray-300 dark:bg-gray-700 text-gray-600 dark:text-gray-400'"
                class="w-8 h-8 sm:w-10 sm:h-10 rounded-full flex items-center justify-center font-bold text-xs sm:text-base transition-all duration-300 shrink-0"
            >
                <span>3</span>
            </div>
            <span :class="currentStep === 3 ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'" class="font-medium text-xs sm:text-sm">
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
                <div class="flex items-center gap-2 mb-2">
                    <x-icon name="shield" class="text-[#457B9D] w-6 h-6" />
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Select Preferred Brands (Optional)</h2>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                    Select your preferred brand to view its specs, price, and automatically calculate your estimated bill total.
                </p>

                <div class="space-y-6">
                    <template x-for="service in services.filter(s => selectedServices.includes(s.id) && s.brands && s.brands.length > 0)" :key="service.id">
                        <div class="bg-gray-50 dark:bg-[#151515] rounded-xl p-4 border border-gray-200 dark:border-white/5 space-y-3">
                            <div class="flex items-start gap-3">
                                <x-icon name="check-square" class="text-[#457B9D] flex-shrink-0 mt-0.5 w-5 h-5" />
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 dark:text-white text-base" x-text="service.name"></h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Available brands for this service:</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pl-0 sm:pl-8">
                                <template x-for="brand in service.brands" :key="brand.name">
                                    <button
                                        type="button"
                                        @click="handleBrandSelection(service.id, brand.name)"
                                        class="text-left p-3.5 rounded-xl border-2 transition-all cursor-pointer flex flex-col justify-between"
                                        :class="selectedBrands[service.id] === brand.name ? 'border-[#457B9D] bg-blue-50/80 dark:bg-blue-950/40 ring-2 ring-[#457B9D]/30' : 'border-gray-200 dark:border-white/10 bg-white dark:bg-[#0B0B0B] hover:border-gray-300 dark:hover:border-white/20'"
                                    >
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between gap-2">
                                                <span class="font-bold text-gray-900 dark:text-white text-sm" x-text="brand.name"></span>
                                                <span class="px-2 py-0.5 rounded-md text-xs font-mono font-bold bg-[#E63946]/10 text-[#E63946] border border-[#E63946]/20">
                                                    ₱<span x-text="Number(brand.price).toLocaleString('en-US', { minimumFractionDigits: 2 })"></span>
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed" x-text="brand.short_description || 'Branded performance component'"></p>
                                        </div>

                                        <div class="mt-3 pt-2 border-t border-gray-100 dark:border-white/5 flex items-center justify-between text-[11px]">
                                            <span class="font-semibold" :class="selectedBrands[service.id] === brand.name ? 'text-[#457B9D]' : 'text-gray-400'" x-text="selectedBrands[service.id] === brand.name ? '✓ Selected Brand' : 'Click to select'"></span>
                                            <x-icon name="check-circle" class="w-4 h-4 text-[#457B9D]" x-show="selectedBrands[service.id] === brand.name" />
                                        </div>
                                    </button>
                                </template>
                            </div>

                            <template x-if="selectedBrands[service.id]">
                                <div class="sm:ml-8 p-3 bg-blue-50 dark:bg-blue-950/40 border-l-4 border-[#457B9D] rounded-xl flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-gray-700 dark:text-gray-300">
                                            <strong>Selected Brand:</strong> <span class="font-bold text-gray-900 dark:text-white" x-text="selectedBrands[service.id]"></span>
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="getSelectedBrandDesc(service.id)"></p>
                                    </div>
                                    <div class="text-right font-mono font-bold text-sm text-[#E63946] pl-4">
                                        ₱<span x-text="getSelectedBrandPrice(service.id)"></span>
                                    </div>
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
                        <div class="flex items-center justify-between flex-wrap gap-2 mb-1">
                            <h3 class="text-lg font-bold">Estimated Total Cost</h3>
                            <span x-show="getEstimatedCost().isExact" class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-white/20 text-white border border-white/30 uppercase tracking-wider">
                                Exact Brand Pricing Calculated
                            </span>
                        </div>
                        <p class="text-3xl font-extrabold mb-3" x-text="getEstimatedCost().display"></p>
                        <div class="bg-white/20 rounded-xl p-3 text-sm space-y-2">
                            <p class="font-medium mb-1">Selected Services (<span x-text="selectedServices.length"></span>):</p>
                            <ul class="space-y-1.5 divide-y divide-white/10">
                                <template x-for="id in selectedServices" :key="id">
                                    <li class="pt-1.5 flex items-center justify-between text-xs sm:text-sm">
                                        <div class="flex items-center gap-2">
                                            <span>•</span>
                                            <span class="font-medium" x-text="services.find(s => s.id === id).name"></span>
                                            <template x-if="selectedBrands[id]">
                                                <span class="ml-1 text-[11px] bg-white/20 px-2 py-0.5 rounded font-semibold" x-text="selectedBrands[id]"></span>
                                            </template>
                                        </div>
                                        <div class="font-mono font-bold">
                                            <template x-if="getSelectedBrandObj(id) && Number(getSelectedBrandObj(id).price) > 0">
                                                <span x-text="'₱' + Number(getSelectedBrandObj(id).price).toLocaleString('en-US', { minimumFractionDigits: 2 })"></span>
                                            </template>
                                            <template x-if="!getSelectedBrandObj(id) || Number(getSelectedBrandObj(id).price) === 0">
                                                <span x-text="'₱' + services.find(s => s.id === id).estimatedPrice.min.toLocaleString() + ' - ₱' + services.find(s => s.id === id).estimatedPrice.max.toLocaleString()"></span>
                                            </template>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                        <p class="text-xs mt-3 text-white/90">
                            ⚠️ Prices include selected brand parts, materials, and labor. Final billing will be locked upon service inspection.
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
        @if ($selectedProduct)
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-300 dark:border-emerald-700 flex items-center justify-between flex-wrap gap-3 animate-fade-in">
                <div class="flex items-center gap-3">
                    <span class="p-2 rounded-lg bg-emerald-500 text-white shadow-sm">
                        <x-icon name="box" class="w-5 h-5" />
                    </span>
                    <div>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">
                            Selected from Catalog: {{ $selectedProduct->name }}
                        </p>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            Service installation for this part/accessory has been automatically selected for your booking.
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xs text-gray-500 block">Catalog Unit Price</span>
                    <span class="font-mono font-bold text-base text-[#E63946]">₱{{ number_format($selectedProduct->unit_price, 2) }}</span>
                </div>
            </div>
        @endif

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

                {{-- Saved vehicle picker (shown only when user has saved vehicles) --}}
                <template x-if="savedVehicles.length > 0">
                    <div class="mb-4 p-4 rounded-xl bg-[#457B9D]/10 border border-[#457B9D]/30">
                        <p class="text-sm font-semibold text-[#457B9D] dark:text-[#6baac8] mb-2">
                            🚗 Select from My Garage
                        </p>
                        <div class="flex gap-3 items-end">
                            <div class="flex-1">
                                <select
                                    x-model="selectedSavedVehicleId"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#457B9D] focus:border-transparent transition-all"
                                >
                                    <option value="">— Pick a saved vehicle —</option>
                                    <template x-for="v in savedVehicles" :key="v.id">
                                        <option :value="v.id" x-text="v.display_name + ' · ' + v.plate_number"></option>
                                    </template>
                                </select>
                            </div>
                            <button
                                type="button"
                                @click="handleSelectSavedVehicle()"
                                :disabled="!selectedSavedVehicleId"
                                class="inline-flex items-center justify-center rounded-xl font-semibold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer bg-white/10 dark:bg-white/5 text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 hover:border-[#E63946] hover:text-[#E63946] px-4 py-2 text-sm whitespace-nowrap"
                            >
                                Use This Vehicle
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            Selecting a vehicle auto-fills the fields below. You can still edit them.
                        </p>
                    </div>
                </template>

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

            {{-- Paint Scope & Panel Inspection (Only for Paint / Custom Build Services) --}}
            <template x-if="isPaintRelated() || requiresDamagePhotos()">
                <x-card class="border-2 border-[#E63946]/40 dark:border-[#E63946]/30 overflow-hidden space-y-6">
                    <div class="flex items-center justify-between flex-wrap gap-3 pb-4 border-b border-gray-200 dark:border-white/10">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="p-2 rounded-lg bg-[#E63946] text-white shadow-sm">
                                    <x-icon name="tool" class="w-5 h-5" />
                                </span>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Paint Scope & Damage Inspection</h2>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                Estimates are shop rate ranges. Final price is confirmed after vehicle and damage photo review.
                            </p>
                        </div>

                        <div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#E63946]/10 text-[#E63946] border border-[#E63946]/30">
                                <span class="w-2 h-2 rounded-full bg-[#E63946] mr-2 animate-pulse"></span>
                                <span x-text="hasPaintTouchUp() ? 'Touch Up Scope (1-2 Panels)' : (hasCustomPaint() ? 'Custom Paint Scope (Multi-Panel)' : (hasFullBuild() ? 'Full Custom Build Package' : 'Body Repair Inspection'))"></span>
                            </span>
                        </div>
                    </div>

                    {{-- Presets Toolbar (Hidden if touch-up, since touch-up is limited to 1-2 panels) --}}
                    <div x-show="!hasPaintTouchUp()" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Quick Selection Presets</label>
                            <span class="text-xs text-gray-500">Selected: <strong class="text-[#E63946]" x-text="selectedPanels.length"></strong> panels</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="applyPanelPreset('front_bumper')" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-white/10 bg-white/5 hover:border-[#E63946] hover:text-[#E63946] transition-all">Front Bumper</button>
                            <button type="button" @click="applyPanelPreset('rear_bumper')" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-white/10 bg-white/5 hover:border-[#E63946] hover:text-[#E63946] transition-all">Rear Bumper</button>
                            <button type="button" @click="applyPanelPreset('both_bumpers')" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-white/10 bg-white/5 hover:border-[#E63946] hover:text-[#E63946] transition-all">Both Bumpers</button>
                            <button type="button" @click="applyPanelPreset('hood')" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-white/10 bg-white/5 hover:border-[#E63946] hover:text-[#E63946] transition-all">Hood</button>
                            <button type="button" @click="applyPanelPreset('all_doors')" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-white/10 bg-white/5 hover:border-[#E63946] hover:text-[#E63946] transition-all">All Doors</button>
                            <button type="button" @click="applyPanelPreset('all_fenders')" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 dark:border-white/10 bg-white/5 hover:border-[#E63946] hover:text-[#E63946] transition-all">All Fenders</button>
                            <button type="button" @click="applyPanelPreset('select_all')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-[#E63946] text-white hover:bg-[#c92a37] transition-all">Select All</button>
                            <button type="button" @click="applyPanelPreset('clear_all')" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-red-300 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30 transition-all">Clear All</button>
                        </div>
                    </div>

                    {{-- Touch Up Note --}}
                    <div x-show="hasPaintTouchUp()" class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 text-xs text-amber-800 dark:text-amber-200 flex items-center gap-2">
                        <x-icon name="info" class="w-4 h-4 flex-shrink-0" />
                        <span>Paint Touch Up scope is strictly limited to <strong>1 or 2 panels</strong>. Select your panels below.</span>
                    </div>

                    {{-- Panel Checklist Grouped --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-bold text-gray-900 dark:text-white">Panel Checklist & Damage Severity</label>
                            <span class="text-xs text-gray-500">Note: Choosing <em>Dent</em> or <em>Body Work</em> includes Body Repair (+₱5,000–₱7,000)</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $groups = [
                                    'front' => 'Front Panels',
                                    'doors' => 'Doors',
                                    'rear_side' => 'Rear & Quarters',
                                    'other' => 'Roof & Accents',
                                ];
                            @endphp

                            @foreach ($groups as $groupKey => $groupTitle)
                                <div class="p-4 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/[0.02] space-y-3">
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#457B9D] dark:text-[#6baac8] flex items-center justify-between">
                                        <span>{{ $groupTitle }}</span>
                                    </h3>
                                    <div class="space-y-2">
                                        <template x-for="p in paintPanels.filter(item => item.group === '{{ $groupKey }}')" :key="p.code">
                                            <div class="p-2.5 rounded-lg border transition-all"
                                                :class="selectedPanels.includes(p.code) ? 'border-[#E63946] bg-[#E63946]/5 dark:bg-[#E63946]/10' : 'border-gray-200 dark:border-white/5 bg-white dark:bg-[#151515] hover:border-gray-300 dark:hover:border-white/20'"
                                            >
                                                <div class="flex items-center justify-between gap-2">
                                                    <label class="flex items-center gap-2.5 cursor-pointer select-none flex-1">
                                                        <input 
                                                            type="checkbox" 
                                                            :checked="selectedPanels.includes(p.code)"
                                                            @change="togglePanel(p.code)"
                                                            class="rounded border-gray-300 text-[#E63946] focus:ring-[#E63946] w-4 h-4 cursor-pointer"
                                                        />
                                                        <span class="text-sm font-medium text-gray-900 dark:text-white" x-text="p.name"></span>
                                                    </label>

                                                    <template x-if="selectedPanels.includes(p.code)">
                                                        <span x-show="panelSeverity[p.code] === 'dent' || panelSeverity[p.code] === 'body_work'" class="text-[10px] px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 font-bold">
                                                            +Body Repair
                                                        </span>
                                                    </template>
                                                </div>

                                                <template x-if="selectedPanels.includes(p.code)">
                                                    <div class="mt-2.5 pt-2 border-t border-gray-200/60 dark:border-white/10 flex items-center gap-2">
                                                        <span class="text-xs text-gray-500 shrink-0">Condition:</span>
                                                        <select
                                                            :value="panelSeverity[p.code] || 'paint_only'"
                                                            @change="setPanelSeverity(p.code, $event.target.value)"
                                                            class="text-xs py-1 px-2 rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1E1E1E] text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-[#E63946]"
                                                        >
                                                            <option value="light_scratch">Light Scratch</option>
                                                            <option value="paint_only">Paint Only</option>
                                                            <option value="dent">Dent (Requires Body Repair)</option>
                                                            <option value="body_work">Heavy Body Work</option>
                                                            <option value="replace_panel">Panel Replacement</option>
                                                        </select>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Multi-photo upload for damage / inspection --}}
                    <div class="space-y-3 pt-4 border-t border-gray-200 dark:border-white/10">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-bold text-gray-900 dark:text-white">
                                    Damage & Vehicle Inspection Photos <span class="text-red-500">*</span>
                                </label>
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    Upload clear photos of the target panels, scratches, or dented areas (1 to 6 photos, max 5MB each, PNG/JPG).
                                </p>
                            </div>
                            <span class="text-xs font-semibold" :class="damagePhotoFiles.length > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-400'">
                                <span x-text="damagePhotoFiles.length"></span>/6 Photos
                            </span>
                        </div>

                        <input 
                            type="file" 
                            x-ref="damagePhotoInput" 
                            @change="handleDamagePhotosUpload" 
                            multiple 
                            accept="image/png,image/jpeg,image/jpg" 
                            class="hidden" 
                        />

                        {{-- Dropzone / Upload button --}}
                        <div 
                            @click="$refs.damagePhotoInput.click()"
                            class="border-2 border-dashed border-gray-300 dark:border-white/10 hover:border-[#E63946] rounded-xl p-6 text-center cursor-pointer transition-all bg-white/5 hover:bg-[#E63946]/5"
                        >
                            <x-icon name="camera" class="w-8 h-8 mx-auto text-[#E63946] mb-2" />
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">Click or tap to upload damage photos</p>
                            <p class="text-xs text-gray-500 mt-1">PNG, JPG, or JPEG up to 5MB per photo (1 to 6 photos)</p>
                        </div>

                        {{-- Photo Preview Grid --}}
                        <template x-if="damagePhotoPreviews.length > 0">
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 pt-2">
                                <template x-for="(preview, idx) in damagePhotoPreviews" :key="idx">
                                    <div class="relative group rounded-xl overflow-hidden border border-gray-200 dark:border-white/10 bg-black aspect-square flex items-center justify-center">
                                        <img :src="preview.url" :alt="preview.name" class="w-full h-full object-cover" />
                                        <button 
                                            type="button" 
                                            @click.stop="removeDamagePhoto(idx)"
                                            class="absolute top-1 right-1 p-1 rounded-full bg-red-600 text-white hover:bg-red-700 shadow-md transition-all cursor-pointer"
                                            title="Remove photo"
                                        >
                                            <x-icon name="trash" class="w-3.5 h-3.5" />
                                        </button>
                                        <div class="absolute bottom-0 inset-x-0 bg-black/70 p-1 text-[10px] text-white truncate text-center" x-text="preview.size"></div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    {{-- Live Estimate Breakdown Box --}}
                    <template x-if="serverEstimate">
                        <div class="p-4 rounded-xl bg-gray-50 dark:bg-white/[0.03] border border-gray-200 dark:border-white/10 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Live Estimate Snapshot</span>
                                <span class="text-base font-extrabold text-[#E63946]" x-text="serverEstimate.display"></span>
                            </div>
                            <template x-if="serverEstimate.breakdown && serverEstimate.breakdown.length > 0">
                                <div class="pt-2 border-t border-gray-200 dark:border-white/10 space-y-1">
                                    <template x-for="(line, idx) in serverEstimate.breakdown" :key="idx">
                                        <div class="flex items-center justify-between text-xs text-gray-700 dark:text-gray-300">
                                            <span x-text="line.title"></span>
                                            <span class="font-mono font-semibold" x-text="'₱' + Number(line.min).toLocaleString() + ' - ₱' + Number(line.max).toLocaleString()"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </x-card>
            </template>

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
                                class="mt-3 p-3 rounded-xl border transition-all"
                                :class="isSunday(formData.preferredDate) || getDateAvailability(formData.preferredDate).isFullyBooked ? 'bg-red-50 dark:bg-red-950/30 border-red-200 dark:border-red-800' : (getDateAvailability(formData.preferredDate).isLoading ? 'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800' : 'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800')"
                            >
                                <div class="flex items-start gap-2">
                                    <span class="w-5 h-5 mt-0.5 flex-shrink-0" :class="isSunday(formData.preferredDate) || getDateAvailability(formData.preferredDate).isFullyBooked ? 'text-red-600 dark:text-red-400' : (getDateAvailability(formData.preferredDate).isLoading ? 'text-amber-600 dark:text-amber-400' : 'text-blue-600 dark:text-blue-400')">
                                        <x-icon name="info" class="w-5 h-5" />
                                    </span>
                                    <div class="text-sm">
                                        <template x-if="getDateAvailability(formData.preferredDate).isLoading">
                                            <p class="text-amber-700 dark:text-amber-300 font-medium animate-pulse">Checking schedule availability for selected date...</p>
                                        </template>
                                        <template x-if="!getDateAvailability(formData.preferredDate).isLoading && isSunday(formData.preferredDate)">
                                            <p class="text-red-600 dark:text-red-400 font-medium">We are closed on Sundays. Please select a weekday (Monday-Saturday).</p>
                                        </template>
                                        <template x-if="!getDateAvailability(formData.preferredDate).isLoading && !isSunday(formData.preferredDate)">
                                            <div>
                                                <template x-if="getDateAvailability(formData.preferredDate).isFullyBooked">
                                                    <p class="text-red-600 dark:text-red-400 font-medium">⛔ This date is fully booked. All time slots have been reserved by other customers. Please select another date.</p>
                                                </template>
                                                <template x-if="!getDateAvailability(formData.preferredDate).isFullyBooked">
                                                    <div class="text-blue-800 dark:text-blue-200">
                                                        <p class="font-medium mb-1">
                                                            ✓ <span x-text="getDateAvailability(formData.preferredDate).availableSlots.length"></span> time slots available
                                                        </p>
                                                        <template x-if="getDateAvailability(formData.preferredDate).bookedSlots.length > 0">
                                                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                                                ℹ️ <span x-text="getDateAvailability(formData.preferredDate).bookedSlots.length"></span> slot(s) already booked on this day
                                                            </p>
                                                        </template>
                                                        <template x-if="getDateAvailability(formData.preferredDate).pastSlots.length > 0">
                                                            <p class="text-xs text-blue-700 dark:text-blue-300">
                                                                ⏰ <span x-text="getDateAvailability(formData.preferredDate).pastSlots.length"></span> time slot(s) have already passed
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
                    <template x-if="formData.preferredDate && !isSunday(formData.preferredDate) && !getDateAvailability(formData.preferredDate).isFullyBooked && !getDateAvailability(formData.preferredDate).isLoading">
                        <div>
                            <label class="block text-sm font-medium mb-3 text-gray-900 dark:text-white">Select Preferred Time Slot *</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5">
                                <template x-for="slot in timeSlots" :key="slot">
                                    @php
                                        $bookedCheck = "getDateAvailability(formData.preferredDate).bookedSlots.includes(slot)";
                                        $pastCheck = "getDateAvailability(formData.preferredDate).pastSlots.includes(slot)";
                                        $disabledCheck = "({$bookedCheck} || {$pastCheck})";
                                    @endphp
                                    <button
                                        type="button"
                                        @click="if (!{{ $disabledCheck }}) { selectedTimeSlot = slot; formData.preferredTime = slot; }"
                                        :disabled="{{ $disabledCheck }}"
                                        class="p-3 rounded-xl border-2 text-sm font-bold transition-all cursor-pointer flex flex-col items-center justify-center gap-1 shadow-sm"
                                        :class="{{ $bookedCheck }} ? 'bg-red-50/80 dark:bg-red-950/30 border-red-200 dark:border-red-900/40 text-red-400 dark:text-red-500/70 cursor-not-allowed opacity-80' : ({{ $pastCheck }} ? 'bg-gray-100 dark:bg-gray-800/60 border-gray-200 dark:border-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed opacity-60' : (selectedTimeSlot === slot ? 'bg-green-600 border-green-600 text-white shadow-green-600/30 shadow-md' : 'bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white hover:border-[#457B9D] hover:bg-blue-50 dark:hover:bg-blue-950/30'))"
                                    >
                                        <div class="flex items-center gap-1">
                                            <x-icon name="calendar" class="w-4 h-4" />
                                            <span x-text="slot"></span>
                                        </div>
                                        <template x-if="{{ $bookedCheck }}">
                                            <span class="px-2 py-0.5 text-[10px] uppercase tracking-wider rounded bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 font-extrabold border border-red-200 dark:border-red-800">
                                                Booked
                                            </span>
                                        </template>
                                        <template x-if="{{ $pastCheck }}">
                                            <span class="px-2 py-0.5 text-[10px] uppercase tracking-wider rounded bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 font-extrabold border border-gray-300 dark:border-gray-600">
                                                Unavailable
                                            </span>
                                        </template>
                                        <template x-if="selectedTimeSlot === slot && !{{ $disabledCheck }}">
                                            <span class="px-2 py-0.5 text-[10px] uppercase tracking-wider rounded bg-green-700 text-white font-extrabold">
                                                Selected
                                            </span>
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
                                <div class="w-4 h-4 rounded bg-red-100 dark:bg-red-900/50 border-2 border-red-200 dark:border-red-800"></div>
                                <span class="text-xs text-gray-700 dark:text-gray-300">Booked</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-gray-200 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600"></div>
                                <span class="text-xs text-gray-700 dark:text-gray-300">Unavailable / Past</span>
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
        <form @submit.prevent="if (!isSubmitting) handleSubmit()" class="space-y-6">
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

                    {{-- Panels Summary --}}
                    <template x-if="selectedPanels.length > 0">
                        <div class="flex justify-between items-start border-t border-gray-100 dark:border-white/5 pt-2">
                            <span class="text-gray-600 dark:text-gray-400 text-sm">Selected Panels (<span x-text="selectedPanels.length"></span>)</span>
                            <div class="text-right max-w-md flex flex-wrap justify-end gap-1">
                                <template x-for="pCode in selectedPanels" :key="pCode">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-gray-200 dark:border-gray-700">
                                        <span x-text="paintPanels.find(p => p.code === pCode)?.name || pCode"></span>
                                        <template x-if="panelSeverity[pCode] && panelSeverity[pCode] !== 'paint_only'">
                                            <span class="ml-1 text-[10px] text-amber-600 dark:text-amber-400 font-bold" x-text="'(' + panelSeverity[pCode] + ')'"></span>
                                        </template>
                                    </span>
                                </template>
                            </div>
                        </div>
                    </template>

                    {{-- Damage Photos Summary --}}
                    <template x-if="damagePhotoPreviews.length > 0">
                        <div class="flex justify-between items-center border-t border-gray-100 dark:border-white/5 pt-2">
                            <span class="text-gray-600 dark:text-gray-400 text-sm">Damage Photos</span>
                            <div class="flex items-center gap-1.5">
                                <template x-for="(p, idx) in damagePhotoPreviews" :key="idx">
                                    <img :src="p.url" :alt="p.name" class="w-8 h-8 rounded-lg object-cover border border-gray-200 dark:border-white/10" />
                                </template>
                                <span class="text-xs text-gray-500 ml-1" x-text="'(' + damagePhotoPreviews.length + ' photos)'"></span>
                            </div>
                        </div>
                    </template>

                    <div class="flex justify-between border-t border-gray-200 dark:border-white/10 pt-3">
                        <span class="text-gray-600 dark:text-gray-400">Total Estimate</span>
                        <div class="text-right">
                            <span class="font-bold text-gray-900 dark:text-white" x-text="getEstimatedCost()?.display"></span>
                            <p class="text-[11px] text-gray-500">Estimates are shop rate ranges; final price confirmed after inspection.</p>
                        </div>
                    </div>

                    {{-- Breakdown if available --}}
                    <template x-if="serverEstimate && serverEstimate.breakdown && serverEstimate.breakdown.length > 0">
                        <div class="p-3 rounded-lg bg-gray-50 dark:bg-white/[0.02] border border-gray-200/60 dark:border-white/5 space-y-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-500">Quotation Breakdown</span>
                            <template x-for="(item, idx) in serverEstimate.breakdown" :key="idx">
                                <div class="flex justify-between text-xs text-gray-600 dark:text-gray-400">
                                    <span x-text="item.title"></span>
                                    <span class="font-mono" x-text="'₱' + Number(item.min).toLocaleString() + ' - ₱' + Number(item.max).toLocaleString()"></span>
                                </div>
                            </template>
                        </div>
                    </template>

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
                                <div class="flex flex-col items-center justify-center p-4 text-center">
                                    <template x-if="!uploadedFilePreview">
                                        <div>
                                            <x-icon name="check-square" class="w-12 h-12 text-gray-400 mb-3 mx-auto" />
                                            <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">
                                                <span class="font-bold">Click to upload</span> or drag and drop
                                            </p>
                                            <p class="text-xs text-gray-500 mb-2">PNG, JPG or JPEG (MAX. 5MB)</p>
                                        </div>
                                    </template>
                                    <template x-if="uploadedFilePreview">
                                        <div class="flex flex-col items-center justify-center gap-1.5">
                                            <img :src="uploadedFilePreview" alt="Payment Screenshot Preview" class="h-20 w-20 object-cover rounded-lg border-2 border-green-500 shadow-sm" />
                                            <div class="flex items-center gap-1 text-xs font-bold text-green-600">
                                                <x-icon name="check-square" class="w-4 h-4 text-green-600" />
                                                <span x-text="uploadedFile" class="truncate max-w-[200px]"></span>
                                            </div>
                                            <p class="text-[10px] text-gray-400">Click box to replace file</p>
                                        </div>
                                    </template>
                                    <template x-if="uploadedFile && !uploadedFilePreview">
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
                <x-button type="button" variant="outline" @click="if (!isSubmitting) currentStep = 2" x-bind:disabled="isSubmitting">
                    <x-icon name="chevron-right" class="w-5 h-5 mr-2 inline-block transform rotate-180" />
                    Back to Details
                </x-button>
                <div class="flex gap-4">
                    <a href="{{ url('/customer') }}" x-bind:class="isSubmitting ? 'pointer-events-none opacity-50' : ''">
                        <x-button type="button" variant="outline" class="border-red-500 text-red-500 hover:bg-red-500 hover:text-white" x-bind:disabled="isSubmitting">Cancel</x-button>
                    </a>
                    <x-button 
                        id="submit-booking-btn"
                        type="submit" 
                        size="lg" 
                        variant="accent" 
                        class="text-white bg-green-600 hover:bg-green-700 border-green-600 transition-all duration-300"
                        x-bind:disabled="isSubmitting"
                        x-bind:class="isSubmitting ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''"
                        @click="if (isSubmitting) { $event.preventDefault(); return false; }"
                    >
                        <template x-if="isSubmitting">
                            <span class="inline-flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                                </svg>
                                Submitting Booking...
                            </span>
                        </template>
                        <template x-if="!isSubmitting">
                            <span class="inline-flex items-center">
                                <x-icon name="check-square" class="w-5 h-5 mr-2 inline-block text-white" />
                                Submit Booking &amp; Payment
                            </span>
                        </template>
                    </x-button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
