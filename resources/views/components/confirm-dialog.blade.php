<div
    x-data="{
        open: false,
        title: 'Confirm Action',
        message: 'Are you sure you want to proceed?',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        variant: 'danger',
        onConfirm: null,
        
        show(data) {
            this.title = data.title || 'Confirm Action';
            this.message = data.message || 'Are you sure you want to proceed?';
            this.confirmText = data.confirmText || 'Confirm';
            this.cancelText = data.cancelText || 'Cancel';
            this.variant = data.variant || 'danger';
            this.onConfirm = data.onConfirm || null;
            this.open = true;
        },
        confirm() {
            this.open = false;
            if (typeof this.onConfirm === 'function') {
                this.onConfirm();
            }
        }
    }"
    x-on:confirm-dialog.window="show($event.detail)"
    x-init="
        window.showConfirm = (options) => $dispatch('confirm-dialog', options);
    "
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center p-4"
    style="display: none;"
    @keydown.escape.window="open = false"
>
    {{-- Glassmorphism Backdrop --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 bg-black/70 backdrop-blur-md"
    ></div>

    {{-- Dialog Box Panel --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        class="relative w-full max-w-md bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl overflow-hidden p-6 z-10 space-y-5"
    >
        <div class="flex items-start gap-4">
            <div
                class="p-3.5 rounded-2xl shrink-0 border"
                :class="{
                    'bg-red-500/10 text-red-500 border-red-500/20': variant === 'danger',
                    'bg-amber-500/10 text-amber-500 border-amber-500/20': variant === 'warning',
                    'bg-blue-500/10 text-blue-500 border-blue-500/20': variant === 'primary',
                    'bg-green-500/10 text-green-500 border-green-500/20': variant === 'success'
                }"
            >
                <template x-if="variant === 'danger'">
                    <x-icon name="x" class="w-6 h-6 text-red-500" />
                </template>
                <template x-if="variant === 'warning'">
                    <x-icon name="info" class="w-6 h-6 text-amber-500" />
                </template>
                <template x-if="variant === 'primary'">
                    <x-icon name="info" class="w-6 h-6 text-blue-500" />
                </template>
                <template x-if="variant === 'success'">
                    <x-icon name="check-square" class="w-6 h-6 text-green-500" />
                </template>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white" x-text="title"></h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 leading-relaxed" x-text="message"></p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-200 dark:border-white/10">
            <button
                type="button"
                @click="open = false"
                class="px-4 py-2.5 rounded-xl border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 font-semibold text-sm hover:bg-gray-100 dark:hover:bg-white/5 transition-all cursor-pointer"
                x-text="cancelText"
            ></button>
            <button
                type="button"
                @click="confirm()"
                class="px-5 py-2.5 rounded-xl font-bold text-sm text-white shadow-lg transition-all cursor-pointer"
                :class="{
                    'bg-red-600 hover:bg-red-700 hover:shadow-red-600/30': variant === 'danger',
                    'bg-amber-600 hover:bg-amber-700 hover:shadow-amber-600/30': variant === 'warning',
                    'bg-[#457B9D] hover:bg-[#3b6987] hover:shadow-[#457B9D]/30': variant === 'primary',
                    'bg-green-600 hover:bg-green-700 hover:shadow-green-600/30': variant === 'success'
                }"
                x-text="confirmText"
            ></button>
        </div>
    </div>
</div>
