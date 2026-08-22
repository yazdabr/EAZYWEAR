@props([
    'step',
    'title',
    'description'
])

<div class="relative flex items-start gap-4 text-left sm:flex-col sm:items-center sm:text-center">

    {{-- Circle --}}
    <div
        class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#AE7C18] text-lg font-bold text-white shadow-md sm:h-20 sm:w-20 sm:text-4xl sm:shadow-lg">

        {{ sprintf('%02d', $step) }}

    </div>

    {{-- Content Wrapper --}}
    <div class="pt-1 sm:pt-0">
        {{-- Title --}}
        <h3 class="text-base font-bold text-gray-900 sm:mt-8 sm:text-2xl lg:text-3xl">

            {{ $title }}

        </h3>

        {{-- Description --}}
        <p class="mt-1 text-xs leading-relaxed text-gray-600 sm:mt-4 sm:max-w-[220px] sm:text-base sm:leading-7">

            {{ $description }}

        </p>
    </div>

</div>