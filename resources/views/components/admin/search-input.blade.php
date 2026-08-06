@props([

    'placeholder' => 'Search products...',

])

<div class="relative w-full sm:w-80">

    {{-- Icon --}}
    <div
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">

        <x-heroicon-o-magnifying-glass
            class="h-5 w-5 text-slate-400"/>

    </div>

    {{-- Input --}}
    <input
        type="text"
        placeholder="{{ $placeholder }}"
        class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 placeholder:text-slate-400 shadow-sm transition-all duration-300 focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/15">

</div>