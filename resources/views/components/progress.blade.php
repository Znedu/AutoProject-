@props([
    'value' => 0,
])

<div {{ $attributes->merge(['class' => 'bg-[#E63946]/20 relative h-2 w-full overflow-hidden rounded-full']) }} data-slot="progress">
    <div
        data-slot="progress-indicator"
        class="bg-[#E63946] h-full transition-all duration-300 rounded-full"
        style="width: {{ min(100, max(0, $value)) }}%;"
    ></div>
</div>
