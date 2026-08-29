<section class="bg-gray-50 py-12 sm:py-20 lg:py-28">
    <x-ui.container>
        {{-- Heading --}}
        <x-ui.reveal>
            <div class="mb-8 text-center sm:mb-14 lg:mb-16">
                <div>
                    <p class="mb-2 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mb-3 sm:text-xs lg:tracking-[0.3em]">
                        PRODUCT CATEGORIES
                    </p>

                    <h2 class="text-2xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                        Find Your Style
                    </h2>
                </div>
            </div>
        </x-ui.reveal>

        {{-- Category Grid --}}
        <div class="mx-auto grid max-w-4xl gap-4 sm:gap-8 md:grid-cols-2">

            {{-- Kaos Jersey --}}
            <x-ui.reveal delay="100">
                <div class="relative flex aspect-[16/9] items-center justify-center overflow-hidden rounded-2xl bg-slate-300">
                    <div class="text-center">
                        <span class="block text-2xl font-bold uppercase tracking-[0.2em] text-white sm:text-3xl">
                            Soon
                        </span>

                        <span class="mt-2 block text-[10px] font-medium uppercase tracking-[0.25em] text-slate-100 sm:text-xs">
                            Kaos Jersey
                        </span>
                    </div>
                </div>
            </x-ui.reveal>

            {{-- Kaos Polo --}}
            <x-ui.reveal delay="200">
                <div class="relative flex aspect-[16/9] items-center justify-center overflow-hidden rounded-2xl bg-slate-300">
                    <div class="text-center">
                        <span class="block text-2xl font-bold uppercase tracking-[0.2em] text-white sm:text-3xl">
                            Soon
                        </span>

                        <span class="mt-2 block text-[10px] font-medium uppercase tracking-[0.25em] text-slate-100 sm:text-xs">
                            Kaos Polo
                        </span>
                    </div>
                </div>
            </x-ui.reveal>

        </div>

    </x-ui.container>
</section>