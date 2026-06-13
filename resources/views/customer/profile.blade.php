@extends('layouts.dashboard')

@section('title', 'My Profile | AutoProject+')

@section('content')
<div 
    x-data="{
        isEditing: false,
        profileData: @js($profileData),
        handleSubmit() {
            fetch('/customer/profile', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(this.profileData)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    this.isEditing = false;
                    showToast.success(data.message);
                } else {
                    showToast.error(data.error || 'Failed to update profile.');
                }
            })
            .catch(err => {
                showToast.error('An error occurred.');
            });
        }
    }"
    class="max-w-4xl mx-auto space-y-6 animate-fade-in"
>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold mb-2 text-gray-900 dark:text-white">My Profile</h1>
            <p class="text-gray-600 dark:text-gray-400">Manage your account information.</p>
        </div>
        <template x-if="!isEditing">
            <x-button variant="secondary" @click="isEditing = true">
                <x-icon name="user" class="w-5 h-5 mr-2 inline-block" />
                Edit Profile
            </x-button>
        </template>
    </div>

    {{-- Profile Card --}}
    <x-card>
        <div class="flex flex-col items-center mb-8">
            <div class="w-24 h-24 rounded-full bg-gradient-to-br from-[#E63946] to-[#D62839] flex items-center justify-center text-white text-3xl font-bold mb-4 shadow-lg">
                @php
                    $initials = '';
                    $words = explode(' ', auth()->user()->name);
                    foreach ($words as $w) {
                        $initials .= strtoupper(substr($w, 0, 1));
                    }
                    $initials = substr($initials, 0, 2);
                @endphp
                {{ $initials }}
            </div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="profileData.fullName"></h2>
            <p class="text-gray-500 dark:text-gray-400 uppercase tracking-wider text-xs font-semibold mt-1">Customer</p>
        </div>

        <form @submit.prevent="handleSubmit()" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Name --}}
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300">
                        <x-icon name="user" class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <template x-if="isEditing">
                            <x-input
                                label="Full Name"
                                x-model="profileData.fullName"
                                required
                            />
                        </template>
                        <template x-if="!isEditing">
                            <div>
                                <p class="text-sm mb-1 text-gray-500 dark:text-gray-400">Full Name</p>
                                <p class="font-bold text-gray-900 dark:text-white text-base" x-text="profileData.fullName"></p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Email --}}
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300">
                        <x-icon name="message-square" class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <template x-if="isEditing">
                            <x-input
                                label="Email Address"
                                type="email"
                                x-model="profileData.email"
                                required
                            />
                        </template>
                        <template x-if="!isEditing">
                            <div>
                                <p class="text-sm mb-1 text-gray-500 dark:text-gray-400">Email Address</p>
                                <p class="font-bold text-gray-900 dark:text-white text-base" x-text="profileData.email"></p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Phone --}}
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300">
                        <x-icon name="message-square" class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <template x-if="isEditing">
                            <x-input
                                label="Phone Number"
                                type="tel"
                                x-model="profileData.phone"
                                required
                            />
                        </template>
                        <template x-if="!isEditing">
                            <div>
                                <p class="text-sm mb-1 text-gray-500 dark:text-gray-400">Phone Number</p>
                                <p class="font-bold text-gray-900 dark:text-white text-base" x-text="profileData.phone"></p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Address --}}
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300">
                        <x-icon name="map-pin" class="w-6 h-6" />
                    </div>
                    <div class="flex-1">
                        <template x-if="isEditing">
                            <x-input
                                label="Address"
                                x-model="profileData.address"
                                required
                            />
                        </template>
                        <template x-if="!isEditing">
                            <div>
                                <p class="text-sm mb-1 text-gray-500 dark:text-gray-400">Address</p>
                                <p class="font-bold text-gray-900 dark:text-white text-base" x-text="profileData.address"></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <template x-if="isEditing">
                <div class="flex gap-3 justify-end pt-4 border-t border-gray-200 dark:border-white/10">
                    <x-button
                        type="button"
                        variant="outline"
                        @click="isEditing = false"
                    >
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="accent" class="text-white bg-green-600 border-green-600 hover:bg-green-700">
                        Save Changes
                    </x-button>
                </div>
            </template>
        </form>
    </x-card>

    {{-- Account Statistics --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-card class="text-center">
            <p class="mb-2 text-sm text-green-500 font-bold uppercase tracking-wider">Total Bookings</p>
            <p class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ $totalBookings }}</p>
        </x-card>
        <x-card class="text-center">
            <p class="mb-2 text-sm text-[#E63946] font-bold uppercase tracking-wider">Completed Services</p>
            <p class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ $completedServices }}</p>
        </x-card>
        <x-card class="text-center">
            <p class="mb-2 text-sm text-[#457B9D] font-bold uppercase tracking-wider">Member Since</p>
            <p class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ $memberSince }}</p>
        </x-card>
    </div>
</div>
@endsection
