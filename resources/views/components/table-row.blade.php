<tr {{ $attributes->merge(['class' => 'hover:bg-gray-100/50 dark:hover:bg-white/5 data-[state=selected]:bg-gray-200/50 dark:data-[state=selected]:bg-white/10 border-b border-gray-200 dark:border-white/10 transition-colors']) }}>
    {{ $slot }}
</tr>
