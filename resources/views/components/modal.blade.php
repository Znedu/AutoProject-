@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
    'title' => null,
])

@php
$maxWidthClass = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    '3xl' => 'sm:max-w-3xl',
    '4xl' => 'sm:max-w-4xl',
    '5xl' => 'sm:max-w-5xl',
    '6xl' => 'sm:max-w-6xl',
    '7xl' => 'sm:max-w-7xl',
][$maxWidth] ?? 'sm:max-w-lg';
@endphp

<div
    x-data="{
        show: @js($show),
        focusables() {
            let selector = 'a, button, input, textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                .filter(el => !el.hasAttribute('disabled') && el.offsetParent !== null)
        },
        getFirstFocusable() { return this.focusables()[0] },
        getLastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocus() {
            let focusables = this.focusables()
            let index = focusables.indexOf(document.activeElement)
            let nextIndex = (index + 1) % focusables.length
            focusables[nextIndex].focus()
        },
        prevFocus() {
            let focusables = this.focusables()
            let index = focusables.indexOf(document.activeElement)
            let prevIndex = (index - 1 + focusables.length) % focusables.length
            focusables[prevIndex].focus()
        }
    }"
    x-init="
        $watch('show', value => {
            if (value) {
                document.body.classList.add('overflow-y-hidden');
                {{ $attributes->has('focusable') ? 'setTimeout(() => getFirstFocusable().focus(), 100)' : '' }}
            } else {
                document.body.classList.remove('overflow-y-hidden');
            }
        });
    "
    x-on:open-modal.window="if ($event.detail.name === '{{ $name }}') show = true"
    x-on:close-modal.window="if ($event.detail.name === '{{ $name }}') show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey ? prevFocus() : nextFocus()"
    x-show="show"
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
    style="display: none;"
>
    <!-- Overlay -->
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
    </div>

    <!-- Modal Content -->
    <div
        x-show="show"
        class="bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10 rounded-xl overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidthClass }} mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $title ?? $header ?? '' }}
            </h3>
            <button x-on:click="show = false" class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 cursor-pointer">
                <x-icon name="close" class="h-5 w-5" />
            </button>
        </div>

        <!-- Modal Body -->
        <div class="px-6 py-4 text-gray-700 dark:text-gray-300">
            {{ $slot }}
        </div>

        @if(isset($footer))
            <!-- Modal Footer -->
            <div class="px-6 py-4 bg-gray-50 dark:bg-black/20 border-t border-gray-200 dark:border-white/10 flex justify-end gap-3">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
