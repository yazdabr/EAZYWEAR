@props([
    'step',
    'title',
    'description'
])

<div class="relative flex flex-col items-center text-center">

    {{-- Circle --}}
    <div
        class="relative z-10 flex h-20 w-20 items-center justify-center rounded-full bg-[#AE7C18] text-4xl font-bold text-white shadow-lg">

        {{ sprintf('%02d', $step) }}

    </div>

    {{-- Title --}}
    <h3 class="mt-8 text-3xl font-bold text-gray-900">

        {{ $title }}

    </h3>

    {{-- Description --}}
    <p class="mt-4 max-w-[220px] leading-8 text-gray-600">

        {{ $description }}

    </p>

</div>