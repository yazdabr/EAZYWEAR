<section class="bg-white py-28">

    <x-ui.container>

        {{-- Heading --}}
        <x-ui.reveal>

            <div class="mb-16 text-center">

                <p
                    class="mb-4 font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">

                    FEATURED PRODUCTS

                </p>

                <h2
                    class="text-4xl font-bold lg:text-5xl">

                    Our Best Collections

                </h2>

                <p
                    class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-gray-600">

                    Discover our most popular custom sportswear, crafted with premium
                    materials, modern printing technology, and exceptional attention
                    to detail.

                </p>

            </div>

        </x-ui.reveal>

        {{-- Product Grid --}}
        <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">

            {{-- Product 1 --}}
            <x-ui.reveal
                :index="0">

                <x-website.product-card
                    title="Football Jersey"
                    category="Jersey"
                    image="images/products/adsy.png"
                    price="149.000"
                    href="{{ route('product.detail', ['product' => 1]) }}" />

            </x-ui.reveal>

            {{-- Product 2 --}}
            <x-ui.reveal
                :index="1">

                <x-website.product-card
                    title="Training Hoodie"
                    category="Jacket"
                    image="images/products/fortis.png"
                    price="189.000"
                    href="{{ route('product.detail', ['product' => 2]) }}" />

            </x-ui.reveal>

            {{-- Product 3 --}}
            <x-ui.reveal
                :index="2">

                <x-website.product-card
                    title="Basketball Jersey"
                    category="Jersey"
                    image="images/products/sujud.png"
                    price="159.000"
                    href="{{ route('product.detail', ['product' => 3]) }}" />

            </x-ui.reveal>

        </div>

    </x-ui.container>

</section>