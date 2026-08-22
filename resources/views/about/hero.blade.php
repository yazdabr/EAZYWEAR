<section
    class="relative overflow-hidden border-b bg-gradient-to-r from-[#F8F4EB] via-[#FFFDF8] to-[#F7ECD6]">

    {{-- 1. Full PNG Background Layer (Transparan & Cover) --}}
    <div class="pointer-events-none absolute inset-0 z-0">
        <img 
            src="{{ asset('images/front/bg.png') }}" 
            alt="Hero Background" 
            class="h-full w-full object-cover object-right opacity-15 mix-blend-multiply"
        >
        {{-- Overlay Gradient agar Teks di Kiri Tetap Kontras & Jelas --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#FAF6EF] via-[#FAF6EF]/90 to-transparent"></div>
    </div>

    <x-ui.container>

        <div
            class="relative z-10 flex min-h-0 items-center py-10 sm:py-16 lg:min-h-[55vh] lg:py-28">

            <div class="max-w-2xl">

                {{-- Small Label --}}
                <div
                    class="hero-label mb-3 flex items-center gap-2.5 sm:mb-5 sm:gap-3">

                    <div
                        class="h-px w-6 bg-[#AE7C18] sm:w-10 lg:w-12">
                    </div>

                    <span
                        class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:text-xs lg:tracking-[0.35em]">

                        ESTABLISHED 2016

                    </span>

                </div>

                {{-- Heading --}}
                <h1
                    class="hero-title text-2xl font-extrabold leading-tight text-secondary sm:text-5xl lg:text-6xl">

                    ABOUT

                    <span class="text-[#AE7C18]">

                        EAZYWEAR

                    </span>

                </h1>

                {{-- Description --}}
                <p
                    class="hero-description mt-3 max-w-xl text-xs leading-relaxed text-gray-700 sm:mt-6 sm:text-base sm:leading-8 lg:text-lg">

                    {{-- Ringkas Khusus Mobile --}}
                    <span class="block sm:hidden">
                        Discover the story behind Eazywear Indonesia, crafting premium sportswear trusted nationwide.
                    </span>

                    {{-- Versi Lengkap Desktop --}}
                    <span class="hidden sm:inline">
                        Discover the story behind Eazywear Indonesia,
                        where innovation, premium craftsmanship, and a passion
                        for sportswear come together to create apparel trusted
                        by teams, communities, schools, and businesses across
                        the nation.
                    </span>

                </p>

            </div>

        </div>

    </x-ui.container>

</section>