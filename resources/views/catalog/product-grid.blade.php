<section class="bg-white py-14">
    <x-ui.container>

        {{-- MOBILE --}}
        <div class="block lg:hidden">
            <div class="grid grid-cols-2 gap-5">

                @forelse($products as $product)
                    <x-ui.reveal :index="floor($loop->index / 2)">
                        <x-catalog.product-card :product="$product" />
                    </x-ui.reveal>
                @empty
                    <div class="col-span-2 py-20 text-center">
                        <div class="mx-auto max-w-xl">

                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#AE7C18]">
                                Eazywear Catalog
                            </p>

                            <h2 class="mt-3 text-2xl font-bold text-slate-900">
                                Our collection is coming soon.
                            </h2>

                            <p class="mt-4 text-sm leading-7 text-slate-500">
                                We are currently preparing our custom sportswear collection.
                                Contact us for custom teamwear, jerseys, and apparel.
                            </p>

                            <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                                <a
                                    href="{{ route('home') }}"
                                    class="inline-flex items-center rounded-full border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#AE7C18] hover:text-[#AE7C18]"
                                >
                                    Back to Home
                                </a>

                                <a
                                    href="{{ route('contact') }}"
                                    class="inline-flex items-center rounded-full bg-[#AE7C18] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#96690F]"
                                >
                                    Contact Eazywear
                                </a>
                            </div>

                        </div>
                    </div>
                @endforelse

            </div>
        </div>

        {{-- DESKTOP --}}
        <div class="hidden lg:block">
            <div class="grid grid-cols-3 gap-8 xl:grid-cols-4">

                @forelse($products as $product)
                    <x-ui.reveal :index="$loop->index">
                        <x-catalog.product-card :product="$product" />
                    </x-ui.reveal>
                @empty
                    <div class="col-span-full py-20 text-center">
                        <div class="mx-auto max-w-xl">

                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#AE7C18]">
                                Eazywear Catalog
                            </p>

                            <h2 class="mt-3 text-2xl font-bold text-slate-900">
                                Our collection is coming soon.
                            </h2>

                            <p class="mt-4 text-sm leading-7 text-slate-500">
                                We are currently preparing our custom sportswear collection.
                                Contact us for custom teamwear, jerseys, and apparel.
                            </p>

                            <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                                <a
                                    href="{{ route('home') }}"
                                    class="inline-flex items-center rounded-full border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:border-[#AE7C18] hover:text-[#AE7C18]"
                                >
                                    Back to Home
                                </a>

                                <a
                                    href="{{ route('contact') }}"
                                    class="inline-flex items-center rounded-full bg-[#AE7C18] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#96690F]"
                                >
                                    Contact Eazywear
                                </a>
                            </div>

                        </div>
                    </div>
                @endforelse

            </div>
        </div>

    </x-ui.container>
</section>