<section class="relative overflow-hidden bg-white">
    {{-- Background --}}
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        {{-- Mobile Background --}}
        <img
            src="{{ asset('images/front/fixbglagi.png') }}"
            alt=""
            class="absolute inset-y-0 right-[-35%] h-full w-[180%] max-w-none object-cover object-right sm:hidden"
        >

        {{-- Desktop Background --}}
        <img
            src="{{ asset('images/front/bg.png') }}"
            alt=""
            class="absolute inset-0 hidden h-full w-full object-cover object-right sm:block"
        >

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-gradient-to-r from-white via-white/90 to-white/20"></div>
    </div>

    {{-- Content --}}
    <div class="relative z-10">
        <x-ui.container>
            <div class="flex min-h-[60vh] items-center py-12 sm:min-h-[90vh] sm:py-16 lg:min-h-[92vh]">
                <div class="max-w-xl lg:max-w-2xl">

                    {{-- Small Label --}}
                    <div class="hero-label mb-3 flex items-center gap-2.5 sm:mb-5 sm:gap-3">
                        <div class="h-px w-6 bg-[#AE7C18] sm:w-10 lg:w-12"></div>

                        <span class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:text-xs lg:tracking-[0.3em]">
                            Engineered For Performance
                        </span>
                    </div>

                    {{-- Main Heading --}}
                    <h1 class="hero-title text-2xl font-extrabold leading-tight text-secondary sm:text-5xl lg:text-7xl">
                        Custom Jerseys
                        <br>
                        <span class="inline italic text-[#AE7C18]">
                            Built for Champions
                        </span>
                    </h1>

                    {{-- Description --}}
                    <p class="hero-description mt-3 max-w-lg text-xs leading-relaxed text-gray-700 sm:mt-6 sm:text-base sm:leading-8 lg:mt-8 lg:max-w-xl lg:text-lg">
                        <span class="block sm:hidden">
                            Premium custom sportswear for teams, schools, companies, and communities.
                        </span>

                        <span class="hidden sm:inline">
                            Premium custom jerseys for football, futsal,
                            basketball, volleyball, cycling, esports,
                            padel, schools, companies, and communities.
                        </span>
                    </p>

                    {{-- Internal Links --}}
                    <div class="hero-buttons mt-5 flex items-center gap-2.5 sm:mt-10 sm:gap-4 lg:mt-12">
                        <x-ui.button
                            :href="route('catalog')"
                            class="!px-4 !py-2.5 !text-xs sm:!px-6 sm:!py-3.5 sm:!text-sm"
                        >
                            Explore Catalog
                        </x-ui.button>

                        <x-ui.button
                            :href="route('contact')"
                            variant="outline"
                            class="!px-4 !py-2.5 !text-xs sm:!px-6 sm:!py-3.5 sm:!text-sm"
                        >
                            Contact Us
                        </x-ui.button>
                    </div>

                </div>
            </div>
        </x-ui.container>
    </div>
</section>