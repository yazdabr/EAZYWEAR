<section class="bg-white py-12 sm:py-20 lg:py-28">
    <x-ui.container>

        {{-- Heading --}}
        <x-ui.reveal>
            <div class="mb-8 text-center sm:mb-14 lg:mb-16">
                <p class="mb-2 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mb-3 sm:text-xs lg:tracking-[0.3em]">
                    FEATURED PRODUCTS
                </p>

                <h2 class="text-2xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                    Our Best Collections
                </h2>

                <p class="mx-auto mt-3 max-w-2xl text-xs leading-relaxed text-gray-600 sm:mt-6 sm:text-base sm:leading-8 lg:text-lg">
                    Explore our custom jersey collections, designed with attention to detail,
                    quality materials, and a style made for your team.
                </p>
            </div>
        </x-ui.reveal>

        {{-- Product Grid --}}
        <div class="mx-auto grid max-w-6xl gap-4 sm:gap-6 lg:gap-8 md:grid-cols-3">

            {{-- Product 1 --}}
            <x-ui.reveal delay="100">
                <a
                    href="{{ route('catalog') }}"
                    class="group relative block h-72 w-full overflow-hidden rounded-2xl bg-slate-200 transition-all duration-300 hover:shadow-xl active:scale-[0.98] sm:h-auto sm:aspect-[4/5]"
                >
                    <picture>
                        {{-- Gambar Desktop (Layar >= 640px) --}}
                        <source media="(min-width: 640px)" srcset="{{ asset('images/hero/jersey1.png') }}">
                        {{-- Gambar Mobile --}}
                        <img
                            src="{{ asset('images/hero/jersey1-mobile.png') }}"
                            alt="Jersey Collection 01"
                            class="absolute inset-0 h-full w-full object-cover object-center transition duration-500 group-hover:scale-105"
                        >
                    </picture>

                    <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>

                    <div class="absolute inset-x-0 bottom-0 flex items-end justify-between p-4 sm:p-5 lg:p-6">
                        <div></div>

                        {{-- Button Panah --}}
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/50 bg-black/20 text-white backdrop-blur-md transition-all duration-300 group-hover:border-[#AE7C18] group-hover:bg-[#AE7C18] group-hover:scale-110 sm:h-10 sm:w-10">
                            <svg
                                class="h-4 w-4 transition-transform duration-300 group-hover:scale-110 sm:h-[18px] sm:w-[18px]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M5 12h14m-6-6 6 6-6 6"
                                />
                            </svg>
                        </div>
                    </div>
                </a>
            </x-ui.reveal>

            {{-- Product 2 --}}
            <x-ui.reveal delay="200">
                <a
                    href="{{ route('catalog') }}"
                    class="group relative block h-72 w-full overflow-hidden rounded-2xl bg-slate-200 transition-all duration-300 hover:shadow-xl active:scale-[0.98] sm:h-auto sm:aspect-[4/5]"
                >
                    <picture>
                        <source media="(min-width: 640px)" srcset="{{ asset('images/hero/jersey2.png') }}">
                        <img
                            src="{{ asset('images/hero/jersey2-mobile.png') }}"
                            alt="Jersey Collection 02"
                            class="absolute inset-0 h-full w-full object-cover object-center transition duration-500 group-hover:scale-105"
                        >
                    </picture>

                    <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>

                    <div class="absolute inset-x-0 bottom-0 flex items-end justify-between p-4 sm:p-5 lg:p-6">
                        <div></div>

                        {{-- Button Panah --}}
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/50 bg-black/20 text-white backdrop-blur-md transition-all duration-300 group-hover:border-[#AE7C18] group-hover:bg-[#AE7C18] group-hover:scale-110 sm:h-10 sm:w-10">
                            <svg
                                class="h-4 w-4 transition-transform duration-300 group-hover:scale-110 sm:h-[18px] sm:w-[18px]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M5 12h14m-6-6 6 6-6 6"
                                />
                            </svg>
                        </div>
                    </div>
                </a>
            </x-ui.reveal>

            {{-- Product 3 --}}
            <x-ui.reveal delay="300">
                <a
                    href="{{ route('catalog') }}"
                    class="group relative block h-72 w-full overflow-hidden rounded-2xl bg-slate-200 transition-all duration-300 hover:shadow-xl active:scale-[0.98] sm:h-auto sm:aspect-[4/5]"
                >
                    <picture>
                        <source media="(min-width: 640px)" srcset="{{ asset('images/hero/jersey3.png') }}">
                        <img
                            src="{{ asset('images/hero/jersey3-mobile.png') }}"
                            alt="Jersey Collection 03"
                            class="absolute inset-0 h-full w-full object-cover object-center transition duration-500 group-hover:scale-105"
                        >
                    </picture>

                    <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent"></div>

                    <div class="absolute inset-x-0 bottom-0 flex items-end justify-between p-4 sm:p-5 lg:p-6">
                        <div></div>

                        {{-- Button Panah --}}
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/50 bg-black/20 text-white backdrop-blur-md transition-all duration-300 group-hover:border-[#AE7C18] group-hover:bg-[#AE7C18] group-hover:scale-110 sm:h-10 sm:w-10">
                            <svg
                                class="h-4 w-4 transition-transform duration-300 group-hover:scale-110 sm:h-[18px] sm:w-[18px]"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.8"
                                    d="M5 12h14m-6-6 6 6-6 6"
                                />
                            </svg>
                        </div>
                    </div>
                </a>
            </x-ui.reveal>

        </div>

    </x-ui.container>
</section>