@props([
    'align' => 'right',
    'width' => '48',
    'contentClasses' => 'py-1 bg-white dark:bg-[#151515] border border-gray-200 dark:border-white/10',
    'dropdownClasses' => '',
])

@php
switch ($align) {
    case 'left':
        $alignmentClasses = 'origin-top-left left-0';
        break;
    case 'top':
        $alignmentClasses = 'origin-top';
        break;
    case 'right':
    default:
        $alignmentClasses = 'origin-top-right right-0';
        break;
}

switch ($width) {
    case '48':
        $widthClass = 'w-48';
        break;
    case '60':
        $widthClass = 'w-60';
        break;
    default:
        $widthClass = $width;
        break;
}
@endphp

<div x-data="{ open: false }" x-on:click.outside="open = false" x-on:close.stop="open = false" class="relative {{ $dropdownClasses }}">
    <div x-on:click="open = ! open" class="cursor-pointer">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute z-50 mt-2 {{ $widthClass }} rounded-xl shadow-lg {{ $alignmentClasses }}"
        style="display: none;"
    >
        <div class="rounded-xl ring-1 ring-black ring-opacity-5 {{ $contentClasses }}">
            {{ $content }}
        </div>
    </div>
</div>
