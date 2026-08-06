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

<section class="bg-white py-24">

    <x-ui.container>

        {{-- Section Header --}}
        <x-ui.reveal>

            <div
                class="mx-auto mb-16 max-w-3xl text-center">

                <p
                    class="mb-4 font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">

                    WHY CHOOSE US

                </p>

                <h2
                    class="text-4xl font-bold leading-tight lg:text-5xl">

                    More Than Just

                    <span class="italic text-[#AE7C18]">

                        Custom Jerseys

                    </span>

                </h2>

                <p
                    class="mt-6 text-lg leading-8 text-gray-600">

                    We are committed to delivering premium sportswear with
                    exceptional craftsmanship, innovative technology,
                    and outstanding customer service.

                </p>

            </div>

        </x-ui.reveal>

        {{-- Cards --}}
        <div
            class="grid items-stretch gap-8 md:grid-cols-2 xl:grid-cols-4">

            @foreach($features as $feature)

                <x-ui.reveal
                    :index="$loop->index">

                    <div
                        class="group flex h-full transform-gpu flex-col rounded-3xl border border-gray-200 bg-white p-8 shadow-md transition-all duration-300 ease-out will-change-transform hover:-translate-y-3 hover:scale-[1.02] hover:border-[#AE7C18] hover:shadow-2xl">

                        {{-- Icon --}}
                        <div
                            class="mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-[#AE7C18]/10 text-[#AE7C18] transition-all duration-300 group-hover:scale-110 group-hover:bg-[#AE7C18] group-hover:text-white">

                            @switch($feature['icon'])

                                @case('shield-check')
                                    <x-heroicon-o-shield-check class="h-8 w-8"/>
                                    @break

                                @case('users')
                                    <x-heroicon-o-users class="h-8 w-8"/>
                                    @break

                                @case('briefcase')
                                    <x-heroicon-o-briefcase class="h-8 w-8"/>
                                    @break

                                @case('hand-thumb-up')
                                    <x-heroicon-o-hand-thumb-up class="h-8 w-8"/>
                                    @break

                            @endswitch

                        </div>

                        {{-- Title --}}
                        <h3
                            class="text-2xl font-bold transition-colors duration-300 group-hover:text-[#AE7C18]">

                            {{ $feature['title'] }}

                        </h3>

                        {{-- Description --}}
                        <p
                            class="mt-4 flex-1 leading-7 text-gray-600 transition-colors duration-300 group-hover:text-gray-700">

                            {{ $feature['description'] }}

                        </p>

                    </div>

                </x-ui.reveal>

            @endforeach

        </div>

    </x-ui.container>

</section>