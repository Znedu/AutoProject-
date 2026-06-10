@props([
    'tabs' => [],
    'default' => null,
])

@php
    $defaultTab = $default ?? (array_key_first($tabs) ?? '');
@endphp

<div x-data="{ activeTab: '{{ $defaultTab }}' }" {{ $attributes->merge(['class' => 'flex flex-col gap-4']) }} data-slot="tabs">
    <!-- Tabs Header List -->
    <div data-slot="tabs-list" class="bg-gray-100 dark:bg-zinc-900/50 text-gray-500 dark:text-muted-foreground inline-flex h-11 w-fit items-center justify-center rounded-xl p-[3px] border border-gray-200 dark:border-white/5">
        @foreach($tabs as $key => $label)
            <button
                type="button"
                data-slot="tabs-trigger"
                x-on:click="activeTab = '{{ $key }}'"
                :class="activeTab === '{{ $key }}' 
                    ? 'bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white font-semibold shadow-sm border border-gray-200 dark:border-white/10' 
                    : 'hover:text-gray-900 dark:hover:text-white border border-transparent'"
                class="inline-flex h-[calc(100%-1px)] flex-1 items-center justify-center gap-1.5 rounded-lg px-4 py-2 text-sm font-medium whitespace-nowrap transition-all focus:outline-none cursor-pointer"
            >
                {{ $label }}
            </button>
        @endforeach
    </div>

    <!-- Tabs Content Container -->
    <div class="flex-1 outline-none">
        @foreach($tabs as $key => $label)
            <div
                x-show="activeTab === '{{ $key }}'"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-98"
                x-transition:enter-end="opacity-100 transform scale-100"
                class="outline-none"
                data-slot="tabs-content"
                style="display: none;"
            >
                {{ ${$key} ?? '' }}
            </div>
        @endforeach
    </div>
</div>
