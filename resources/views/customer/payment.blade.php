@extends('layouts.dashboard')

@section('title', 'Payment | AutoProject+')

@section('content')
<div 
    x-data="{
        selectedPayment: 'gcash',
        uploadedFile: null,
        bookingDetails: {
            id: '{{ $bookingId ?? '12345' }}',
            service: 'Engine Customization',
            vehicle: 'Honda Civic 2020',
            reservationFee: '₱5,000',
            totalEstimate: '₱75,000'
        },
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
    class="max-w-4xl mx-auto space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Payment</h1>
        <p class="text-gray-600 dark:text-gray-400">Complete your reservation payment to confirm booking.</p>
    </div>

    {{-- Booking Summary --}}
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

    {{-- Payment Method Selection --}}
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

    {{-- Payment Instructions --}}
    <x-card class="bg-[#457B9D]/10 border border-[#457B9D]/20">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Payment Instructions</h2>
        <ol class="space-y-3 list-decimal list-inside text-gray-700 dark:text-gray-300 text-sm sm:text-base">
            <li>Send ₱5,000 reservation fee to our <span class="font-bold capitalize text-[#E63946]" x-text="selectedPayment"></span> number: <strong>0912 345 6789</strong></li>
            <li>Take a screenshot of your payment confirmation</li>
            <li>Upload the screenshot below</li>
            <li>Click "Confirm Payment" to complete the process</li>
        </ol>
    </x-card>

    {{-- Upload Payment Screenshot --}}
    <x-card>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Upload Payment Screenshot</h2>
        <div class="space-y-4">
            <label class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 dark:border-white/15 rounded-xl cursor-pointer hover:border-[#E63946] transition-colors bg-gray-50 dark:bg-white/5">
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
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="handleFileUpload"
                />
            </label>
        </div>
    </x-card>

    {{-- Actions --}}
    <div class="flex gap-4">
        <a href="{{ url('/customer/bookings') }}" class="flex-1">
            <x-button variant="outline" class="w-full">
                Cancel
            </x-button>
        </a>
        <x-button
            variant="accent"
            size="lg"
            @click="handleConfirmPayment()"
            class="flex-1 text-white"
        >
            Confirm Payment
        </x-button>
    </div>
</div>
@endsection
