@props([
    'hover' => false,
    'variant' => 'glass',
])

@php
    $variantClasses = [
        'default' => 'bg-white dark:bg-[#151515] border border-gray-300 dark:border-white/10',
        'glass' => 'bg-white/80 dark:bg-[#151515]/70 backdrop-blur-md border border-gray-200 dark:border-white/10 shadow-sm dark:shadow-[0_8px_32px_0_rgba(0,0,0,0.37)]',
    ];

    $hoverClasses = $hover 
        ? 'hover:shadow-xl dark:hover:shadow-[0_8px_32px_0_rgba(230,57,70,0.2)] hover:border-[#E63946]/30 dark:hover:border-[#E63946]/50 hover:-translate-y-0.5' 
        : '';

    $classes = 'rounded-xl p-6 transition-all duration-300 ' . ($variantClasses[$variant] ?? $variantClasses['glass']) . ' ' . $hoverClasses;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
