@php
    $images = $product->gallery_images;

    $imageUrls = $images->map(function ($image) {
        return asset('storage/' . $image->image);
    })->values()->all();

    if (empty($imageUrls)) {
        $imageUrls = [
            asset('images/products/placeholder.png')
        ];
    }

    $startingPrice = $product->starting_price ?? 0;

    $whatsappMessage = 'Halo Eazywear, saya ingin bertanya tentang produk ' . $product->name . '.';

    $whatsappUrl = 'https://wa.me/6285754431105?text=' . urlencode($whatsappMessage);
@endphp

<section
    x-data="galleryProduct()"
    class="bg-white py-14"
>
    <x-ui.container>
        <div class="grid gap-16 lg:grid-cols-2">

            {{-- ================= GALLERY ================= --}}
            <div>

                <div class="overflow-hidden rounded-3xl shadow-xl">
                    <img
                        :src="currentImage"
                        alt="{{ $product->name }}"
                        class="aspect-square w-full object-cover transition duration-500"
                    >
                </div>

                @if(count($imageUrls) > 1)
                    <div class="mt-5 flex flex-wrap gap-4">
                        <template x-for="image in images" :key="image">
                            <button
                                type="button"
                                @click="currentImage = image"
                                class="overflow-hidden rounded-xl border-2 transition"
                                :class="currentImage === image
                                    ? 'border-[#AE7C18]'
                                    : 'border-gray-200 hover:border-[#AE7C18]'"
                            >
                                <img
                                    :src="image"
                                    alt="{{ $product->name }}"
                                    class="h-20 w-20 object-cover sm:h-24 sm:w-24"
                                >
                            </button>
                        </template>
                    </div>
                @endif

                {{-- ================= WHATSAPP MOBILE ================= --}}
                <div class="mt-6 lg:hidden">
                    <a
                        href="{{ $whatsappUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex w-full items-center justify-center gap-3 rounded-full border-2 border-[#AE7C18] px-6 py-4 text-lg font-semibold text-[#AE7C18] transition-all duration-300 hover:bg-[#AE7C18] hover:text-white hover:shadow-lg"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path d="M20.52 3.48A11.86 11.86 0 0012.05 0C5.5 0 .17 5.33.17 11.88c0 2.1.55 4.15 1.59 5.96L0 24l6.35-1.67a11.88 11.88 0 005.7 1.45h.01c6.55 0 11.88-5.33 11.88-11.88 0-3.17-1.23-6.15-3.42-8.42zm-8.47 18.3h-.01a9.87 9.87 0 01-5.03-1.38l-.36-.21-3.77.99 1.01-3.67-.23-.38a9.84 9.84 0 01-1.51-5.25c0-5.45 4.44-9.88 9.9-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 012.9 6.98c0 5.46-4.44 9.9-9.89 9.9zm5.43-7.39c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.66.15-.2.3-.76.97-.93 1.17-.17.2-.35.22-.65.07-.3-.15-1.27-.47-2.42-1.5-.9-.8-1.5-1.8-1.68-2.1-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.48-.5-.66-.5h-.56c-.2 0-.52.08-.8.37-.27.3-1.04 1.02-1.04 2.5s1.07 2.9 1.22 3.1c.15.2 2.1 3.2 5.08 4.49.71.3 1.27.48 1.7.62.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.42.25-.69.25-1.28.17-1.42-.08-.13-.27-.2-.57-.35z"/>
                        </svg>

                        <span>Tanyakan Produk</span>
                    </a>
                </div>
            </div>

            {{-- ================= PRODUCT INFO ================= --}}
            <div>

                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">
                    {{ $product->category?->name ?? 'PRODUCT' }}
                    @if($product->material)
                        // {{ $product->material }}
                    @endif
                </p>

                <h1 class="mt-3 text-4xl font-bold sm:text-5xl">
                    {{ $product->name }}
                </h1>

                <h2 class="mt-4 text-3xl font-bold text-[#AE7C18] sm:text-4xl">
                    Starting from Rp {{ number_format($startingPrice, 0, ',', '.') }}
                </h2>

                @if($product->description)
                    <p class="mt-8 text-lg leading-8 text-gray-600">
                        {{ $product->description }}
                    </p>
                @endif

                {{-- ================= SIZE ================= --}}
                @if(count($product->available_sizes))
                    <div
                        class="mt-10"
                        x-data="{
                            selectedSize: {{ $product->available_sizes[0]['size_id'] ?? 'null' }}
                        }"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="font-semibold uppercase">
                                Available Sizes
                            </h3>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @foreach($product->available_sizes as $size)
                                <button
                                    type="button"
                                    @click="selectedSize = {{ $size['size_id'] }}"
                                    class="h-11 min-w-[54px] rounded-full border px-4 transition"
                                    :class="selectedSize === {{ $size['size_id'] }}
                                        ? 'border-[#AE7C18] bg-[#AE7C18] text-white'
                                        : 'border-gray-300 hover:border-[#AE7C18]'"
                                >
                                    {{ $size['name'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- ================= PRODUCT FEATURES ================= --}}
                <div class="mt-10 grid gap-4 sm:grid-cols-2">

                    <div class="rounded-2xl bg-[#AE7C18] p-5 text-white">
                        <h4 class="font-semibold">
                            {{ $product->material ?: 'Premium Material' }}
                        </h4>

                        <p class="mt-2 text-sm">
                            Premium quality material for comfortable use.
                        </p>
                    </div>

                    <div class="rounded-2xl bg-[#AE7C18] p-5 text-white">
                        <h4 class="font-semibold">
                            Production Time
                        </h4>

                        <p class="mt-2 text-sm">
                            10–14 Working Days
                        </p>
                    </div>

                </div>

                {{-- ================= WHATSAPP DESKTOP ================= --}}
                <div class="mt-10 hidden lg:block">
                    <a
                        href="{{ $whatsappUrl }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex w-full items-center justify-center gap-3 rounded-full border-2 border-[#AE7C18] px-6 py-4 text-lg font-semibold text-[#AE7C18] transition-all duration-300 hover:bg-[#AE7C18] hover:text-white hover:shadow-lg"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6"
                            fill="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path d="M20.52 3.48A11.86 11.86 0 0012.05 0C5.5 0 .17 5.33.17 11.88c0 2.1.55 4.15 1.59 5.96L0 24l6.35-1.67a11.88 11.88 0 005.7 1.45h.01c6.55 0 11.88-5.33 11.88-11.88 0-3.17-1.23-6.15-3.42-8.42zm-8.47 18.3h-.01a9.87 9.87 0 01-5.03-1.38l-.36-.21-3.77.99 1.01-3.67-.23-.38a9.84 9.84 0 01-1.51-5.25c0-5.45 4.44-9.88 9.9-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 012.9 6.98c0 5.46-4.44 9.9-9.89 9.9zm5.43-7.39c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.66.15-.2.3-.76.97-.93 1.17-.17.2-.35.22-.65.07-.3-.15-1.27-.47-2.42-1.5-.9-.8-1.5-1.8-1.68-2.1-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.48-.5-.66-.5h-.56c-.2 0-.52.08-.8.37-.27.3-1.04 1.02-1.04 2.5s1.07 2.9 1.22 3.1c.15.2 2.1 3.2 5.08 4.49.71.3 1.27.48 1.7.62.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.42.25-.69.25-1.28.17-1.42-.08-.13-.27-.2-.57-.35z"/>
                        </svg>

                        <span>Tanyakan Produk</span>
                    </a>
                </div>

            </div>
        </div>
    </x-ui.container>
</section>

<script>
function galleryProduct() {
    return {
        images: @js($imageUrls),
        currentImage: @js($imageUrls[0] ?? asset('images/products/placeholder.png')),
    }
}
</script>