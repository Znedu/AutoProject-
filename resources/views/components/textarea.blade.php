@props([
    'label' => null,
    'error' => null,
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
    <textarea
        {{ $attributes->merge([
            'required' => $required,
            'class' => 'w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#1F1F1F] text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-[#666666] focus:outline-none focus:ring-2 focus:ring-[#E63946] focus:border-transparent transition-all resize-none ' . ($error ? 'border-red-500 ring-2 ring-red-500/20' : '')
        ]) }}
    ></textarea>
    @if($error)
        <p class="mt-2 text-sm text-red-500">{{ $error }}</p>
    @endif
</div>
