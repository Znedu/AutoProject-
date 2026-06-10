@props([
    'variant' => 'default',
])

@php
    $variants = [
        'default' => 'border-transparent bg-[#E63946] text-white hover:bg-[#E63946]/90',
        'secondary' => 'border-transparent bg-gray-200 dark:bg-[#2F2F2F] text-gray-900 dark:text-white hover:bg-gray-300 dark:hover:bg-white/10',
        'destructive' => 'border-transparent bg-red-600 text-white hover:bg-red-700 focus-visible:ring-red-500/20 dark:focus-visible:ring-red-500/40 dark:bg-red-500/60',
        'outline' => 'text-gray-900 dark:text-white border border-gray-300 dark:border-white/20 hover:bg-gray-50 dark:hover:bg-white/5',
    ];

    $classes = 'inline-flex items-center justify-center rounded-md border px-2.5 py-0.5 text-xs font-semibold w-fit whitespace-nowrap shrink-0 transition-[color,box-shadow] ' . ($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
