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
        @if (session()->has('error'))
            add({ type: 'error', message: @js(session('error')) });
        @endif
        @if (session()->has('info'))
            add({ type: 'info', message: @js(session('info')) });
        @endif
    "
    class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm w-full"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            :class="{
                'bg-green-500/10 border-green-500/20 text-green-500': toast.type === 'success',
                'bg-red-500/10 border-red-500/20 text-red-500': toast.type === 'error',
                'bg-blue-500/10 border-blue-500/20 text-blue-500': toast.type === 'info' || toast.type === 'default'
            }"
            class="flex items-center justify-between p-4 rounded-xl border backdrop-blur-md shadow-lg"
        >
            <div class="flex items-center gap-3">
                <!-- Icon mapping -->
                <div class="shrink-0">
                    <template x-if="toast.type === 'success'">
                        <x-icon name="check-circle" class="w-5 h-5 text-green-500" />
                    </template>
                    <template x-if="toast.type === 'error'">
                        <x-icon name="close" class="w-5 h-5 text-red-500" />
                    </template>
                    <template x-if="toast.type === 'info' || toast.type === 'default'">
                        <x-icon name="info" class="w-5 h-5 text-blue-500" />
                    </template>
                </div>
                <p class="text-sm font-semibold" x-text="toast.message"></p>
            </div>
            <button x-on:click="remove(toast.id)" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer ml-3 shrink-0">
                <x-icon name="close" class="w-4 h-4" />
            </button>
        </div>
    </template>
</div>
