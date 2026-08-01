<section
    class="relative overflow-hidden bg-cover bg-center bg-no-repeat"
    style="background-image: url('{{ asset('images/hero/bg.png') }}');">

    {{-- Overlay --}}
    <div
        class="absolute inset-0 bg-gradient-to-r from-white via-white/90 to-white/20">
    </div>

    {{-- Content --}}
    <div class="relative z-10">

        <x-ui.container>

            <div
                class="flex min-h-[85vh] items-center py-16 sm:min-h-[90vh] lg:min-h-[92vh]">

                <div
                    class="max-w-xl lg:max-w-2xl">

                    {{-- Small Label --}}
                    <div
                        class="hero-label mb-5 flex items-center gap-3">

                        <div
                            class="h-px w-10 bg-[#AE7C18] lg:w-12">
                        </div>

                        <span
                            class="text-[10px] font-semibold uppercase tracking-[0.25em] text-[#AE7C18] sm:text-xs lg:tracking-[0.3em]">

                            Engineered For Performance

                        </span>

                    </div>

                    {{-- Heading --}}
                    <h1
                        class="hero-title text-4xl font-extrabold leading-tight text-secondary sm:text-5xl lg:text-7xl">

                        Custom Jerseys

                        <br>

                        <span
                            class="block italic text-[#AE7C18] lg:inline">

                            Built for Champions

                        </span>

                    </h1>

                    {{-- Description --}}
                    <p
                        class="hero-description mt-6 max-w-lg text-base leading-8 text-gray-700 lg:mt-8 lg:max-w-xl lg:text-lg">

                        Premium custom jerseys for football, futsal,
                        basketball, volleyball, cycling, esports,
                        padel, schools, companies, and communities.

                    </p>

                    {{-- Button --}}
                    <div
                        class="hero-buttons mt-10 flex flex-col gap-4 sm:flex-row lg:mt-12">

                        <x-ui.button
                            href="/catalog">

                            Explore Catalog

                        </x-ui.button>

                        <x-ui.button
                            href="/contact"
                            variant="outline">

                            Contact Us

                        </x-ui.button>

                    </div>

                </div>

            </div>

        </x-ui.container>

    </div>

</section>