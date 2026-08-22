@props([
    'placeholder' => 'Search products...',
])

<div class="relative w-full sm:w-80">

    {{-- Icon --}}
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 sm:pl-4">
        <x-heroicon-o-magnifying-glass
            class="h-4 w-4 text-slate-400 sm:h-5 sm:w-5"
        />
    </div>

    {{-- Input --}}
    <input
        type="text"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' => 'h-[42px] sm:h-[50px] w-full rounded-xl border border-slate-300 bg-white pl-10 sm:pl-11 pr-3.5 sm:pr-4 text-xs sm:text-sm text-slate-700 placeholder:text-slate-400 shadow-sm transition-all duration-300 focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/15'
        ]) }}
    >

</div>