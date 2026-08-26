<section class="relative overflow-hidden border-b border-amber-900/10 bg-[#FAF6EF]">

    {{-- 1. Full PNG Background Layer (Transparan & Cover) --}}
    <div class="pointer-events-none absolute inset-0 z-0">
        <img 
            src="{{ asset('images/front/fixbglagi.png') }}" 
            alt="Hero Background" 
            class="h-full w-full object-cover object-right opacity-15 mix-blend-multiply"
        >
        {{-- Overlay Gradient agar Teks di Kiri Tetap Kontras & Jelas --}}
        <div class="absolute inset-0 bg-gradient-to-r from-[#FAF6EF] via-[#FAF6EF]/90 to-transparent"></div>
    </div>

    {{-- 3. Ambient Background Glows --}}
    <div class="pointer-events-none absolute -left-20 top-10 h-64 w-64 rounded-full bg-[#AE7C18]/15 blur-3xl"></div>
    <div class="pointer-events-none absolute right-0 top-0 h-80 w-80 rounded-full bg-[#AE7C18]/10 blur-3xl"></div>

    {{-- 4. Hero Content --}}
    <x-ui.container>
        <div class="relative z-10 flex min-h-0 items-center py-10 sm:py-16 lg:min-h-[55vh] lg:py-28">
            <div class="max-w-2xl">

                {{-- Small Label --}}
                <div class="hero-label mb-3 flex items-center gap-2.5 sm:mb-5 sm:gap-3">
                    <div class="h-px w-6 bg-[#AE7C18] sm:w-10 lg:w-12"></div>
                    <span class="text-[9px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:text-xs lg:tracking-[0.35em]">
                        THE EQUIPMENT OF EXCELLENCE
                    </span>
                </div>

                {{-- Heading --}}
                <h1 class="hero-title text-2xl font-extrabold leading-tight text-slate-900 sm:text-5xl lg:text-6xl">
                    CATALOG
                </h1>

                {{-- Description --}}
                <p class="hero-description mt-3 max-w-xl text-xs leading-relaxed text-slate-700 sm:mt-6 sm:text-base sm:leading-8 lg:text-lg">
                    {{-- Ringkas Khusus Mobile --}}
                    <span class="block sm:hidden">
                        Explore our custom sportswear collection engineered for performance and excellence.
                    </span>

                    {{-- Versi Lengkap Desktop --}}
                    <span class="hidden sm:inline">
                        Browse our premium custom sportswear collection,
                        engineered for the technical pursuit of excellence
                        in every discipline.
                    </span>
                </p>

            </div>
        </div>
    </x-ui.container>

</section>