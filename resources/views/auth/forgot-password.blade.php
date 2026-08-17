<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Forgot Password | AutoProject+</title>
    <meta name="description" content="Reset your AutoProject+ password by requesting a password reset link via email.">

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
                <p class="text-[#B8B8B8] text-lg">Reset your account password</p>
            </div>

            {{-- Card --}}
            <div class="glass-card p-8 rounded-2xl">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-[#E63946]/10 text-[#E63946] border border-[#E63946]/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <x-icon name="key-round" class="w-8 h-8" />
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">Forgot Password?</h2>
                    <p class="text-[#B8B8B8] text-sm">
                        Enter your registered email address and we'll send you a link to reset your password.
                    </p>
                </div>

                {{-- Status / Error Alerts --}}
                @if (session('status'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-500/30 border border-emerald-400/50 text-white text-sm font-semibold flex items-center gap-3 shadow-lg shadow-emerald-500/20">
                        <x-icon name="check-circle" class="w-5 h-5 text-emerald-400 shrink-0" />
                        <span class="text-white font-semibold">{{ session('status') }}</span>
                    </div>
                @elseif (session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-500/30 border border-emerald-400/50 text-white text-sm font-semibold flex items-center gap-3 shadow-lg shadow-emerald-500/20">
                        <x-icon name="check-circle" class="w-5 h-5 text-emerald-400 shrink-0" />
                        <span class="text-white font-semibold">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-[#E63946]/30 border border-[#E63946]/60 text-white text-sm font-semibold flex items-center gap-3 shadow-lg shadow-[#E63946]/20">
                        <x-icon name="info" class="w-5 h-5 text-[#E63946] shrink-0" />
                        <span class="text-white font-semibold">{{ session('error') }}</span>
                    </div>
                @endif

                {{-- Forgot Password Form --}}
                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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
                            placeholder="Enter your registered email"
                            required
                            autofocus
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-[#666666] focus:outline-none focus:border-[#E63946] focus:ring-1 focus:ring-[#E63946] transition-all duration-300"
                        />
                        @error('email')
                            <p class="text-[#E63946] text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        class="w-full group px-6 py-3.5 bg-[#E63946] hover:bg-[#E63946]/90 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg shadow-[#E63946]/20 hover:shadow-[#E63946]/40 flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <x-icon name="mail" class="w-5 h-5" />
                        Send Password Reset Link
                    </button>
                </form>

                {{-- Back to Login Link --}}
                <div class="mt-6 pt-6 border-t border-white/10 text-center">
                    <a href="{{ route('login') }}" class="text-[#B8B8B8] hover:text-white font-medium transition-colors inline-flex items-center gap-2 text-sm">
                        ← Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <x-toast />

</body>
</html>
