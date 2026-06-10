@props([
    'label' => null,
    'error' => null,
    'options' => [],
    'required' => false,
])

<div class="w-full">
    @if($label)
        <label class="block mb-2 text-gray-700 dark:text-[#B8B8B8] font-medium">
            {{ $label }}
            @if($required)
                <span class="text-[#E63946] ml-1">*</span>
            @endif
        </label>
    @endif
    <select
        {{ $attributes->merge([
            'required' => $required,
            'class' => 'w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all ' . ($error ? 'border-red-500 ring-2 ring-red-500/20' : '')
        ]) }}
    >
        @if(!empty($options))
            @foreach($options as $option)
                @php
                    $val = is_array($option) ? ($option['value'] ?? '') : $option;
                    $lbl = is_array($option) ? ($option['label'] ?? '') : $option;
                @endphp
                <option value="{{ $val }}" class="bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white">
                    {{ $lbl }}
                </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
    @if($error)
        <p class="mt-2 text-sm text-red-500">{{ $error }}</p>
    @endif
</div>
