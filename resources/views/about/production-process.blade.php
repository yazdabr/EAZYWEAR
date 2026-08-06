@php

$steps = [

    [
        'title' => 'Consultation',
        'description' => 'Discuss your ideas and requirements with our team.',
        'icon' => 'chat-bubble-left-right',
    ],

    [
        'title' => 'Design',
        'description' => 'Our designers create a unique custom jersey.',
        'icon' => 'pencil-square',
    ],

    [
        'title' => 'Printing',
        'description' => 'High-definition sublimation printing process.',
        'icon' => 'swatch',
    ],

    [
        'title' => 'Sewing',
        'description' => 'Professional stitching with premium finishing.',
        'icon' => 'scissors',
    ],

    [
        'title' => 'Quality Check',
        'description' => 'Every jersey is inspected before shipping.',
        'icon' => 'shield-check',
    ],

    [
        'title' => 'Delivery',
        'description' => 'Your custom jerseys are delivered safely.',
        'icon' => 'truck',
    ],

];

@endphp

<section class="bg-[#F8F8F8] py-24">

    <x-ui.container>

        {{-- Heading --}}
        <x-ui.reveal>

            <div
                class="mx-auto mb-20 max-w-3xl text-center">

                <p
                    class="mb-4 font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">

                    OUR WORKFLOW

                </p>

                <h2
                    class="text-4xl font-bold lg:text-5xl">

                    Journey of

                    <span class="italic text-[#AE7C18]">

                        Your Jersey

                    </span>

                </h2>

                <p
                    class="mt-6 text-lg leading-8 text-gray-600">

                    Every jersey goes through a carefully managed production
                    process to ensure premium quality from concept to delivery.

                </p>

            </div>

        </x-ui.reveal>

        {{-- Timeline --}}
        <div
            class="relative">

            {{-- Line Desktop --}}
            <div
                class="absolute left-0 right-0 top-10 hidden h-1 bg-[#E6E6E6] lg:block">
            </div>

            <div
                class="grid gap-10 lg:grid-cols-6">

                @foreach($steps as $step)

                    <x-ui.reveal
                        :index="$loop->index">

                        <div
                            class="relative text-center">

                            {{-- Icon --}}
                            <div
                                class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#AE7C18] text-white shadow-lg transition-all duration-300 hover:scale-110">

                                @switch($step['icon'])

                                    @case('chat-bubble-left-right')
                                        <x-heroicon-o-chat-bubble-left-right class="h-9 w-9"/>
                                        @break

                                    @case('pencil-square')
                                        <x-heroicon-o-pencil-square class="h-9 w-9"/>
                                        @break

                                    @case('swatch')
                                        <x-heroicon-o-swatch class="h-9 w-9"/>
                                        @break

                                    @case('scissors')
                                        <x-heroicon-o-scissors class="h-9 w-9"/>
                                        @break

                                    @case('shield-check')
                                        <x-heroicon-o-shield-check class="h-9 w-9"/>
                                        @break

                                    @case('truck')
                                        <x-heroicon-o-truck class="h-9 w-9"/>
                                        @break

                                @endswitch

                            </div>

                            {{-- Title --}}
                            <h3
                                class="mt-6 text-lg font-bold">

                                {{ $step['title'] }}

                            </h3>

                            {{-- Description --}}
                            <p
                                class="mt-3 text-sm leading-6 text-gray-600">

                                {{ $step['description'] }}

                            </p>

                        </div>

                    </x-ui.reveal>

                @endforeach

            </div>

        </div>

    </x-ui.container>

</section>