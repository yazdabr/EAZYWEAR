<section class="bg-white py-12 sm:py-20 lg:py-24 overflow-hidden">

    <x-ui.container>

        <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">

            {{-- LEFT: Animasi Berurutan --}}
            <div class="flex flex-col">

                {{-- 1. Label --}}
                <x-ui.reveal animation="right" :delay="0">

                    <div class="mb-3 flex items-center gap-2.5 sm:mb-6 sm:gap-3">

                        <div class="h-px w-8 bg-[#AE7C18] sm:w-12"></div>

                        <span
                            class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:text-xs sm:tracking-[0.3em]">

                            OUR LEGACY

                        </span>

                    </div>

                </x-ui.reveal>

                {{-- 2. Heading --}}
                <x-ui.reveal animation="right" :delay="100">

                    <h2
                        class="text-2xl font-bold leading-tight sm:text-4xl lg:text-5xl">

                        Driven by

                        <span class="italic text-[#AE7C18]">

                            Performance

                        </span>

                    </h2>

                </x-ui.reveal>

                {{-- 3. Paragraphs --}}
                <x-ui.reveal animation="right" :delay="200">

                    <div class="mt-4 space-y-3 text-xs leading-relaxed text-gray-600 sm:mt-8 sm:space-y-6 sm:text-base sm:leading-8">

                        <p>
                            Based in Banjarmasin, Eazywear Indonesia is built around a simple belief: every team, community, and organization deserves sportswear that matches their ambition.
                        </p>

                        <p>
                            We combine local craftsmanship with quality materials, precision manufacturing, and custom designs to create sportswear that is comfortable, durable, and made to perform.
                        </p>

                        <p class="hidden sm:block">
                            Today, Eazywear serves teams, schools, communities, companies, and organizations across Indonesia with a commitment to quality, detail, and reliable service.
                        </p>

                    </div>

                </x-ui.reveal>

            </div>

            {{-- RIGHT: Gambar & Floating Badge --}}
            <div class="relative flex justify-center lg:block">

                <div class="relative mx-auto w-full max-w-[240px] sm:max-w-md lg:max-w-none">

                    {{-- Image Reveal --}}
                    <x-ui.reveal animation="left" :delay="150">

                        <img
                            src="{{ asset('images/about/df.png') }}"
                            alt="Eazywear Story"
                            loading="eager"
                            decoding="sync"
                            class="h-auto w-full rounded-2xl object-cover shadow-xl sm:rounded-3xl sm:shadow-2xl">

                    </x-ui.reveal>

                    {{-- Floating Badge: Animasi Pop/Scale Terpisah setelah Gambar --}}
                    <div class="absolute -bottom-3 -left-3 z-10 sm:-bottom-6 sm:-left-4">

                        <x-ui.reveal animation="scale" :delay="350">

                            <div
                                class="flex items-center gap-2 rounded-xl bg-[#AE7C18] px-3 py-2 text-white shadow-md sm:gap-3.5 sm:rounded-2xl sm:px-6 sm:py-4 sm:shadow-xl">

                                <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-white/20 sm:h-11 sm:w-11 sm:rounded-xl">
                                    <x-heroicon-s-sparkles class="h-3.5 w-3.5 text-white sm:h-6 sm:w-6" />
                                </div>

                                <div>
                                    <p class="text-[9px] font-bold leading-tight uppercase tracking-wider sm:text-base">
                                        100% Premium
                                    </p>
                                    <p class="text-[7px] font-medium uppercase tracking-widest text-white/80 sm:text-xs">
                                        Custom Sportswear
                                    </p>
                                </div>

                            </div>

                        </x-ui.reveal>

                    </div>

                </div>

            </div>

        </div>

    </x-ui.container>

</section>