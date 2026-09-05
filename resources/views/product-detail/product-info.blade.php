@php
    $images = $product->gallery_images;
    $imageUrls = $images->map(function ($image) {
        return asset('storage/' . $image->image);
    })->values()->all();

    if (empty($imageUrls)) {
        $imageUrls = [asset('images/products/placeholder.png')];
    }

    $startingPrice = $product->starting_price ?? 0;
    $whatsappMessage = 'Halo Eazywear, saya tertarik dengan produk ' . $product->name . '. Saya ingin menanyakan harga dan detail produk.';
    $whatsappUrl = 'https://wa.me/6285754431105?text=' . urlencode($whatsappMessage);
@endphp

<section x-data="galleryProduct()" class="bg-white py-6 sm:py-10 lg:py-14">
    <x-ui.container>
        <div class="grid gap-6 sm:gap-10 lg:grid-cols-2 lg:gap-16">
            {{-- ================= GALLERY ================= --}}
            <div>
                <div class="overflow-hidden rounded-2xl shadow-md sm:rounded-3xl sm:shadow-xl">
                    <img id="main-product-image" :src="currentImage" alt="{{ $product->name }}" class="aspect-square w-full object-cover transition duration-500">
                </div>
                @if(count($imageUrls)>1)
                    <div class="mt-3 grid grid-cols-4 gap-2.5 sm:mt-5 sm:gap-4">
                        <template x-for="(image,index) in images.slice(1,5)" :key="image">
                            <button type="button" @click="currentImage=image" class="overflow-hidden rounded-lg border-2 transition sm:rounded-xl" :class="currentImage===image?'border-[#AE7C18]':'border-gray-200 hover:border-[#AE7C18]'">
                                <img :src="image" alt="{{ $product->name }}" width="200" height="200" loading="lazy" decoding="async" class="aspect-square w-full object-cover">
                            </button>
                        </template>
                    </div>
                @endif
            </div>

            {{-- PRODUCT INFO --}}
            <div class="flex h-full flex-col">
                {{-- PRODUCT HEADER --}}
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:text-xs sm:tracking-[0.3em]">
                        {{ $product->category?->name ?? 'PRODUCT' }}
                    </p>

                    <h1 class="mt-3 text-2xl font-bold leading-none tracking-tight text-slate-900 sm:mt-4 sm:text-4xl sm:leading-tight lg:text-5xl">
                        {{ $product->name }}
                    </h1>

                    <h2 class="mt-1 text-xl font-bold leading-none text-[#AE7C18] sm:mt-3 sm:text-3xl lg:text-4xl">
                        Starting from Rp {{ number_format($startingPrice, 0, ',', '.') }}
                    </h2>

                    @if($product->description)
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-600 sm:mt-4 sm:text-base lg:text-lg lg:leading-7">
                            {{ $product->description }}
                        </p>
                    @endif
                </div>

                {{-- SIZE / VARIANT --}}
                @if(count($product->available_sizes))
                    <div
                        class="mt-4 sm:mt-7"
                        x-data="{
                            selectedVariant: {{ $product->available_sizes[0]['id'] ?? 'null' }},
                            selectedPrice: {{ $product->available_sizes[0]['price'] ?? 0 }},
                            selectedStock: {{ $product->available_sizes[0]['stock'] ?? 0 }}
                        }"
                    >
                        <div class="mb-2 flex items-center justify-between sm:mb-3">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-900 sm:text-sm">
                                Available Sizes
                            </h3>

                            <span
                                class="text-xs text-gray-500 sm:text-sm"
                                x-show="selectedStock > 0"
                            >
                                Stock:
                                <span
                                    x-text="selectedStock"
                                    class="font-semibold text-slate-800"
                                ></span>
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-2 sm:gap-2.5">
                            @foreach($product->available_sizes as $size)
                                <button
                                    type="button"
                                    @click="
                                        selectedVariant = {{ $size['id'] }};
                                        selectedPrice = {{ $size['price'] }};
                                        selectedStock = {{ $size['stock'] }};
                                    "
                                    class="h-9 min-w-[44px] rounded-full border px-3 text-xs transition sm:h-10 sm:min-w-[50px] sm:px-4 sm:text-sm"
                                    x-bind:class="selectedVariant === {{ $size['id'] }}
                                        ? 'border-[#AE7C18] bg-[#AE7C18] text-white'
                                        : 'border-gray-300 hover:border-[#AE7C18]'"
                                >
                                    {{ $size['name'] }}
                                </button>
                            @endforeach
                        </div>

                        <div class="mt-2.5 sm:mt-3">
                            <p class="text-xs text-gray-500 sm:text-sm">
                                Selected price
                            </p>

                            <p
                                class="text-xl font-bold text-[#AE7C18] sm:text-2xl"
                                x-text="'Rp ' + Number(selectedPrice).toLocaleString('id-ID')"
                            ></p>
                        </div>

                        {{-- ================= ADD TO CART - DINONAKTIFKAN SEMENTARA ================= --}}
                        {{--
                        <form
                            method="POST"
                            action="{{ route('cart.add') }}"
                            class="mt-4 sm:mt-5"
                            @submit.prevent="addToCartAnimation($event)"
                        >
                            @csrf

                            <input
                                type="hidden"
                                name="variant_id"
                                x-model="selectedVariant"
                            >

                            <input
                                type="hidden"
                                name="qty"
                                value="1"
                            >

                            <button
                                type="submit"
                                x-bind:disabled="selectedStock <= 0"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-800 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-gray-300 disabled:shadow-none sm:gap-3 sm:px-6 sm:py-3.5 sm:text-base"
                            >
                                <x-heroicon-o-shopping-cart class="h-5 w-5"/>

                                <span x-show="selectedStock > 0">
                                    Add to Cart
                                </span>

                                <span x-show="selectedStock <= 0">
                                    Out of Stock
                                </span>
                            </button>
                        </form>
                        --}}

                        {{-- ================= TANYAKAN PRODUK ================= --}}
                        <a
                            href="{{ $whatsappUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-full bg-[#AE7C18] px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] active:scale-[0.98] sm:mt-5 sm:gap-3 sm:px-6 sm:py-3.5 sm:text-base"
                        >
                            <x-heroicon-o-chat-bubble-left-right class="h-5 w-5"/>
                            <span>Tanyakan Produk</span>
                        </a>
                    </div>
                @else
                    <div class="mt-5 rounded-xl bg-gray-100 p-4 text-center sm:mt-7 sm:rounded-2xl sm:p-5">
                        <p class="text-xs font-semibold text-gray-600 sm:text-base">
                            Product currently unavailable.
                        </p>
                    </div>
                @endif

                {{-- PRODUCT FEATURES --}}
                <div class="mt-auto pt-5 sm:pt-7">
                    <div class="grid grid-cols-2 gap-3 sm:gap-4">
                        <div class="rounded-xl bg-[#AE7C18] p-3.5 text-white sm:rounded-2xl sm:p-4">
                            <h4 class="text-xs font-semibold sm:text-base">
                                {{ $product->material ?: 'Premium Material' }}
                            </h4>

                            <p class="mt-1 text-[10px] leading-4 opacity-90 sm:mt-1.5 sm:text-sm sm:leading-5">
                                Premium quality material for comfortable use.
                            </p>
                        </div>

                        <div class="rounded-xl bg-[#AE7C18] p-3.5 text-white sm:rounded-2xl sm:p-4">
                            <h4 class="text-xs font-semibold sm:text-base">
                                Production Time
                            </h4>

                            <p class="mt-1 text-[10px] leading-4 opacity-90 sm:mt-1.5 sm:text-sm sm:leading-5">
                                10–14 Working Days
                            </p>
                        </div>
                    </div>
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

        addToCartAnimation(event) {
            const form = event.target;
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const cartCandidates = [
                document.getElementById('navbar-cart'),
                document.querySelector('[aria-label="Cart"]'),
                document.querySelector('[aria-label="Keranjang"]')
            ];

            const cart = cartCandidates.find((element) => {
                if (!element) return false;
                const rect = element.getBoundingClientRect();
                const style = window.getComputedStyle(element);
                return (
                    rect.width > 0 &&
                    rect.height > 0 &&
                    style.display !== 'none' &&
                    style.visibility !== 'hidden'
                );
            });

            if (!cart) {
                HTMLFormElement.prototype.submit.call(form);
                return;
            }

            const button = form.querySelector('button[type="submit"]');
            if (!button) {
                HTMLFormElement.prototype.submit.call(form);
                return;
            }

            const buttonRect = button.getBoundingClientRect();
            const cartRect = cart.getBoundingClientRect();

            const startX = buttonRect.left + (buttonRect.width / 2);
            const startY = buttonRect.top + (buttonRect.height / 2);

            const endX = cartRect.left + (cartRect.width / 2);
            const endY = cartRect.top + (cartRect.height / 2);

            const deltaX = endX - startX;
            const deltaY = endY - startY;

            const dot = document.createElement('div');
            dot.style.position = 'fixed';
            dot.style.left = `${startX - 11}px`;
            dot.style.top = `${startY - 11}px`;
            dot.style.width = '22px';
            dot.style.height = '22px';
            dot.style.borderRadius = '9999px';
            dot.style.backgroundColor = '#0F172A'; // Dark slate matching the button
            dot.style.boxShadow = '0 4px 16px rgba(15, 23, 42, 0.40), 0 0 0 5px rgba(15, 23, 42, 0.10)';
            dot.style.zIndex = '999999';
            dot.style.pointerEvents = 'none';

            document.body.appendChild(dot);

            const animation = dot.animate(
                [
                    { transform: 'translate3d(0, 0, 0) scale(1)', opacity: 1 },
                    { transform: `translate3d(${deltaX * 0.45}px, ${deltaY * 0.45}px, 0) scale(1.15)`, opacity: 1 },
                    { transform: `translate3d(${deltaX * 0.80}px, ${deltaY * 0.80}px, 0) scale(0.95)`, opacity: 0.95 },
                    { transform: `translate3d(${deltaX}px, ${deltaY}px, 0) scale(0.45)`, opacity: 0.15 }
                ],
                {
                    duration: 1000,
                    easing: 'cubic-bezier(0.22, 1, 0.36, 1)',
                    fill: 'forwards'
                }
            );

            setTimeout(() => {
                cart.animate(
                    [
                        { transform: 'scale(1)' },
                        { transform: 'scale(1.12)' },
                        { transform: 'scale(0.97)' },
                        { transform: 'scale(1.04)' },
                        { transform: 'scale(1)' }
                    ],
                    { duration: 420, easing: 'ease-out' }
                );
            }, 820);

            setTimeout(() => {
                dot.remove();
                HTMLFormElement.prototype.submit.call(form);
            }, 1050);
        }
    }
}
</script>