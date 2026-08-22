<section class="bg-white py-14">
    <x-ui.container>

        {{-- MOBILE --}}
        <div class="block lg:hidden">
            <div class="grid grid-cols-2 gap-5">

                @forelse($products as $product)

                    <x-ui.reveal :index="floor($loop->index / 2)">

                        <x-catalog.product-card
                            :product="$product"
                        />

                    </x-ui.reveal>

                @empty

                    <div class="col-span-2 py-16 text-center">
                        <p class="text-gray-500">
                            No products found.
                        </p>
                    </div>

                @endforelse

            </div>
        </div>

        {{-- DESKTOP --}}
        <div class="hidden lg:block">
            <div class="grid grid-cols-3 gap-8 xl:grid-cols-4">

                @forelse($products as $product)

                    <x-ui.reveal :index="$loop->index">

                        <x-catalog.product-card
                            :product="$product"
                        />

                    </x-ui.reveal>

                @empty

                    <div class="col-span-full py-16 text-center">
                        <p class="text-gray-500">
                            No products found.
                        </p>
                    </div>

                @endforelse

            </div>
        </div>

    </x-ui.container>
</section>