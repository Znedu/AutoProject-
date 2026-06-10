@props([
    'title',
    'value',
    'trend' => null,
    'color' => 'red',
])

@php
    $colors = [
        'red' => 'bg-[#E63946]/10 text-[#E63946] border border-[#E63946]/20',
        'blue' => 'bg-[#457B9D]/10 text-[#457B9D] border border-[#457B9D]/20',
        'charcoal' => 'bg-gray-200 dark:bg-white/5 text-gray-900 dark:text-white border border-gray-300 dark:border-white/10',
        'green' => 'bg-green-500/10 text-green-500 border border-green-500/20',
    ];

    $colorClass = $colors[$color] ?? $colors['red'];
@endphp

<x-card class="flex items-start gap-4 hover:scale-105 cursor-pointer" hover>
    <div class="p-4 rounded-xl {{ $colorClass }}">
        {{ $icon ?? '' }}
    </div>
    <div class="flex-1">
        <p class="text-sm text-gray-600 dark:text-[#B8B8B8] mb-1">{{ $title }}</p>
        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
        @if($trend)
            @php
                $isPositive = is_array($trend) ? ($trend['isPositive'] ?? true) : ($trend->isPositive ?? true);
                $trendVal = is_array($trend) ? ($trend['value'] ?? '') : ($trend->value ?? '');
            @endphp
            <p class="text-sm mt-2 font-medium {{ $isPositive ? 'text-green-500' : 'text-red-500' }}">
                {{ $isPositive ? '↑' : '↓' }} {{ $trendVal }}
            </p>
        @endif
    </div>
</x-card>
