@php

$features = [
    [
        'title' => 'Premium Quality',
        'description' => 'High-quality fabrics, precision stitching, and premium sublimation printing ensure every jersey meets professional standards.',
        'icon' => 'shield-check',
    ],
    [
        'title' => 'Experienced Team',
        'description' => 'Our experienced designers and production team transform your ideas into high-quality jerseys with attention to every detail.',
        'icon' => 'users',
    ],
    [
        'title' => 'Professional Service',
        'description' => 'From consultation to delivery, we provide responsive communication and professional support for every customer.',
        'icon' => 'briefcase',
    ],
    [
        'title' => 'Trusted Partner',
        'description' => 'Trusted by schools, communities, companies, and sports teams throughout Indonesia for premium custom apparel.',
        'icon' => 'hand-thumb-up',
    ],
];

@endphp

<section class="bg-white py-12 sm:py-20 lg:py-24">

    <x-ui.container>

        {{-- Section Header --}}
        <x-ui.reveal>

            <div class="mx-auto mb-8 max-w-3xl text-center sm:mb-16">

                <p class="mb-2 font-semibold uppercase tracking-[0.2em] text-[#AE7C18] text-[10px] sm:text-xs sm:mb-4 sm:tracking-[0.3em]">
                    WHY CHOOSE US
                </p>

                <h2 class="text-2xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                    More Than Just
                    <span class="italic text-[#AE7C18]">
                        Custom Jerseys
                    </span>
                </h2>

                <p class="mt-3 text-xs leading-relaxed text-gray-600 sm:mt-6 sm:text-lg sm:leading-8">
                    We are committed to delivering premium sportswear with
                    exceptional craftsmanship, innovative technology,
                    and outstanding customer service.
                </p>

            </div>

        </x-ui.reveal>

        {{-- Cards Grid: 2 Kolom di Mobile, 4 Kolom di XL --}}
        <div class="grid grid-cols-2 gap-3.5 sm:gap-6 xl:grid-cols-4 items-stretch">

            @foreach($features as $feature)

                <x-ui.reveal :index="$loop->index" class="h-full">

                    <div class="group flex h-full transform-gpu flex-col rounded-2xl border border-gray-100 bg-white p-4 shadow-sm transition-all duration-300 ease-out will-change-transform hover:-translate-y-1 hover:border-[#AE7C18] hover:shadow-xl sm:rounded-3xl sm:border-gray-200 sm:p-8 sm:shadow-md sm:hover:-translate-y-2">

                        {{-- Icon --}}
                        <div class="mb-3 flex h-9 w-9 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18] transition-all duration-300 group-hover:scale-110 group-hover:bg-[#AE7C18] group-hover:text-white sm:mb-6 sm:h-16 sm:w-16 sm:rounded-2xl">

                            @switch($feature['icon'])
                                @case('shield-check')
                                    <x-heroicon-o-shield-check class="h-5 w-5 sm:h-8 sm:w-8"/>
                                    @break

                                @case('users')
                                    <x-heroicon-o-users class="h-5 w-5 sm:h-8 sm:w-8"/>
                                    @break

                                @case('briefcase')
                                    <x-heroicon-o-briefcase class="h-5 w-5 sm:h-8 sm:w-8"/>
                                    @break

                                @case('hand-thumb-up')
                                    <x-heroicon-o-hand-thumb-up class="h-5 w-5 sm:h-8 sm:w-8"/>
                                    @break
                            @endswitch

                        </div>

                        {{-- Title --}}
                        <h3 class="text-sm font-bold text-gray-900 transition-colors duration-300 group-hover:text-[#AE7C18] sm:text-2xl">
                            {{ $feature['title'] }}
                        </h3>

                        {{-- Description --}}
                        <p class="mt-1.5 flex-1 text-[11px] leading-relaxed text-gray-600 transition-colors duration-300 group-hover:text-gray-700 sm:mt-4 sm:text-base sm:leading-7">
                            {{ $feature['description'] }}
                        </p>

                    </div>

                </x-ui.reveal>

            @endforeach

        </div>

    </x-ui.container>

</section>