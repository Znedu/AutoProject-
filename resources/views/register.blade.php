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

                    {{-- Phone Number --}}
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Phone Number <span class="text-[#E63946]">*</span>
                        </label>
                        <input
                            id="phone"
                            type="tel"
                            name="phone"
                            value="{{ old('phone') }}"
                            placeholder="+63 912 345 6789"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#E63946] focus:ring-1 focus:ring-[#E63946] transition-all duration-300"
                        />
                        @error('phone')
                            <p class="text-[#E63946] text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Password <span class="text-[#E63946]">*</span>
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Create a strong password"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#E63946] focus:ring-1 focus:ring-[#E63946] transition-all duration-300"
                        />
                        @error('password')
                            <p class="text-[#E63946] text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Confirm Password --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Confirm Password <span class="text-[#E63946]">*</span>
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            placeholder="Re-enter your password"
                            required
                            class="w-full px-4 py-3 rounded-xl border border-gray-300 text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#E63946] focus:ring-1 focus:ring-[#E63946] transition-all duration-300"
                        />
                    </div>

                    {{-- Terms Checkbox --}}
                    <div class="flex items-start gap-2">
                        <input type="checkbox" name="terms" required class="mt-1 accent-[#E63946]" />
                        <label class="text-sm text-gray-600">
                            I agree to the Terms of Service and Privacy Policy
                        </label>
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
