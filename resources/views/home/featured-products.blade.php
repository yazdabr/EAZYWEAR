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
                    <span class="block sm:hidden">
                        Our premium collections are coming soon.
                    </span>

                    <span class="hidden sm:inline">
                        Discover our upcoming collection of premium custom apparel,
                        crafted with exceptional attention to detail.
                    </span>
                </p>
            </div>
        </x-ui.reveal>

        {{-- Product Grid --}}
        <div class="mx-auto grid max-w-6xl gap-4 sm:gap-8 md:grid-cols-3">

            {{-- Product 1 --}}
            <x-ui.reveal delay="100">
                <div class="relative flex aspect-[4/5] items-center justify-center overflow-hidden rounded-2xl bg-slate-300">
                    <div class="text-center">
                        <span class="block text-2xl font-bold uppercase tracking-[0.2em] text-white sm:text-3xl">
                            Soon
                        </span>

                        <span class="mt-2 block text-[10px] font-medium uppercase tracking-[0.25em] text-slate-100 sm:text-xs">
                            Premium Collection
                        </span>
                    </div>
                </div>
            </x-ui.reveal>

            {{-- Product 2 --}}
            <x-ui.reveal delay="200">
                <div class="relative flex aspect-[4/5] items-center justify-center overflow-hidden rounded-2xl bg-slate-300">
                    <div class="text-center">
                        <span class="block text-2xl font-bold uppercase tracking-[0.2em] text-white sm:text-3xl">
                            Soon
                        </span>

                        <span class="mt-2 block text-[10px] font-medium uppercase tracking-[0.25em] text-slate-100 sm:text-xs">
                            Premium Collection
                        </span>
                    </div>
                </div>
            </x-ui.reveal>

            {{-- Product 3 --}}
            <x-ui.reveal delay="300">
                <div class="relative flex aspect-[4/5] items-center justify-center overflow-hidden rounded-2xl bg-slate-300">
                    <div class="text-center">
                        <span class="block text-2xl font-bold uppercase tracking-[0.2em] text-white sm:text-3xl">
                            Soon
                        </span>

                        <span class="mt-2 block text-[10px] font-medium uppercase tracking-[0.25em] text-slate-100 sm:text-xs">
                            Premium Collection
                        </span>
                    </div>
                </div>
            </x-ui.reveal>

        </div>
    </x-ui.container>
</section>