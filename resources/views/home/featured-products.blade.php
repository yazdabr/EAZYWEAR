<section class="bg-white py-12 sm:py-20 lg:py-28">
    <x-ui.container>
        @php
            $featuredProducts = \App\Models\Product::with([
                'images',
                'variants',
                'category'
            ])
            ->whereIn('id', [43, 45, 46])
            ->get();
        @endphp

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
                    {{-- Ringkas Khusus Mobile --}}
                    <span class="block sm:hidden">
                        Discover our most popular custom apparel, crafted with premium materials and exceptional detail.
                    </span>

                    {{-- Versi Lengkap Desktop --}}
                    <span class="hidden sm:inline">
                        Discover our most popular custom apparel, crafted with premium materials,
                        modern printing technology, and exceptional attention to detail.
                    </span>
                </p>
            </div>
        </x-ui.reveal>

        {{-- Product Grid --}}
        <div class="mx-auto grid max-w-6xl gap-4 sm:gap-8 md:grid-cols-3">
            @foreach($featuredProducts as $product)
                @php
                    $thumbnail = $product->images
                        ->where('is_thumbnail', true)
                        ->sortBy('sort_order')
                        ->first();

                    $image = $thumbnail
                        ? asset('storage/' . $thumbnail->image)
                        : asset('images/products/placeholder.png');

                    $price = $product->variants->min('price') ?? 0;

                    $category = $product->category?->name ?? 'Product';
                @endphp

                <x-ui.reveal :index="$loop->index">
                    <x-website.product-card
                        :title="$product->name"
                        :category="$category"
                        :image="$image"
                        :price="number_format($price, 0, ',', '.')"
                        :href="route('product.detail', ['product' => $product->id])"
                    />
                </x-ui.reveal>
            @endforeach
        </div>
    </x-ui.container>
</section>