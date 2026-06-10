@props([
    'variant' => 'primary',
    'size' => 'md',
])

@php
    $baseStyles = 'inline-flex items-center justify-center rounded-xl font-semibold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer';

    $variants = [
        'primary' => 'bg-gray-200 dark:bg-[#151515] text-gray-900 dark:text-white border border-gray-300 dark:border-white/10 hover:border-[#E63946] hover:shadow-lg hover:shadow-[#E63946]/20',
        'secondary' => 'bg-gray-300 dark:bg-[#2F2F2F] text-gray-900 dark:text-white border border-gray-400 dark:border-white/10 hover:border-gray-500 dark:hover:border-white/30 hover:shadow-lg',
        'accent' => 'bg-gradient-red text-white hover:shadow-xl hover:shadow-[#E63946]/50 glow-red-hover border border-transparent',
        'outline' => 'border-2 border-gray-400 dark:border-white/20 text-gray-900 dark:text-white hover:border-[#E63946] hover:text-[#E63946] hover:shadow-lg hover:shadow-[#E63946]/20',
        'ghost' => 'text-gray-600 dark:text-[#B8B8B8] hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5',
    ];

    $sizes = [
        'sm' => 'px-4 py-2 text-sm',
        'md' => 'px-6 py-3 text-base',
        'lg' => 'px-8 py-4 text-lg',
    ];

    $isAlpine = $attributes->has(':variant');
    $alpineVariant = $attributes->get(':variant');
@endphp

@if($isAlpine)
    @php
        $baseClass = $baseStyles . ' ' . ($sizes[$size] ?? $sizes['md']);
        $variantsJson = json_encode($variants);
    @endphp
    <button 
        {{ $attributes->except(['variant', ':variant']) }}
        class="{{ $baseClass }}"
        :class="({{ $variantsJson }})[{{ $alpineVariant }}] || '{{ $variants['primary'] }}'"
    >
        {{ $slot }}
    </button>
@else
    @php
        $classes = $baseStyles . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
    @endphp
    <button {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif

