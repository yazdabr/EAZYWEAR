@props([
    'name',
    'position',
    'image',
    'quote'
])

<x-ui.card
    class="group h-full rounded-3xl border border-[#AE7C18]/40 p-8 transition-all duration-300 hover:-translate-y-2 hover:shadow-xl">

    {{-- Rating --}}
    <div class="mb-6 flex text-[#F4B400]">

        @for($i = 0; $i < 5; $i++)

            <x-heroicon-s-star class="h-5 w-5"/>

        @endfor

    </div>

    {{-- Quote --}}
    <blockquote
        class="italic leading-8 text-gray-700">

        "{{ $quote }}"

    </blockquote>

    {{-- Divider --}}
    <div class="my-8 h-px bg-[#AE7C18]/30"></div>

    {{-- User --}}
    <div class="flex items-center gap-4">

        <img
            src="{{ asset($image) }}"
            alt="{{ $name }}"
            class="h-14 w-14 rounded-full object-cover">

        <div>

            <h4
                class="font-bold text-gray-900">

                {{ $name }}

            </h4>

            <p
                class="text-sm text-gray-500">

                {{ $position }}

            </p>

        </div>

    </div>

</x-ui.card>