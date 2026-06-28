@extends('layouts.dashboard')

@section('title', 'Payment Details | AutoProject+')

@section('content')
@php
    $hasPayment = isset($payment) && $payment;
    if ($hasPayment) {
        $badgeStatus = $payment->status === 'submitted' ? 'pending' : ($payment->status === 'verified' ? 'approved' : $payment->status);
        $statusLabels = [
            'pending' => 'Pending Verification',
            'submitted' => 'Pending Verification',
            'verified' => 'Paid & Verified',
            'rejected' => 'Rejected',
        ];
        $displayStatus = $statusLabels[$payment->status] ?? ucfirst($payment->status);
    }
@endphp

<div class="max-w-6xl mx-auto space-y-6 animate-fade-in">
    @if ($errors->any())
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 dark:bg-[#1a1111] dark:text-red-400 border border-red-500/20" role="alert">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($hasPayment)
        {{-- VIEW ONLY & EDITABLE PAYMENT DETAILS --}}
        <form 
            method="POST" 
            action="{{ route('customer.payment.submit', $bookingId) }}" 
            enctype="multipart/form-data"
            x-data="{
                isEditing: {{ $errors->any() ? 'true' : 'false' }},
                referenceNumber: '{{ addslashes($payment->reference_number) }}',
                selectedPayment: '{{ $payment->method }}',
                uploadedFile: null,
                handleFileUpload(e) {
                    if (e.target.files && e.target.files[0]) {
                        this.uploadedFile = e.target.files[0].name;
                    }
                }
            }"
            class="space-y-6"
        >
            @csrf
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Reservation Payment Details</h1>
                    <p class="text-gray-600 dark:text-gray-400">View information about your submitted reservation fee.</p>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <a href="{{ route('customer.bookings.index') }}" x-show="!isEditing">
                        <x-button variant="outline" type="button">
                            <x-icon name="arrow-right" class="w-4 h-4 mr-2 inline-block transform rotate-180" />
                            Back to Bookings
                        </x-button>
                    </a>
                    
                    @if ($payment->status !== 'verified')
                        <button 
                            type="button" 
                            @click="isEditing = true" 
                            x-show="!isEditing"
                            class="inline-flex items-center justify-center rounded-xl font-semibold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer bg-gradient-red text-white hover:shadow-xl hover:shadow-[#E63946]/50 glow-red-hover border border-transparent px-4 py-2 text-sm whitespace-nowrap"
                        >
                            <x-icon name="edit" class="w-4 h-4 mr-2" />
                            Edit Details
                        </button>
                        
                        <div class="flex gap-2 w-full sm:w-auto" x-show="isEditing" style="display: none;">
                            <x-button variant="outline" type="button" @click="isEditing = false; uploadedFile = null;">
                                Cancel
                            </x-button>
                            <x-button variant="accent" type="submit">
                                Save Changes
                            </x-button>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Left column: Details --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Payment info --}}
                    <x-card>
                        <div class="flex items-center justify-between border-b border-gray-200 dark:border-white/10 pb-4 mb-4">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Payment Information</h2>
                            <x-status-badge :status="$badgeStatus">
                                {{ $displayStatus }}
                            </x-status-badge>
                        </div>

                        @if ($payment->status === 'rejected' && $payment->rejection_reason)
                            <div class="mb-6 p-4 bg-red-50 dark:bg-red-950/20 border-l-4 border-red-500 rounded-r-xl">
                                <p class="text-sm font-bold text-red-800 dark:text-red-400 mb-1">Rejection Reason:</p>
                                <p class="text-sm text-red-700 dark:text-red-300">{{ $payment->rejection_reason }}</p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 mb-1">Payment Reference</p>
                                <p class="font-extrabold text-gray-900 dark:text-white text-base font-mono">{{ $payment->payment_number }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 mb-1">Gcash/Maya Reference Number</p>
                                <div x-show="!isEditing">
                                    <p class="font-extrabold text-gray-900 dark:text-white text-base font-mono">{{ $payment->reference_number ?? 'N/A' }}</p>
                                </div>
                                <div x-show="isEditing" style="display: none;">
                                    <input 
                                        type="text" 
                                        name="reference_number" 
                                        x-model="referenceNumber"
                                        class="w-full mt-1 bg-white dark:bg-[#1a1a1a] text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 rounded-xl px-3 py-2 text-sm focus:outline-none focus:border-[#E63946]"
                                        placeholder="GCash/Maya reference number"
                                    />
                                </div>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 mb-1">Payment Method</p>
                                <div x-show="!isEditing">
                                    <p class="font-bold text-gray-900 dark:text-white text-base capitalize" x-text="selectedPayment"></p>
                                </div>
                                <div x-show="isEditing" style="display: none;">
                                    <input type="hidden" name="payment_method" :value="selectedPayment">
                                    <div class="flex gap-2 mt-1">
                                        <button
                                            type="button"
                                            @click="selectedPayment = 'gcash'"
                                            class="flex-1 py-1.5 px-3 rounded-lg border text-xs font-bold transition-all cursor-pointer text-center"
                                            :class="selectedPayment === 'gcash' ? 'border-blue-600 bg-blue-600/10 text-blue-600' : 'border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300'"
                                        >
                                            GCash
                                        </button>
                                        <button
                                            type="button"
                                            @click="selectedPayment = 'maya'"
                                            class="flex-1 py-1.5 px-3 rounded-lg border text-xs font-bold transition-all cursor-pointer text-center"
                                            :class="selectedPayment === 'maya' ? 'border-green-600 bg-green-600/10 text-green-600' : 'border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300'"
                                        >
                                            Maya
                                        </button>
                                    </div>
                                    <div class="mt-4 p-3 bg-[#457B9D]/10 border border-[#457B9D]/20 rounded-xl text-xs space-y-1">
                                        <p class="font-semibold text-[#457B9D]">Payment Instructions:</p>
                                        <p class="text-gray-700 dark:text-gray-300">
                                            Send fee to <span class="capitalize font-bold" x-text="selectedPayment"></span>: 
                                            <strong class="text-[#E63946]" x-text="selectedPayment === 'gcash' ? '{{ $bookingDetails['gcashNumber'] }}' : '{{ $bookingDetails['mayaNumber'] }}'"></strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <p class="text-gray-600 dark:text-gray-400 mb-1">Date Submitted</p>
                                <p class="font-bold text-gray-900 dark:text-white text-base">
                                    {{ $payment->paid_at ? $payment->paid_at->format('F j, Y, h:i A') : $payment->created_at->format('F j, Y, h:i A') }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between items-center bg-[#E63946]/10 p-4 rounded-xl">
                            <span class="font-extrabold text-gray-900 dark:text-white">Amount Paid</span>
                            <span class="text-2xl font-black text-[#E63946]">{{ $payment->formatted_amount }}</span>
                        </div>
                    </x-card>

                    {{-- Booking summary --}}
                    <x-card>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Booking Summary</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Booking ID</span>
                                <span class="font-bold text-gray-900 dark:text-white">BK-{{ $bookingDetails['id'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Service</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $bookingDetails['service'] }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Vehicle</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $bookingDetails['vehicle'] }}</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 dark:border-white/10 pt-3">
                                <span class="text-gray-600 dark:text-gray-400">Total Estimate</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $bookingDetails['totalEstimate'] }}</span>
                            </div>
                        </div>
                    </x-card>
                </div>

                {{-- Right column: Screenshot proof --}}
                <div>
                    <x-card class="h-full flex flex-col">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Payment Screenshot Proof</h2>
                        
                        {{-- Read-only Screenshot --}}
                        <div x-show="!isEditing" class="flex-1 flex flex-col items-center justify-center bg-gray-100 dark:bg-neutral-900 rounded-xl p-4 border border-gray-300 dark:border-white/10">
                            @if ($screenshotUrl)
                                <a href="{{ $screenshotUrl }}" target="_blank" class="block relative group overflow-hidden rounded-lg shadow-md hover:shadow-xl transition-all w-full text-center">
                                    <img src="{{ $screenshotUrl }}" alt="Payment Screenshot Proof" class="mx-auto max-w-full h-auto max-h-96 object-contain rounded-lg transition-transform duration-300 group-hover:scale-105" />
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <span class="text-white text-xs font-bold uppercase tracking-wider bg-black/60 px-3 py-1.5 rounded-lg">View Full Image</span>
                                    </div>
                                </a>
                            @else
                                <div class="text-center py-8">
                                    <x-icon name="info" class="w-12 h-12 text-gray-400 mb-2 mx-auto" />
                                    <p class="text-sm text-gray-600 dark:text-gray-400">No payment screenshot uploaded.</p>
                                </div>
                            @endif
                        </div>

                        {{-- Edit Screenshot --}}
                        <div x-show="isEditing" style="display: none;" class="flex-1 flex flex-col justify-center">
                            <label for="payment_screenshot_input_edit" class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 dark:border-white/15 rounded-xl cursor-pointer hover:border-[#E63946] transition-colors bg-gray-50 dark:bg-white/5">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4">
                                    <x-icon name="check-square" class="w-10 h-10 text-gray-400 mb-2 animate-bounce" />
                                    <p class="mb-1 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-bold">Click to replace screenshot</span>
                                    </p>
                                    <p class="text-xs text-gray-500">PNG, JPG or JPEG (MAX. 5MB)</p>
                                    <p class="text-xs text-gray-400 mt-2">Leave empty to keep existing proof</p>
                                    <template x-if="uploadedFile">
                                        <div class="mt-3 flex items-center justify-center gap-2 text-green-600 text-xs">
                                            <x-icon name="check-square" class="w-4 h-4 text-green-600" />
                                            <span class="font-bold" x-text="uploadedFile"></span>
                                        </div>
                                    </template>
                                </div>
                                <input
                                    id="payment_screenshot_input_edit"
                                    name="payment_screenshot"
                                    type="file"
                                    class="hidden"
                                    accept="image/*"
                                    @change="handleFileUpload"
                                />
                            </label>
                            @if ($screenshotUrl)
                                <div class="mt-4">
                                    <p class="text-xs text-gray-500 mb-1">Current Proof:</p>
                                    <img src="{{ $screenshotUrl }}" class="h-16 w-auto object-contain rounded border border-gray-300 dark:border-white/10" />
                                </div>
                            @endif
                        </div>
                    </x-card>
                </div>
            </div>
        </form>
    @else
        {{-- FALLBACK PAYMENT SUBMISSION FORM --}}
        <div 
            x-data="{
                selectedPayment: 'gcash',
                uploadedFile: null,
                bookingDetails: @js($bookingDetails),
                handleFileUpload(e) {
                    if (e.target.files && e.target.files[0]) {
                        this.uploadedFile = e.target.files[0].name;
                    }
                },
                handleConfirmPayment() {
                    if (!this.uploadedFile) {
                        showToast.error('Please upload payment screenshot');
                        return;
                    }
                    showToast.success('Payment submitted successfully! Your booking is now confirmed.');
                    window.location.href = '{{ url('/customer/track') }}';
                }
            }"
            class="space-y-6"
        >
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Payment</h1>
                <p class="text-gray-600 dark:text-gray-400">Complete your reservation payment to confirm booking.</p>
            </div>

            <x-card>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Booking Summary</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Booking ID</span>
                        <span class="font-bold text-gray-900 dark:text-white" x-text="'BK-' + bookingDetails.id"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Service</span>
                        <span class="font-bold text-gray-900 dark:text-white" x-text="bookingDetails.service"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Vehicle</span>
                        <span class="font-bold text-gray-900 dark:text-white" x-text="bookingDetails.vehicle"></span>
                    </div>
                    <div class="flex justify-between border-t border-gray-200 dark:border-white/10 pt-3">
                        <span class="text-gray-600 dark:text-gray-400">Total Estimate</span>
                        <span class="font-bold text-gray-900 dark:text-white" x-text="bookingDetails.totalEstimate"></span>
                    </div>
                    <div class="flex justify-between items-center bg-[#E63946]/10 p-4 rounded-xl">
                        <span class="font-extrabold text-gray-900 dark:text-white">Reservation Fee (Required)</span>
                        <span class="text-2xl font-black text-[#E63946]" x-text="bookingDetails.reservationFee"></span>
                    </div>
                </div>
            </x-card>

            <x-card>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Payment Method</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <button
                        @click="selectedPayment = 'gcash'"
                        class="p-6 border-2 rounded-xl transition-all cursor-pointer text-center"
                        :class="selectedPayment === 'gcash' ? 'border-[#E63946] bg-[#E63946]/5' : 'border-gray-200 dark:border-white/10 hover:border-[#E63946] bg-white dark:bg-[#151515]'"
                    >
                        <div class="text-4xl font-extrabold text-blue-600 mb-2">GCash</div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Pay via GCash</p>
                    </button>
                    <button
                        @click="selectedPayment = 'maya'"
                        class="p-6 border-2 rounded-xl transition-all cursor-pointer text-center"
                        :class="selectedPayment === 'maya' ? 'border-[#E63946] bg-[#E63946]/5' : 'border-gray-200 dark:border-white/10 hover:border-[#E63946] bg-white dark:bg-[#151515]'"
                    >
                        <div class="text-4xl font-extrabold text-green-600 mb-2">Maya</div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Pay via Maya</p>
                    </button>
                </div>
            </x-card>

            <x-card class="bg-[#457B9D]/10 border border-[#457B9D]/20">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Payment Instructions</h2>
                <ol class="space-y-3 list-decimal list-inside text-gray-700 dark:text-gray-300 text-sm sm:text-base">
                    <li>Send <span class="font-bold" x-text="bookingDetails.reservationFee"></span> reservation fee to our <span class="font-bold capitalize text-[#E63946]" x-text="selectedPayment"></span> number: <strong x-text="selectedPayment === 'gcash' ? bookingDetails.gcashNumber : bookingDetails.mayaNumber"></strong></li>
                    <li>Take a screenshot of your payment confirmation</li>
                    <li>Upload the screenshot below</li>
                    <li>Click "Confirm Payment" to complete the process</li>
                </ol>
            </x-card>

            <form id="payment-submit-form" method="POST" action="{{ route('customer.payment.submit', $bookingId) }}" enctype="multipart/form-data">
                @csrf
                <x-card>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Upload Payment Screenshot</h2>
                    <div class="space-y-4">
                        <label for="payment_screenshot_input" class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 dark:border-white/15 rounded-xl cursor-pointer hover:border-[#E63946] transition-colors bg-gray-50 dark:bg-white/5">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <x-icon name="check-square" class="w-12 h-12 text-gray-400 mb-3" />
                                <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-bold">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500">PNG, JPG or JPEG (MAX. 5MB)</p>
                                <template x-if="uploadedFile">
                                    <div class="mt-4 flex items-center gap-2 text-green-600">
                                        <x-icon name="check-square" class="w-5 h-5 text-green-600" />
                                        <span class="font-bold" x-text="uploadedFile"></span>
                                    </div>
                                </template>
                            </div>
                            <input
                                id="payment_screenshot_input"
                                name="payment_screenshot"
                                type="file"
                                class="hidden"
                                accept="image/*"
                                @change="handleFileUpload"
                            />
                        </label>
                        <input type="hidden" name="payment_method" :value="selectedPayment">
                        <div class="mt-2">
                            <label class="text-sm text-gray-600 dark:text-gray-400">Reference Number (optional)</label>
                            <input type="text" name="reference_number" class="w-full mt-1 border rounded px-3 py-2" placeholder="GCash/Maya reference number" />
                        </div>
                    </div>
                </x-card>

                <div class="flex gap-4 mt-4">
                    <a href="{{ url('/customer/bookings') }}" class="flex-1">
                        <x-button variant="outline" class="w-full">
                            Cancel
                        </x-button>
                    </a>
                    <x-button
                        variant="accent"
                        size="lg"
                        type="submit"
                        class="flex-1 text-white"
                    >
                        Confirm Payment
                    </x-button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
