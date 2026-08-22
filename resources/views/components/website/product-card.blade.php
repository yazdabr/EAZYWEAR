@props([
    'title',
    'category',
    'image',
    'price' => '149.000',
    'href' => '#'
])

<x-ui.card
    class="group overflow-hidden rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl sm:rounded-3xl sm:hover:-translate-y-2 sm:hover:shadow-2xl">

    {{-- Product Image --}}
    <div class="overflow-hidden">
        <img
            src="{{ asset($image) }}"
            alt="{{ $title }}"
            class="aspect-[4/3] w-full object-cover transition duration-500 group-hover:scale-105 sm:aspect-[4/5]">
    </div>

    {{-- Content --}}
    <div class="p-4 sm:p-6">

        {{-- Category --}}
        <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-[#AE7C18] sm:text-xs sm:tracking-[0.2em]">
            {{ $category }}
        </p>

        {{-- Product Name --}}
        <h3 class="mt-0.5 text-base font-bold text-gray-900 sm:mt-1 sm:text-2xl">
            {{ $title }}
        </h3>

        {{-- Starting Price --}}
        <div class="mt-2.5 sm:mt-5">
            <p class="text-[10px] uppercase tracking-[0.15em] text-gray-500 sm:text-xs sm:tracking-[0.2em]">
                Starting From
            </p>

            <p class="mt-0.5 text-base font-bold text-[#AE7C18] sm:mt-1 sm:text-2xl">
                Rp {{ $price }}
            </p>
        </div>

        {{-- View Detail --}}
        <div class="mt-3 sm:mt-6">
            <a
                href="{{ $href }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#AE7C18] transition-all duration-300 hover:gap-2 sm:gap-2 sm:text-base sm:hover:gap-3">
                View Detail
                <x-heroicon-o-arrow-right class="h-4 w-4 sm:h-5 sm:w-5"/>
            </a>
        </div>

    </div>

</x-ui.card>