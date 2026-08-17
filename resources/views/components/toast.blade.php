<div
    x-data="{
        toasts: [],
        add(toast) {
            toast.id = Date.now() + Math.random();
            this.toasts.push(toast);
            setTimeout(() => {
                this.remove(toast.id);
            }, toast.duration || 5000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-on:toast.window="add($event.detail)"
    x-init="
        window.showToast = {
            success: (message) => $dispatch('toast', { type: 'success', message }),
            error: (message) => $dispatch('toast', { type: 'error', message }),
            info: (message) => $dispatch('toast', { type: 'info', message })
        };
        @if (session()->has('success'))
            add({ type: 'success', message: @js(session('success')) });
        @endif
        @if (session()->has('status'))
            add({ type: 'success', message: @js(session('status')) });
        @endif
        @if (session()->has('error'))
            add({ type: 'error', message: @js(session('error')) });
        @endif
        @if (session()->has('info'))
            add({ type: 'info', message: @js(session('info')) });
        @endif
    "
    class="fixed bottom-5 right-5 z-[200] flex flex-col gap-3 max-w-sm w-full pointer-events-none"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-3 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            :class="{
                'bg-[#065F46] border-emerald-400 shadow-emerald-950/80': toast.type === 'success',
                'bg-[#991B1B] border-red-400 shadow-red-950/80': toast.type === 'error',
                'bg-[#1E40AF] border-blue-400 shadow-blue-950/80': toast.type === 'info' || toast.type === 'default'
            }"
            class="pointer-events-auto flex items-center justify-between p-4 rounded-xl border-2 shadow-2xl text-white opacity-100"
        >
            <div class="flex items-center gap-3">
                <!-- Icon mapping -->
                <div class="shrink-0">
                    <template x-if="toast.type === 'success'">
                        <x-icon name="check-circle" class="w-5 h-5 text-emerald-300" />
                    </template>
                    <template x-if="toast.type === 'error'">
                        <x-icon name="close" class="w-5 h-5 text-red-300" />
                    </template>
                    <template x-if="toast.type === 'info' || toast.type === 'default'">
                        <x-icon name="info" class="w-5 h-5 text-blue-300" />
                    </template>
                </div>
                <p class="text-sm font-bold text-white tracking-wide" x-text="toast.message"></p>
            </div>
            <button x-on:click="remove(toast.id)" class="text-white/70 hover:text-white cursor-pointer ml-3 shrink-0 p-1 hover:bg-white/10 rounded-lg transition-colors">
                <x-icon name="close" class="w-4 h-4" />
            </button>
        </div>
    </template>
</div>
