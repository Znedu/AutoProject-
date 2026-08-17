<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Verify Email | AutoProject+</title>
    <meta name="description" content="Enter your 6-digit verification code to activate your AutoProject+ account.">

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

        <div class="relative z-10 w-full max-w-md" x-data="emailVerification({{ $cooldown }})">
            {{-- Logo --}}
            <div class="text-center mb-8">
                <h1 class="text-5xl font-bold text-white mb-2 tracking-wider">
                    AUTO<span class="text-[#E63946] text-glow">PROJECT</span>+
                </h1>
                <p class="text-[#B8B8B8] text-lg">Verify your email address to continue</p>
            </div>

            {{-- Card --}}
            <div class="glass-card p-8 rounded-2xl">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 bg-[#E63946]/10 text-[#E63946] border border-[#E63946]/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <x-icon name="mail" class="w-8 h-8" />
                    </div>
                    <h2 class="text-2xl font-bold text-white mb-2">Check Your Email</h2>
                    <p class="text-[#B8B8B8] text-sm">
                        We sent a 6-digit verification code to:<br>
                        <span class="text-white font-semibold">{{ $email }}</span>
                    </p>
                </div>

                {{-- Status / Error Alerts --}}
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-sm font-medium flex items-center gap-3 shadow-lg shadow-emerald-500/10">
                        <x-icon name="check-circle" class="w-5 h-5 text-emerald-400 shrink-0" />
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-[#E63946]/20 border border-[#E63946]/40 text-red-200 text-sm font-medium flex items-center gap-3 shadow-lg shadow-[#E63946]/10">
                        <x-icon name="info" class="w-5 h-5 text-[#E63946] shrink-0" />
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if (session('info'))
                    <div class="mb-6 p-4 rounded-xl bg-blue-500/20 border border-blue-500/40 text-blue-200 text-sm font-medium flex items-center gap-3 shadow-lg shadow-blue-500/10">
                        <x-icon name="info" class="w-5 h-5 text-blue-400 shrink-0" />
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                {{-- Verification Form --}}
                <form method="POST" action="{{ route('verification.verify') }}" class="space-y-6" @submit="submitCode">
                    @csrf

                    <input type="hidden" name="code" :value="digits.join('')">

                    {{-- 6-Digit OTP Inputs --}}
                    <div>
                        <label class="block text-sm font-medium text-white mb-3 text-center">
                            Enter 6-Digit Verification Code <span class="text-[#E63946]">*</span>
                        </label>

                        <div class="flex justify-between items-center gap-2">
                            <template x-for="(digit, index) in digits" :key="index">
                                <input
                                    :id="'otp-input-' + index"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="1"
                                    x-model="digits[index]"
                                    @input="handleInput(index, $event)"
                                    @keydown.backspace="handleBackspace(index, $event)"
                                    @paste="handlePaste($event)"
                                    class="w-12 h-14 text-center text-xl font-bold rounded-xl bg-white/5 border border-white/10 text-white focus:outline-none focus:border-[#E63946] focus:ring-1 focus:ring-[#E63946] transition-all duration-300"
                                    required
                                />
                            </template>
                        </div>

                        @error('code')
                            <p class="text-[#E63946] text-sm mt-2 text-center">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <button
                        type="submit"
                        :disabled="digits.join('').length !== 6"
                        :class="digits.join('').length === 6 ? 'bg-[#E63946] hover:bg-[#E63946]/90 opacity-100 cursor-pointer' : 'bg-white/10 opacity-50 cursor-not-allowed'"
                        class="w-full group px-6 py-3.5 text-white font-semibold rounded-xl transition-all duration-300 shadow-lg flex items-center justify-center gap-2"
                    >
                        <x-icon name="check-circle" class="w-5 h-5" />
                        Verify Email Address
                    </button>
                </form>

                {{-- Resend & Change Email Section --}}
                <div class="mt-6 pt-6 border-t border-white/10 text-center space-y-4">
                    <div>
                        <p class="text-[#B8B8B8] text-sm mb-2">Didn't receive the code?</p>

                        <form method="POST" action="{{ route('verification.resend') }}">
                            @csrf
                            <button
                                type="submit"
                                :disabled="cooldown > 0"
                                :class="cooldown > 0 ? 'text-[#666666] cursor-not-allowed' : 'text-[#E63946] hover:underline cursor-pointer'"
                                class="font-semibold text-sm transition-colors"
                            >
                                <span x-show="cooldown > 0">Resend code in <span x-text="cooldown"></span>s</span>
                                <span x-show="cooldown <= 0">Resend Verification Code</span>
                            </button>
                        </form>
                    </div>

                    <div class="pt-3 border-t border-white/5">
                        <p class="text-[#B8B8B8] text-xs">
                            Entered the wrong email address?
                            <a href="{{ url('/register') }}" class="text-[#E63946] font-semibold hover:underline transition-colors ml-1 inline-flex items-center gap-1">
                                <x-icon name="user-plus" class="w-3.5 h-3.5" />
                                Re-register / Change Email
                            </a>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Back to Home --}}
            <div class="mt-6 text-center">
                <a href="{{ route('verification.cancel') }}" class="text-white hover:text-[#E63946] font-medium transition-colors inline-flex items-center gap-2 text-sm px-4 py-2 rounded-xl hover:bg-white/5">
                    ← Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('emailVerification', (initialCooldown) => ({
                digits: ['', '', '', '', '', ''],
                cooldown: initialCooldown,
                timer: null,

                init() {
                    if (this.cooldown > 0) {
                        this.startTimer();
                    }
                    this.$nextTick(() => {
                        const firstInput = document.getElementById('otp-input-0');
                        if (firstInput) firstInput.focus();
                    });
                },

                startTimer() {
                    this.timer = setInterval(() => {
                        if (this.cooldown > 0) {
                            this.cooldown--;
                        } else {
                            clearInterval(this.timer);
                        }
                    }, 1000);
                },

                handleInput(index, event) {
                    const value = event.target.value.replace(/[^0-9]/g, '');
                    this.digits[index] = value;

                    if (value && index < 5) {
                        const nextInput = document.getElementById(`otp-input-${index + 1}`);
                        if (nextInput) nextInput.focus();
                    }
                },

                handleBackspace(index, event) {
                    if (!this.digits[index] && index > 0) {
                        const prevInput = document.getElementById(`otp-input-${index - 1}`);
                        if (prevInput) {
                            prevInput.focus();
                            this.digits[index - 1] = '';
                        }
                    }
                },

                handlePaste(event) {
                    event.preventDefault();
                    const pasteData = (event.clipboardData || window.clipboardData).getData('text').trim().replace(/[^0-9]/g, '');
                    if (pasteData.length > 0) {
                        const chars = pasteData.slice(0, 6).split('');
                        chars.forEach((char, i) => {
                            if (i < 6) this.digits[i] = char;
                        });
                        const lastIndex = Math.min(chars.length - 1, 5);
                        const targetInput = document.getElementById(`otp-input-${lastIndex}`);
                        if (targetInput) targetInput.focus();
                    }
                },

                submitCode(event) {
                    if (this.digits.join('').length !== 6) {
                        event.preventDefault();
                    }
                }
            }));
        });
    </script>

    <!-- Toast Notifications -->
    <x-toast />

</body>
</html>
