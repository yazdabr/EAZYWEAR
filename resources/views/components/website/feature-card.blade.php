@props([
    'title',
    'description'
])

<x-ui.card
    class="group h-full rounded-2xl border border-gray-200 p-5 transition-all duration-300 hover:-translate-y-1 hover:border-[#AE7C18] hover:shadow-xl sm:rounded-3xl sm:p-8 sm:hover:-translate-y-2 sm:hover:shadow-2xl">

    {{-- Icon --}}
    <div
        class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18] transition duration-300 group-hover:bg-[#AE7C18] group-hover:text-white sm:mb-6 sm:h-16 sm:w-16 sm:rounded-2xl">

        {{ $slot }}

    </div>

    {{-- Title --}}
    <h3
        class="mb-2 text-lg font-bold text-gray-900 sm:mb-4 sm:text-2xl">

        {{ $title }}

    </h3>

    {{-- Description --}}
    <p
        class="text-xs leading-relaxed text-gray-600 sm:text-base sm:leading-7">

        {{ $description }}

    </p>

</x-ui.card>