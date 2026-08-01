@props([
    'title',
    'category',
    'image',
    'price' => '149.000',
    'href' => '#'
])

<x-ui.card
    class="group overflow-hidden transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">

    {{-- Product Image --}}
    <div class="overflow-hidden">

        <img
            src="{{ asset($image) }}"
            alt="{{ $title }}"
            class="aspect-[4/5] w-full object-cover transition duration-500 group-hover:scale-105">

    </div>

    {{-- Content --}}
    <div class="p-6">

        {{-- Category --}}
        <p class="mb-2 text-sm font-semibold uppercase tracking-[0.2em] text-[#AE7C18]">

            {{ $category }}

        </p>

        {{-- Product Name --}}
        <h3 class="text-2xl font-bold text-gray-900">

            {{ $title }}

        </h3>

        {{-- Starting Price --}}
        <div class="mt-5">

            <p class="text-xs uppercase tracking-[0.2em] text-gray-500">

                Starting From

            </p>

            <p class="mt-1 text-2xl font-bold text-[#AE7C18]">

                Rp {{ $price }}

            </p>

        </div>

        {{-- View Detail --}}
        <div class="mt-6">

            <a
                href="{{ $href }}"
                class="inline-flex items-center gap-2 font-semibold text-[#AE7C18] transition-all duration-300 hover:gap-3">

                View Detail

                <x-heroicon-o-arrow-right class="h-5 w-5"/>

            </a>

        </div>

    </div>

</x-ui.card>