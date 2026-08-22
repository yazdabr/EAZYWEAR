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

<section class="bg-[#F8F8F8] py-12 sm:py-20 lg:py-24">

    <x-ui.container>

        {{-- Heading --}}
        <x-ui.reveal>

            <div class="mx-auto mb-10 max-w-3xl text-center sm:mb-16 lg:mb-20">

                <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mb-4 sm:text-xs sm:tracking-[0.3em]">
                    OUR WORKFLOW
                </p>

                <h2 class="text-2xl font-bold lg:text-5xl sm:text-4xl">
                    Journey of
                    <span class="italic text-[#AE7C18]">
                        Your Jersey
                    </span>
                </h2>

                <p class="mt-3 text-xs leading-relaxed text-gray-600 sm:mt-6 sm:text-lg sm:leading-8">
                    Every jersey goes through a carefully managed production
                    process to ensure premium quality from concept to delivery.
                </p>

            </div>

        </x-ui.reveal>

        {{-- Timeline --}}
        <div class="relative">

            {{-- Line Horizontal (Khusus Desktop lg) --}}
            <div class="absolute left-0 right-0 top-10 hidden h-1 bg-gray-200 lg:block"></div>

            {{-- Line Vertikal (Khusus Mobile/Tablet) --}}
            <div class="absolute bottom-4 left-5 top-5 w-0.5 bg-[#AE7C18]/30 lg:hidden"></div>

            <div class="grid gap-6 lg:grid-cols-6 lg:gap-8">

                @foreach($steps as $step)

                    <x-ui.reveal :index="$loop->index">

                        {{-- Card/Item Layout --}}
                        <div class="relative flex items-start gap-4 text-left lg:block lg:text-center">

                            {{-- Badge Angka Step (Tambahan Khusus Mobile agar Urutan Jelas) --}}
                            <div class="absolute -top-1 left-7 z-10 flex h-4 w-4 items-center justify-center rounded-full bg-gray-900 text-[9px] font-bold text-white shadow lg:hidden">
                                {{ $loop->iteration }}
                            </div>

                            {{-- Icon Bubble --}}
                            <div class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#AE7C18] text-white shadow-md transition-all duration-300 hover:scale-110 sm:h-12 sm:w-12 lg:mx-auto lg:h-20 lg:w-20 lg:shadow-lg">

                                @switch($step['icon'])
                                    @case('chat-bubble-left-right')
                                        <x-heroicon-o-chat-bubble-left-right class="h-5 w-5 sm:h-6 sm:w-6 lg:h-9 lg:w-9"/>
                                        @break

                                    @case('pencil-square')
                                        <x-heroicon-o-pencil-square class="h-5 w-5 sm:h-6 sm:w-6 lg:h-9 lg:w-9"/>
                                        @break

                                    @case('swatch')
                                        <x-heroicon-o-swatch class="h-5 w-5 sm:h-6 sm:w-6 lg:h-9 lg:w-9"/>
                                        @break

                                    @case('scissors')
                                        <x-heroicon-o-scissors class="h-5 w-5 sm:h-6 sm:w-6 lg:h-9 lg:w-9"/>
                                        @break

                                    @case('shield-check')
                                        <x-heroicon-o-shield-check class="h-5 w-5 sm:h-6 sm:w-6 lg:h-9 lg:w-9"/>
                                        @break

                                    @case('truck')
                                        <x-heroicon-o-truck class="h-5 w-5 sm:h-6 sm:w-6 lg:h-9 lg:w-9"/>
                                        @break
                                @endswitch

                            </div>

                            {{-- Content (Samping di Mobile, Bawah di Desktop) --}}
                            <div class="pt-0.5 lg:pt-0">

                                {{-- Title --}}
                                <h3 class="text-sm font-bold text-gray-900 lg:mt-6 sm:text-base lg:text-lg">
                                    {{ $step['title'] }}
                                </h3>

                                {{-- Description --}}
                                <p class="mt-1 text-xs leading-relaxed text-gray-600 lg:mt-3 sm:text-sm lg:leading-6">
                                    {{ $step['description'] }}
                                </p>

                            </div>

                        </div>

                    </x-ui.reveal>

                @endforeach

            </div>

        </div>

    </x-ui.container>

</section>