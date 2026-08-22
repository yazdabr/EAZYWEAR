<section
    class="relative overflow-hidden bg-cover bg-center bg-no-repeat py-12 sm:py-20 lg:py-28"
    style="background-image: url('{{ asset('images/cta/bg.png') }}');">

    {{-- Backdrop overlay tipis untuk kontras teks di mobile --}}
    <div class="absolute inset-0 bg-white/40 backdrop-blur-[2px] sm:bg-transparent sm:backdrop-blur-none"></div>

    <div class="relative z-10">

        <x-ui.container>

            <x-ui.reveal animation="scale">

                <div class="mx-auto max-w-4xl text-center text-gray-900">

                    {{-- Label --}}
                    <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mb-5 sm:text-xs sm:tracking-[0.3em]">
                        READY TO START?
                    </p>

                    {{-- Heading --}}
                    <h2 class="text-2xl font-bold leading-tight sm:text-4xl lg:text-6xl">
                        Let's Create Your
                        <span class="italic text-[#AE7C18]">
                            Dream Jersey
                        </span>
                    </h2>

                    {{-- Description --}}
                    <p class="mx-auto mt-3 max-w-2xl text-xs leading-relaxed text-gray-800 sm:mt-8 sm:text-lg sm:leading-8">
                        Whether you're building a professional team,
                        representing your community, or creating apparel
                        for your company, Eazywear is ready to help turn
                        your ideas into premium-quality custom sportswear.
                    </p>

                    {{-- Buttons Wrapper --}}
                    <div class="mt-6 flex flex-col justify-center gap-2.5 sm:mt-12 sm:flex-row sm:gap-5">

                        <x-ui.button
                            :href="route('catalog')"
                            class="w-full sm:w-auto">
                            Browse Catalog
                        </x-ui.button>

                        <x-ui.button
                            href="https://wa.me/6285754431105"
                            target="_blank"
                            rel="noopener noreferrer"
                            variant="outline"
                            class="w-full sm:w-auto">
                            Contact Us  
                        </x-ui.button>

                    </div>

                </div>

            </x-ui.reveal>

        </x-ui.container>

    </div>

</section>