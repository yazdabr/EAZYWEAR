@props([
    'name',
    'position',
    'image',
    'quote'
])

<x-ui.card
    class="group h-full rounded-2xl border border-[#AE7C18]/40 p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl sm:rounded-3xl sm:p-8 sm:hover:-translate-y-2">

    {{-- Rating --}}
    <div class="mb-4 flex text-[#F4B400] sm:mb-6">

        @for($i = 0; $i < 5; $i++)

            <x-heroicon-s-star class="h-4 w-4 sm:h-5 sm:w-5"/>

        @endfor

    </div>

    {{-- Quote --}}
    <blockquote
        class="text-xs italic leading-relaxed text-gray-700 sm:text-base sm:leading-8">

        "{{ $quote }}"

    </blockquote>

    {{-- Divider --}}
    <div class="my-4 h-px bg-[#AE7C18]/30 sm:my-8"></div>

    {{-- User --}}
    <div class="flex items-center gap-3 sm:gap-4">

        <img
            src="{{ asset($image) }}"
            alt="Testimonial {{ $name }}"
            loading="lazy"
            decoding="async"
            class="h-10 w-10 rounded-full object-cover sm:h-14 sm:w-14"
        >

        <div>

            <h4
                class="text-xs font-bold text-gray-900 sm:text-base">

                {{ $name }}

            </h4>

            <p
                class="text-[10px] text-gray-500 sm:text-sm">

                {{ $position }}

            </p>

        </div>

    </div>

</x-ui.card>