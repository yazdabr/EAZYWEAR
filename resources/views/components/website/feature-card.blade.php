@props([
    'title',
    'description'
])

<x-ui.card
    class="group h-full rounded-3xl border border-gray-200 p-8 transition-all duration-300 hover:-translate-y-2 hover:border-[#AE7C18] hover:shadow-2xl">

    {{-- Icon --}}
    <div
        class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#AE7C18]/10 text-[#AE7C18] transition duration-300 group-hover:bg-[#AE7C18] group-hover:text-white">

        {{ $slot }}

    </div>

    {{-- Title --}}
    <h3
        class="mb-4 text-2xl font-bold text-gray-900">

        {{ $title }}

    </h3>

    {{-- Description --}}
    <p
        class="leading-7 text-gray-600">

        {{ $description }}

    </p>

</x-ui.card>