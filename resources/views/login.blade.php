<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login | AutoProject+</title>
    <meta name="description" content="Login to your AutoProject+ account to manage bookings, track services, and more.">

    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen">

    <div
        class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden"
        style="background-image: url('https://images.unsplash.com/photo-1768387666438-b3da75373846?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&q=80&w=1920'); background-size: cover; background-position: center; background-attachment: fixed;"
    >
        {{-- Dark Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-br from-black/90 via-black/85 to-black/90"></div>

        <div class="relative z-10 w-full max-w-md">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <h1 class="text-5xl font-bold text-white mb-2 tracking-wider">
                    AUTO<span class="text-[#E63946] text-glow">PROJECT</span>+
                </h1>
                <p class="text-[#B8B8B8] text-lg">Welcome back! Please login to continue.</p>
            </div>

            {{-- Login Card --}}
            <div class="glass-card p-8 rounded-2xl">
                <h2 class="text-2xl font-bold text-white mb-6">Login to Your Account</h2>

                {{-- Login Form --}}
                <form method="POST" action="{{ url('/login') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-white mb-1.5">
                            Email Address <span class="text-[#E63946]">*</span>
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Enter your email"
                            required
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-[#666666] focus:outline-none focus:border-[#E63946] focus:ring-1 focus:ring-[#E63946] transition-all duration-300"
                        />
                        @error('email')
                            <p class="text-[#E63946] text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-white mb-1.5">
                            Password <span class="text-[#E63946]">*</span>
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            placeholder="Enter your password"
                            required
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-[#666666] focus:outline-none focus:border-[#E63946] focus:ring-1 focus:ring-[#E63946] transition-all duration-300"
                        />
                        @error('password')
                            <p class="text-[#E63946] text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        class="w-full group px-6 py-3.5 bg-[#E63946] hover:bg-[#E63946]/90 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-[#E63946]/20 hover:shadow-[#E63946]/40 flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <x-icon name="log-in" class="w-5 h-5" />
                        Login to Dashboard
                        <x-icon name="arrow-right" class="w-4.5 h-4.5 group-hover:translate-x-1 transition-transform" />
                    </button>
                </form>

                {{-- Register Link --}}
                <div class="mt-6 text-center">
                    <p class="text-[#B8B8B8]">
                        Don't have an account?
                        <a href="{{ url('/register') }}" class="text-[#E63946] font-semibold hover:underline transition-colors">
                            Create Account
                        </a>
                    </p>
                </div>

            {{-- Back to Home --}}
            <div class="mt-6 text-center">
                <a href="{{ url('/') }}" class="text-[#B8B8B8] hover:text-white transition-colors inline-flex items-center gap-2">
                    ← Back to Home
                </a>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

</body>
</html>
