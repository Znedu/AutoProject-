<div data-slot="table-container" class="relative w-full overflow-x-auto">
    <table {{ $attributes->merge(['class' => 'w-full caption-bottom text-sm']) }}>
        {{ $slot }}
    </table>
</div>
