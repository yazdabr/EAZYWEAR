@php
    $images = $product->gallery_images;
    $imageUrls = $images->map(function ($image) {
        return asset('storage/' . $image->image);
    })->values()->all();
    if (empty($imageUrls)) {
        $imageUrls = [asset('images/products/placeholder.png')];
    }
    $startingPrice = $product->variants
        ->filter(fn ($variant) => (float) $variant->price > 0)
        ->min('price') ?? 0;
    $whatsappMessage = 'Halo Eazywear, saya ingin bertanya mengenai jersey dan informasi lebih lanjut.';
    $whatsappUrl = 'https://wa.me/628138377763?text=' . urlencode($whatsappMessage);
    // Urutan size dari terkecil → terbesar
    $sizeOrder = [
        'XXXS' => 1, 'XXS' => 2, 'XS' => 3, 'S' => 4, 'M' => 5,
        'L' => 6, 'XL' => 7, 'XXL' => 8, '2XL' => 8, 'XXXL' => 9,
        '3XL' => 9, 'XXXXL' => 10, '4XL' => 10, '5XL' => 11,
    ];
    $availableSizes = collect($product->available_sizes)
        ->sortBy(function ($size) use ($sizeOrder) {
            $name = strtoupper(trim($size['name'] ?? ''));
            return $sizeOrder[$name] ?? 999;
        })
        ->values()
        ->all();
@endphp
<section x-data="galleryProduct()" class="bg-white py-6 sm:py-10 lg:py-14">
    <x-ui.container>
        <div class="grid items-start gap-6 sm:gap-10 lg:grid-cols-2 lg:gap-16">
            {{-- *GALLERY* --}}
            <div class="w-full min-w-0">
                <div class="w-full">
                    {{-- *DESKTOP* --}}
                    <div class="hidden lg:grid lg:grid-cols-[minmax(0,1fr)_164px] lg:items-start lg:gap-3">
                        {{-- *Main Image* --}}
                        <div class="relative aspect-[3/4] w-full overflow-hidden rounded-2xl bg-slate-50 shadow-md sm:rounded-3xl sm:shadow-xl">
                            <img id="main-product-image" :src="currentImage" alt="{{ $product->name }}" class="absolute inset-0 h-full w-full object-contain transition duration-500">
                            <div class="absolute bottom-3 right-3 rounded-full bg-black/60 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur-sm">
                                <span x-text="images.indexOf(currentImage) + 1"></span>/<span x-text="images.length"></span>
                            </div>
                        </div>
                        {{-- *Thumbnails Gallery* --}}
                        @if(count($imageUrls) > 1)
                            <div class="max-h-[calc(100%)] w-full overflow-y-auto pr-1 [scrollbar-width:thin]">
                                <div class="grid grid-cols-2 gap-2">
                                    <template x-for="(image,index) in images" :key="image">
                                        <button type="button" @click="currentImage=image" class="group relative aspect-3/4 w-full overflow-hidden rounded-xl border-2 bg-slate-50 transition" :class="currentImage===image ? 'border-[#AE7C18] ring-2 ring-[#AE7C18]/15' : 'border-slate-200 hover:border-[#AE7C18]'">
                                            <img :src="image" alt="{{ $product->name }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
                                            <span class="absolute bottom-1.5 left-1.5 flex h-5 min-w-5 items-center justify-center rounded-md bg-black/65 px-1 text-[9px] font-bold text-white backdrop-blur-sm" x-text="index + 1"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        @endif
                    </div>
                    {{-- *MOBILE* --}}
                    <div class="lg:hidden">
                        <div class="relative aspect-[3/4] w-full overflow-hidden rounded-2xl bg-slate-50 shadow-md sm:rounded-3xl sm:shadow-xl">
                            <img id="main-product-image" :src="currentImage" alt="{{ $product->name }}" class="absolute inset-0 h-full w-full object-contain transition duration-500">
                            <div class="absolute bottom-2.5 right-2.5 rounded-full bg-black/60 px-2.5 py-1 text-[10px] font-semibold text-white backdrop-blur-sm sm:bottom-4 sm:right-4 sm:px-3 sm:py-1.5 sm:text-xs">
                                <span x-text="images.indexOf(currentImage) + 1"></span>/<span x-text="images.length"></span>
                            </div>
                        </div>
                        @if(count($imageUrls) > 1)
                            <div class="mt-3 w-full min-w-0 overflow-hidden">
                                <div class="flex w-full max-w-full snap-x snap-mandatory gap-2 overflow-x-auto overscroll-x-contain pb-1 [scrollbar-width:none] [-ms-overflow-style:none] sm:gap-2.5">
                                    <template x-for="(image,index) in images.slice(0,10)" :key="image">
                                        <button type="button" @click="currentImage=image" class="group relative aspect-[3/4] w-[58px] shrink-0 snap-start overflow-hidden rounded-lg border-2 bg-slate-50 transition sm:w-[68px] sm:rounded-xl" :class="currentImage===image ? 'border-[#AE7C18] ring-2 ring-[#AE7C18]/15' : 'border-slate-200 hover:border-[#AE7C18]'">
                                            <img :src="image" alt="{{ $product->name }}" width="162" height="216" loading="lazy" decoding="async" class="h-full w-full object-contain transition duration-300 group-hover:scale-[1.03]">
                                            <span class="absolute bottom-1 left-1 flex h-5 min-w-5 items-center justify-center rounded-md bg-black/65 px-1 text-[9px] font-bold text-white backdrop-blur-sm" x-text="index + 1"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            {{-- PRODUCT INFO --}}
            <div class="flex h-full min-h-0 flex-col">
                {{-- PRODUCT HEADER --}}
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:text-xs sm:tracking-[0.3em]">{{ $product->category?->name ?? 'PRODUCT' }}</p>
                    <h1 class="mt-3 text-2xl font-bold leading-none tracking-tight text-slate-900 sm:mt-4 sm:text-4xl sm:leading-tight lg:text-5xl">{{ $product->name }}</h1>
                    <h2 class="mt-1 text-xl font-bold leading-none text-[#AE7C18] sm:mt-3 sm:text-3xl lg:text-4xl">Starting from Rp {{ number_format($startingPrice, 0, ',', '.') }}</h2>
                    @if($product->description)
                        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-gray-600 sm:mt-4 sm:text-base lg:text-lg lg:leading-7">{{ $product->description }}</p>
                    @endif
                </div>
                {{-- SIZE / VARIANT --}}
                @if(count($availableSizes))
                    <div class="mt-4 sm:mt-7" x-data="{
                        selectedVariant: {{ $availableSizes[0]['id'] ?? 'null' }},
                        selectedPrice: {{ $availableSizes[0]['price'] ?? 0 }},
                        selectedStock: {{ $availableSizes[0]['stock'] ?? 0 }},
                        customName: '',
                        customNumber: '',
                        customizationEnabled: @js((bool) $product->customization_enabled),
                        customizationPrice: @js((int) $product->customization_price),
                        longsleeveEnabled: @js((bool) $product->longsleeve_enabled),
                        longsleevePrice: @js((int) $product->longsleeve_price),
                        isLongsleeve: false,
                        patchEnabled: @js((bool) $product->patch_enabled),
                        patchPrice: @js((int) $product->patch_price),
                        isPatch: false,
                        get patchFee() {
                            return this.patchEnabled && this.isPatch ? Number(this.patchPrice) || 0 : 0;
                        },
                        get longsleeveFee() {
                            return this.longsleeveEnabled && this.isLongsleeve ? Number(this.longsleevePrice) || 0 : 0;
                        },
                        get hasCustomization() {
                            return this.customName.trim() !== '' || this.customNumber.trim() !== '';
                        },
                        get customizationFee() {
                            if (!this.customizationEnabled || !this.hasCustomization) return 0;
                            return Number(this.customizationPrice) || 0;
                        },
                        get finalPrice() {
                            return Number(this.selectedPrice) + this.customizationFee + this.longsleeveFee + this.patchFee;
                        }
                    }">
                        <div class="mb-2 flex items-center justify-between sm:mb-3">
                            <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-900 sm:text-sm">Available Sizes</h3>
                            <span class="text-xs text-gray-500 sm:text-sm" x-show="selectedStock > 0">Stock: Pre-Order</span>
                        </div>
                        <div class="flex flex-wrap gap-2 sm:gap-2.5">
                            @foreach($availableSizes as $size)
                                <button type="button" @click="selectedVariant = {{ $size['id'] }}; selectedPrice = {{ $size['price'] }}; selectedStock = {{ $size['stock'] }};" class="h-9 min-w-[44px] rounded-full border px-3 text-xs transition sm:h-10 sm:min-w-[50px] sm:px-4 sm:text-sm" x-bind:class="selectedVariant === {{ $size['id'] }} ? 'border-[#AE7C18] bg-[#AE7C18] text-white' : 'border-gray-300 hover:border-[#AE7C18]'">{{ $size['name'] }}</button>
                            @endforeach
                        </div>
                        {{-- *JERSEY CUSTOMIZATION* --}}
                        <div x-show="customizationEnabled" x-cloak class="mt-5 sm:mt-6">
                            {{-- *CUSTOMIZATION NOTICE* --}}
                            <div class="mb-4 rounded-2xl border border-[#AE7C18]/20 bg-[#AE7C18]/5 p-3.5 sm:p-4">
                                <div class="flex items-start gap-2.5">
                                    <div class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-[#AE7C18]/10 text-[#AE7C18]">
                                        <x-heroicon-o-information-circle class="h-4 w-4"/>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-900 sm:text-sm">Custom Nama & Nomor</p>
                                        <p class="mt-1 text-[10px] leading-relaxed text-slate-600 sm:text-xs">Opsional. Isi nama atau nomor untuk custom jersey.</p>
                                        <div class="mt-2 space-y-1 text-[10px] font-medium sm:text-xs">
                                            <p class="text-slate-600">Biaya custom: <span class="font-bold text-[#AE7C18]" x-text="'+ Rp ' + Number(customizationPrice).toLocaleString('id-ID')"></span></p>
                                            <p class="text-[10px] leading-relaxed text-slate-500 sm:text-xs">Biaya hanya dikenakan jika nama atau nomor diisi.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- *CUSTOMIZATION INPUTS* --}}
                            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                {{-- *JERSEY NAME* --}}
                                <div>
                                    <div class="mb-2 flex items-end justify-between">
                                        <div>
                                            <label for="custom_name" class="text-xs font-bold uppercase tracking-wide text-slate-900 sm:text-sm">Name on Jersey</label>
                                            <p class="text-[10px] text-gray-400 sm:text-xs">Optional</p>
                                        </div>
                                        <span class="text-[10px] tabular-nums text-gray-400 sm:text-xs"><span x-text="customName.length"></span>/20</span>
                                    </div>
                                    <input id="custom_name" type="text" name="custom_name" x-model="customName" form="add-to-cart-form" maxlength="20" autocomplete="off" placeholder="e.g. BARITO PUTERA" pattern="[A-Za-zÀ-ÿ\s]+" title="Jersey name may only contain letters and spaces." class="w-full rounded-xl border border-gray-300 bg-white px-3 py-3 text-sm font-semibold uppercase tracking-wide text-slate-900 outline-none transition placeholder:font-normal placeholder:normal-case placeholder:tracking-normal placeholder:text-gray-400 hover:border-gray-400 focus:border-[#AE7C18] focus:ring-2 focus:ring-[#AE7C18]/10 sm:rounded-2xl sm:px-4 sm:py-3.5 sm:text-base">
                                    <p class="mt-1.5 text-[10px] text-gray-400 sm:text-xs">Optional · Letters and spaces only</p>
                                </div>
                                {{-- *BACK NUMBER* --}}
                                <div>
                                    <div class="mb-2">
                                        <label for="custom_number" class="text-xs font-bold uppercase tracking-wide text-slate-900 sm:text-sm">Back Number</label>
                                        <p class="text-[10px] text-gray-400 sm:text-xs">Optional</p>
                                    </div>
                                    <input id="custom_number" type="text" name="custom_number" x-model="customNumber" form="add-to-cart-form" maxlength="2" inputmode="numeric" autocomplete="off" placeholder="e.g. 10" pattern="[0-9]{1,2}" title="Back number may only contain 1-2 digits." class="w-full rounded-xl border border-gray-300 bg-white px-3 py-3 text-sm font-semibold tracking-wide text-slate-900 outline-none transition placeholder:font-normal placeholder:tracking-normal placeholder:text-gray-400 hover:border-gray-400 focus:border-[#AE7C18] focus:ring-2 focus:ring-[#AE7C18]/10 sm:rounded-2xl sm:px-4 sm:py-3.5 sm:text-base">
                                    <p class="mt-1.5 text-[10px] text-gray-400 sm:text-xs">Optional · Numbers only · Maximum 2 digits</p>
                                </div>
                            </div>
                        </div>
                        {{-- LONGSLEEVE & PATCH OPTIONS --}}
                        <div class="mt-5 grid grid-cols-2 items-stretch gap-3 sm:mt-6 sm:gap-4">
                            {{-- LONGSLEEVE: KOLOM KIRI --}}
                            <div x-show="longsleeveEnabled" x-cloak class="min-w-0 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-[#AE7C18]/50 sm:p-4" :class="isLongsleeve ? 'border-[#AE7C18] bg-[#AE7C18]/5 ring-1 ring-[#AE7C18]/20' : ''">
                                <label class="flex h-full cursor-pointer flex-col">
                                    <span class="flex items-start justify-between gap-2">
                                        <span class="text-xs font-bold text-slate-900 sm:text-sm">Longsleeve</span>
                                        <input type="checkbox" x-model="isLongsleeve" class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-[#AE7C18] focus:ring-[#AE7C18]">
                                    </span>
                                    <span class="mt-2 text-xs font-bold leading-relaxed text-[#AE7C18] sm:text-sm">+ Rp <span x-text="Number(longsleevePrice).toLocaleString('id-ID')"></span></span>
                                    <span class="mt-2 text-[10px] leading-relaxed text-slate-500 sm:text-xs">Jersey dengan lengan panjang.</span>
                                </label>
                            </div>
                            {{-- PATCH: KOLOM KANAN --}}
                            <div x-show="patchEnabled" x-cloak class="min-w-0 rounded-2xl border border-slate-200 bg-white p-3 transition hover:border-[#AE7C18]/50 sm:p-4" :class="isPatch ? 'border-[#AE7C18] bg-[#AE7C18]/5 ring-1 ring-[#AE7C18]/20' : ''">
                                <label class="flex h-full cursor-pointer flex-col">
                                    <span class="flex items-start justify-between gap-2">
                                        <span class="text-xs font-bold text-slate-900 sm:text-sm">Patch</span>
                                        <input type="checkbox" x-model="isPatch" class="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-[#AE7C18] focus:ring-[#AE7C18]">
                                    </span>
                                    <span class="mt-2 text-xs font-bold leading-relaxed text-[#AE7C18] sm:text-sm">+ Rp <span x-text="Number(patchPrice).toLocaleString('id-ID')"></span></span>
                                    <span class="mt-2 text-[10px] leading-relaxed text-slate-500 sm:text-xs">Tambahkan patch pada jersey.</span>
                                </label>
                            </div>
                        </div>
                        {{-- SELECTED PRICE --}}
                        <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-3.5 sm:mt-4 sm:p-4">
                            <div class="flex items-center justify-between gap-3 text-xs sm:text-sm">
                                <span class="text-gray-500">Jersey Price</span>
                                <span class="font-semibold text-slate-800" x-text="'Rp ' + Number(selectedPrice).toLocaleString('id-ID')"></span>
                            </div>
                            <div x-show="customizationFee > 0" x-transition class="mt-2 flex items-center justify-between gap-3 border-t border-slate-200 pt-2 text-xs sm:text-sm">
                                <span class="text-gray-500">Custom Nama / Nomor</span>
                                <span class="font-semibold text-[#AE7C18]" x-text="'+ Rp ' + Number(customizationFee).toLocaleString('id-ID')"></span>
                            </div>
                            <div x-show="longsleeveFee > 0" x-transition class="mt-2 flex items-center justify-between gap-3 border-t border-slate-200 pt-2 text-xs sm:text-sm">
                                <span class="text-gray-500">Longsleeve</span>
                                <span class="font-semibold text-[#AE7C18]" x-text="'+ Rp ' + Number(longsleeveFee).toLocaleString('id-ID')"></span>
                            </div>
                            <div x-show="patchFee > 0" x-transition class="mt-2 flex items-center justify-between gap-3 border-t border-slate-200 pt-2 text-xs sm:text-sm">
                                <span class="text-gray-500">Patch</span>
                                <span class="font-semibold text-[#AE7C18]" x-text="'+ Rp ' + Number(patchFee).toLocaleString('id-ID')"></span>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-3 border-t border-slate-200 pt-3">
                                <span class="text-sm font-bold text-slate-900 sm:text-base">Selected Price</span>
                                <span class="text-xl font-extrabold text-[#AE7C18] sm:text-2xl" x-text="'Rp ' + Number(finalPrice).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                        <form id="add-to-cart-form" method="POST" action="{{ route('cart.add') }}" class="mt-4 sm:mt-5" @submit.prevent="addToCartAnimation($event)">
                            @csrf
                            <input type="hidden" name="variant_id" x-model="selectedVariant">
                            <input type="hidden" name="qty" value="1">
                            <input type="hidden" name="custom_name" :value="customName">
                            <input type="hidden" name="custom_number" :value="customNumber">
                            <input type="hidden" name="is_longsleeve" :value="isLongsleeve ? 1 : 0">
                            <input type="hidden" name="is_patch" :value="isPatch ? 1 : 0">
                            <button type="submit" x-bind:disabled="selectedStock <= 0" class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-800 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-gray-300 disabled:shadow-none sm:gap-3 sm:px-6 sm:py-3.5 sm:text-base">
                                <x-heroicon-o-shopping-cart class="h-5 w-5"/>
                                <span x-show="selectedStock > 0">Add to Cart</span>
                                <span x-show="selectedStock <= 0">Out of Stock</span>
                            </button>
                        </form>
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-full bg-slate-900 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-800 active:scale-[0.98] sm:mt-5 sm:gap-3 sm:px-6 sm:py-3.5 sm:text-base">
                            <x-heroicon-o-chat-bubble-left-right class="h-5 w-5"/>
                            <span>Tanyakan Produk</span>
                        </a>
                    </div>
                @else
                    <div class="mt-5 rounded-xl bg-gray-100 p-4 text-center sm:mt-7 sm:rounded-2xl sm:p-5">
                        <p class="text-xs font-semibold text-gray-600 sm:text-base">Product currently unavailable.</p>
                    </div>
                @endif
                {{-- PRODUCT FEATURES --}}
                <div class="mt-6 pt-0 sm:mt-7">
                    <div class="grid grid-cols-2 gap-3 sm:gap-4">
                        <div class="rounded-xl bg-[#AE7C18] p-3.5 text-white sm:rounded-2xl sm:p-4">
                            <h4 class="text-xs font-semibold sm:text-base">{{ $product->material ?: 'Premium Material' }}</h4>
                            <p class="mt-1 text-[10px] leading-4 opacity-90 sm:mt-1.5 sm:text-sm sm:leading-5">Premium quality material for comfortable use.</p>
                        </div>
                        <div class="rounded-xl bg-[#AE7C18] p-3.5 text-white sm:rounded-2xl sm:p-4">
                            <h4 class="text-xs font-semibold sm:text-base">Production Time</h4>
                            <p class="mt-1 text-[10px] leading-4 opacity-90 sm:mt-1.5 sm:text-sm sm:leading-5">Pre-Order • Ready from 30 October - 3 November 2026</p>
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
                return rect.width > 0 && rect.height > 0 && style.display !== 'none' && style.visibility !== 'hidden';
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
            dot.style.backgroundColor = '#0F172A';
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