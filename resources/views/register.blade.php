<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Register | AutoProject+</title>
    <meta name="description" content="Create your AutoProject+ account to book automotive services, track progress, and more.">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen">

    <div class="min-h-screen bg-gradient-to-br from-[#1F2937] via-[#374151] to-[#1F2937] flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-white mb-2">AutoProject+</h1>
                <p class="text-gray-300">Create your account and get started.</p>
            </div>

            {{-- Register Card --}}
            <div class="bg-white rounded-lg shadow-xl p-8">
                <h2 class="text-2xl font-bold text-[#1F2937] mb-6">Create Account</h2>

                <form method="POST" action="{{ url('/register') }}" class="space-y-4">
                    @csrf

                    {{-- Full Name --}}
                    <div>
                        <label for="fullName" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Full Name <span class="text-[#E63946]">*</span>
                        </label>
                        <input
                            id="fullName"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="John Doe"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#E63946] focus:ring-1 focus:ring-[#E63946] transition-all duration-300"
                        />
                        @error('name')
                            <p class="text-[#E63946] text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email Address --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Email Address <span class="text-[#E63946]">*</span>
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="your@email.com"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#E63946] focus:ring-1 focus:ring-[#E63946] transition-all duration-300"
                        />
                        @error('email')
                            <p class="text-[#E63946] text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div x-data="{ 
                        showPassword: false, 
                        password: '',
                        get hasMinLength() { return this.password.length >= 8; },
                        get hasUppercase() { return /[A-Z]/.test(this.password); },
                        get hasLowercase() { return /[a-z]/.test(this.password); },
                        get hasNumber() { return /[0-9]/.test(this.password); },
                        get hasSpecial() { return /[^A-Za-z0-9]/.test(this.password); },
                        get allValid() { return this.hasMinLength && this.hasUppercase && this.hasLowercase && this.hasNumber && this.hasSpecial; }
                    }">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Password <span class="text-[#E63946]">*</span>
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                x-model="password"
                                placeholder="Create a strong password"
                                required
                                minlength="8"
                                class="w-full pl-4 pr-12 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#E63946] focus:ring-1 focus:ring-[#E63946] transition-all duration-300"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none cursor-pointer p-1"
                                :title="showPassword ? 'Hide password' : 'Show password'"
                            >
                                <template x-if="showPassword">
                                    <x-icon name="eye-off" class="w-5 h-5 text-[#E63946]" />
                                </template>
                                <template x-if="!showPassword">
                                    <x-icon name="eye" class="w-5 h-5" />
                                </template>
                            </button>
                        </div>

                        {{-- Real-time Password Requirements Checklist --}}
                        <div class="mt-2.5 p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-1.5">
                            <p class="font-medium text-gray-700 mb-1">Password must contain:</p>
                            
                            <div class="flex items-center gap-2 transition-colors duration-200" :class="hasMinLength ? 'text-emerald-600 font-medium' : 'text-gray-500'">
                                <span class="w-4 h-4 flex items-center justify-center rounded-full text-[11px] font-bold transition-all duration-200" :class="hasMinLength ? 'bg-emerald-100 text-emerald-600 ring-1 ring-emerald-400/30' : 'bg-gray-200 text-gray-400'">
                                    <template x-if="hasMinLength">&#x2713;</template>
                                    <template x-if="!hasMinLength">&bull;</template>
                                </span>
                                <span>Minimum 8 characters</span>
                            </div>

                            <div class="flex items-center gap-2 transition-colors duration-200" :class="hasUppercase ? 'text-emerald-600 font-medium' : 'text-gray-500'">
                                <span class="w-4 h-4 flex items-center justify-center rounded-full text-[11px] font-bold transition-all duration-200" :class="hasUppercase ? 'bg-emerald-100 text-emerald-600 ring-1 ring-emerald-400/30' : 'bg-gray-200 text-gray-400'">
                                    <template x-if="hasUppercase">&#x2713;</template>
                                    <template x-if="!hasUppercase">&bull;</template>
                                </span>
                                <span>At least 1 uppercase letter</span>
                            </div>

                            <div class="flex items-center gap-2 transition-colors duration-200" :class="hasLowercase ? 'text-emerald-600 font-medium' : 'text-gray-500'">
                                <span class="w-4 h-4 flex items-center justify-center rounded-full text-[11px] font-bold transition-all duration-200" :class="hasLowercase ? 'bg-emerald-100 text-emerald-600 ring-1 ring-emerald-400/30' : 'bg-gray-200 text-gray-400'">
                                    <template x-if="hasLowercase">&#x2713;</template>
                                    <template x-if="!hasLowercase">&bull;</template>
                                </span>
                                <span>At least 1 lowercase letter</span>
                            </div>

                            <div class="flex items-center gap-2 transition-colors duration-200" :class="hasNumber ? 'text-emerald-600 font-medium' : 'text-gray-500'">
                                <span class="w-4 h-4 flex items-center justify-center rounded-full text-[11px] font-bold transition-all duration-200" :class="hasNumber ? 'bg-emerald-100 text-emerald-600 ring-1 ring-emerald-400/30' : 'bg-gray-200 text-gray-400'">
                                    <template x-if="hasNumber">&#x2713;</template>
                                    <template x-if="!hasNumber">&bull;</template>
                                </span>
                                <span>At least 1 number</span>
                            </div>

                            <div class="flex items-center gap-2 transition-colors duration-200" :class="hasSpecial ? 'text-emerald-600 font-medium' : 'text-gray-500'">
                                <span class="w-4 h-4 flex items-center justify-center rounded-full text-[11px] font-bold transition-all duration-200" :class="hasSpecial ? 'bg-emerald-100 text-emerald-600 ring-1 ring-emerald-400/30' : 'bg-gray-200 text-gray-400'">
                                    <template x-if="hasSpecial">&#x2713;</template>
                                    <template x-if="!hasSpecial">&bull;</template>
                                </span>
                                <span>At least 1 special character</span>
                            </div>
                        </div>

                        @error('password')
                            <p class="text-[#E63946] text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div x-data="{ showConfirmPassword: false }">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Confirm Password <span class="text-[#E63946]">*</span>
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                :type="showConfirmPassword ? 'text' : 'password'"
                                name="password_confirmation"
                                placeholder="Re-enter your password"
                                required
                                class="w-full pl-4 pr-12 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#E63946] focus:ring-1 focus:ring-[#E63946] transition-all duration-300"
                            />
                            <button
                                type="button"
                                @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none cursor-pointer p-1"
                                :title="showConfirmPassword ? 'Hide password' : 'Show password'"
                            >
                                <template x-if="showConfirmPassword">
                                    <x-icon name="eye-off" class="w-5 h-5 text-[#E63946]" />
                                </template>
                                <template x-if="!showConfirmPassword">
                                    <x-icon name="eye" class="w-5 h-5" />
                                </template>
                            </button>
                        </div>
                    </div>

                    {{-- Terms Checkbox & Modal Trigger --}}
                    <div class="flex items-start gap-2 pt-1" x-data="{ showTermsModal: false, agreedTerms: false }">
                        <input 
                            type="checkbox" 
                            id="terms" 
                            name="terms" 
                            required 
                            x-model="agreedTerms"
                            class="mt-1 accent-[#E63946] w-4 h-4 rounded cursor-pointer" 
                        />
                        <label for="terms" class="text-sm text-gray-600">
                            I agree to the 
                            <button 
                                type="button" 
                                @click="showTermsModal = true" 
                                class="text-[#E63946] font-semibold hover:underline cursor-pointer focus:outline-none"
                            >
                                Terms of Service
                            </button> 
                            and 
                            <button 
                                type="button" 
                                @click="showTermsModal = true" 
                                class="text-[#E63946] font-semibold hover:underline cursor-pointer focus:outline-none"
                            >
                                Privacy Policy
                            </button>
                        </label>

                        <!-- READABLE TERMS OF SERVICE & PRIVACY POLICY MODAL -->
                        <div x-show="showTermsModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm text-left">
                            <div @click.away="showTermsModal = false" class="w-full max-w-2xl bg-white dark:bg-[#121212] text-gray-900 dark:text-white rounded-2xl shadow-2xl border border-gray-200 dark:border-white/10 overflow-hidden flex flex-col max-h-[85vh]">
                                
                                <!-- Modal Header -->
                                <div class="p-5 border-b border-gray-200 dark:border-white/10 flex items-center justify-between bg-gray-50 dark:bg-white/5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="p-2 rounded-xl bg-[#E63946]/10 text-[#E63946]">
                                            <x-icon name="file-text" class="w-5 h-5" />
                                        </span>
                                        <div>
                                            <h3 class="text-lg font-bold">Terms of Service & Privacy Policy</h3>
                                            <p class="text-xs text-gray-500 dark:text-white/60">AutoProject-D Custom Garage</p>
                                        </div>
                                    </div>
                                    <button type="button" @click="showTermsModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-white p-1">
                                        <x-icon name="x" class="w-5 h-5" />
                                    </button>
                                </div>

                                <!-- Modal Readable Document Content -->
                                <div class="p-6 overflow-y-auto space-y-6 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                    
                                    <!-- Terms of Service -->
                                    <div class="space-y-3">
                                        <h4 class="text-base font-bold text-[#E63946] flex items-center gap-2">
                                            <x-icon name="shield" class="w-4 h-4" />
                                            1. Terms of Service
                                        </h4>

                                        <div class="space-y-2 text-xs sm:text-sm">
                                            <p class="font-semibold text-gray-900 dark:text-white">1.1 Account Registration & Security</p>
                                            <p class="text-gray-600 dark:text-white/70">
                                                By creating an account on AutoProject+, you agree to provide accurate and complete information. You are responsible for maintaining the confidentiality of your account credentials.
                                            </p>

                                            <p class="font-semibold text-gray-900 dark:text-white mt-3">1.2 Service Bookings & Appointments</p>
                                            <p class="text-gray-600 dark:text-white/70">
                                                Service appointments scheduled online are subject to shop confirmation. AutoProject-D Custom Garage reserves the right to adjust schedule slots based on workshop capacity and parts availability.
                                            </p>

                                            <p class="font-semibold text-gray-900 dark:text-white mt-3">1.3 Cost Estimations & Final Invoicing</p>
                                            <p class="text-gray-600 dark:text-white/70">
                                                Online cost estimations are preliminary figures based on standard package rates. Final billing will reflect verified physical vehicle inspection, actual parts used, and approved custom modifications.
                                            </p>

                                            <p class="font-semibold text-gray-900 dark:text-white mt-3">1.4 Vehicle Care & Storage</p>
                                            <p class="text-gray-600 dark:text-white/70">
                                                Vehicles left for service are stored securely in workshop facilities. Customers are advised to remove personal valuables prior to surrendering vehicles.
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Privacy Policy -->
                                    <div class="space-y-3 pt-4 border-t border-gray-200 dark:border-white/10">
                                        <h4 class="text-base font-bold text-[#E63946] flex items-center gap-2">
                                            <x-icon name="lock" class="w-4 h-4" />
                                            2. Privacy Policy
                                        </h4>

                                        <div class="space-y-2 text-xs sm:text-sm">
                                            <p class="font-semibold text-gray-900 dark:text-white">2.1 Information We Collect</p>
                                            <p class="text-gray-600 dark:text-white/70">
                                                We collect customer details (Name, Email Address, Contact Info) and vehicle information (Make, Model, Year, Plate Number) necessary to fulfill garage service bookings and invoices.
                                            </p>

                                            <p class="font-semibold text-gray-900 dark:text-white mt-3">2.2 Data Protection</p>
                                            <p class="text-gray-600 dark:text-white/70">
                                                Your personal and vehicle information is encrypted and securely stored. We do not sell or share customer data with unauthorized third parties.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-xl bg-gray-100 dark:bg-white/5 text-xs text-gray-600 dark:text-white/70 flex justify-between items-center">
                                        <span>Want to view full page document?</span>
                                        <a href="{{ route('terms') }}" target="_blank" class="text-[#E63946] font-semibold hover:underline flex items-center gap-1">
                                            Open Full Page Terms
                                            <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                                        </a>
                                    </div>

                                </div>

                                <!-- Modal Footer Action -->
                                <div class="p-4 border-t border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 flex flex-col sm:flex-row items-center justify-between gap-3">
                                    <span class="text-xs text-gray-500 dark:text-white/60">
                                        Clicking agree will automatically check the acceptance box.
                                    </span>
                                    <div class="flex items-center gap-2 w-full sm:w-auto">
                                        <button 
                                            type="button" 
                                            @click="showTermsModal = false" 
                                            class="w-full sm:w-auto px-4 py-2 rounded-xl border border-gray-300 dark:border-white/10 text-gray-700 dark:text-white/80 text-xs font-semibold hover:bg-gray-100 dark:hover:bg-white/10 transition-colors"
                                        >
                                            Close
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="agreedTerms = true; showTermsModal = false" 
                                            class="w-full sm:w-auto px-5 py-2 rounded-xl bg-[#E63946] hover:bg-[#E63946]/90 text-white text-xs font-semibold transition-all shadow-md flex items-center justify-center gap-1.5 cursor-pointer"
                                        >
                                            <x-icon name="check-circle" class="w-4 h-4" />
                                            I Have Read & Agree
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        class="w-full px-6 py-3.5 bg-[#E63946] hover:bg-[#E63946]/90 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-[#E63946]/20 hover:shadow-[#E63946]/40 cursor-pointer"
                    >
                        Register
                    </button>
                </form>

                {{-- Login Link --}}
                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Already have an account?
                        <a href="{{ url('/login') }}" class="text-[#E63946] hover:underline font-medium">
                            Login here
                        </a>
                    </p>
                </div>
            </div>

            {{-- Back to Home --}}
            <div class="text-center mt-6">
                <a href="{{ url('/') }}" class="text-white hover:text-gray-300 transition-colors">
                    ← Back to Home
                </a>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

</body>
</html>
