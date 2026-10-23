@extends('admin.layouts.app')

@section('title', 'Kasir Booth')
@section('page-title', 'Kasir Booth')

@section('content')
<div x-data="boothPos()">
    {{-- Container Utama Produk --}}
    <div class="mx-auto max-w-7xl space-y-4 pb-32 px-3 sm:px-4">
        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-800 sm:text-2xl">Kasir Booth</h1>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Pilih produk dan ukuran untuk bertransaksi.</p>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:min-w-[340px]">
                <button
                    type="button"
                    @click="stockModalOpen = true"
                    class="inline-flex min-h-10 items-center justify-center rounded-xl border border-amber-200 bg-white px-3 py-2 text-xs font-semibold text-amber-700 transition hover:bg-amber-50 sm:text-sm"
                >
                    Edit Stok
                </button>

                <a
                    href="{{ route('admin.offline-transactions.index') }}"
                    class="inline-flex min-h-10 items-center justify-center rounded-xl bg-amber-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-amber-700 sm:text-sm"
                >
                    Riwayat Transaksi
                </a>
            </div>
        </div>

        {{-- Sticky Search Bar --}}
        <div class="sticky top-16 z-30 bg-[#F5F6FA] py-2 sm:top-20">
            <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm sm:p-4">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        type="search"
                        x-model="search"
                        placeholder="Cari nama atau kode produk..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 py-2.5 pl-10 pr-4 text-xs font-medium outline-none transition focus:border-amber-500 focus:bg-white focus:ring-2 focus:ring-amber-100 sm:text-sm"
                    >
                </div>

                <div class="mt-2.5 flex items-center justify-between text-xs text-slate-500 px-1">
                    <span>Katalog Ready Stock</span>
                    <span class="font-semibold text-slate-700">{{ $products->count() }} produk</span>
                </div>
            </div>
        </div>

        {{-- Katalog Grid: 1 Kolom di Mobile, 2 Kolom di Tablet, 3-4 Kolom di Desktop --}}
        <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            @forelse($products as $product)
            @php
                $sizeOrder = [
                    'XXS' => 1, '2XS' => 1, 'XS' => 2, 'S' => 3,
                    'M' => 4, 'L' => 5, 'XL' => 6, '2XL' => 7,
                    'XXL' => 7, '3XL' => 8, '4XL' => 9,
                    '5XL' => 10, '6XL' => 11,
                ];

                $readyVariants = $product->variants
                    ->sortBy(function ($variant) use ($sizeOrder) {
                        $size = strtoupper(trim(
                            $variant->size->name
                            ?? $variant->size->size_name
                            ?? ''
                        ));

                        return [
                            $sizeOrder[$size] ?? 99,
                            $size,
                            $variant->id,
                        ];
                    })
                    ->values();

                $variantOptions = $readyVariants->map(function ($variant) {
                    return [
                        'id' => (int) $variant->id,
                        'sku' => (string) ($variant->sku ?? ''),
                        'size' => (string) (
                            $variant->size->name
                            ?? $variant->size->size_name
                            ?? '-'
                        ),
                        'color' => (string) (
                            $variant->color->name
                            ?? $variant->color->color_name
                            ?? '-'
                        ),
                        'price' => (float) ($variant->price ?? 0),
                        'stock' => (int) ($variant->inventory->stock ?? 0),
                    ];
                })->values();

                $firstAvailable = $readyVariants->first(
                    fn ($variant) => (int) ($variant->inventory->stock ?? 0) > 0
                );
                $thumbnail = $product->images->firstWhere('is_thumbnail', true);
                $productImage = $thumbnail && $thumbnail->image
                    ? asset('storage/' . ltrim($thumbnail->image, '/'))
                    : asset('images/products/placeholder.png');
            @endphp

                @if($readyVariants->isNotEmpty())
                    <article
                        x-data="{
                            variants: @js($variantOptions),
                            selectedId: {{ (int) ($firstAvailable?->id ?? $readyVariants->first()->id) }},
                            get selected() {
                                return this.variants.find(v => v.id === Number(this.selectedId)) || this.variants[0];
                            }
                        }"
                        x-show="matchesProduct(@js($product->name), @js($product->product_code ?? ''))"
                        class="group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm transition hover:border-amber-200 hover:shadow-md sm:p-4"
                    >
                        <div>
                            {{-- Container Gambar Produk (Latar Putih Bersih) --}}
                            <div class="relative mb-3 flex h-48 w-full items-center justify-center overflow-hidden rounded-xl bg-white p-2 border border-slate-100 sm:h-52">
                                <img
                                    src="{{ $productImage }}"
                                    alt="{{ $product->name }}"
                                    loading="lazy"
                                    decoding="async"
                                    class="h-full w-full object-contain transition duration-300 group-hover:scale-105"
                                    onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.png') }}';"
                                >
                                <span class="absolute top-2 right-2 rounded-full border border-emerald-200/80 bg-emerald-50 px-2.5 py-0.5 text-[10px] font-bold text-emerald-700 shadow-xs">
                                    Ready
                                </span>
                            </div>

                            {{-- Title & Color --}}
                            <div>
                                <h2 class="text-sm font-bold leading-snug text-slate-800 sm:text-base">
                                    {{ $product->name }}
                                </h2>
                            </div>

                            {{-- Pemilih Ukuran --}}
                            <div class="mt-3">
                                <span class="mb-1 block text-[10px] font-medium text-slate-400 uppercase tracking-wider">Ukuran</span>
                                <div class="flex flex-wrap gap-1.5">
                                    <template x-for="variant in variants" :key="variant.id">
                                        <button
                                            type="button"
                                            @click="selectedId = variant.id"
                                            :class="Number(selectedId) === variant.id
                                                ? 'border-amber-600 bg-amber-600 text-white shadow-xs'
                                                : 'border-slate-200 bg-slate-50 text-slate-700 hover:border-amber-400 hover:bg-amber-50/40'"
                                            class="flex h-8 min-w-[32px] items-center justify-center rounded-lg border px-2.5 text-xs font-bold transition disabled:cursor-not-allowed disabled:border-slate-100 disabled:bg-slate-100 disabled:text-slate-300 disabled:line-through sm:h-9 sm:min-w-[36px]"
                                            x-text="variant.size"
                                            :disabled="variant.stock < 1"
                                            :title="variant.stock < 1 ? 'Stok habis' : 'Pilih ukuran ' + variant.size"
                                        ></button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Card (Harga, Stok & Tombol) --}}
                        <div class="mt-4 border-t border-slate-100 pt-3">
                            <div class="mb-3 flex items-end justify-between">
                                <div>
                                    <span class="block text-[10px] font-medium uppercase tracking-wider text-slate-400">Harga</span>
                                    <span class="text-sm font-bold text-slate-900 sm:text-base" x-text="money(selected.price)"></span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-[10px] font-medium uppercase tracking-wider text-slate-400">Stok</span>
                                    <span
                                        class="text-xs font-semibold"
                                        :class="selected.stock > 0 ? 'text-emerald-600' : 'text-red-500'"
                                        x-text="selected.stock > 0 ? selected.stock + ' pcs' : 'Habis'"
                                    ></span>
                                </div>
                            </div>

                            <button
                                type="button"
                                :disabled="selected.stock < 1"
                                @click="addProduct({
                                    id: selected.id,
                                    name: @js($product->name),
                                    sku: selected.sku,
                                    size: selected.size,
                                    color: selected.color,
                                    price: selected.price,
                                    stock: selected.stock
                                })"
                                class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-amber-600 py-2.5 text-xs font-bold text-white transition hover:bg-amber-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400 sm:text-sm"
                            >
                                <span>Tambah Ke Keranjang</span>
                            </button>
                        </div>
                    </article>
                @endif
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center">
                    <p class="font-semibold text-slate-700">Belum ada produk ready stock.</p>
                    <p class="mt-1 text-xs text-slate-500">Produk akan muncul di sini jika stok tersedia.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Floating Cart Bar --}}
    <div
        x-cloak
        x-show="itemCount > 0"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-10 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        class="fixed bottom-4 left-3 right-3 z-40 sm:left-auto sm:right-6 sm:bottom-6 sm:w-auto"
    >
        <button
            type="button"
            @click="cartOpen = true"
            class="flex w-full items-center justify-between gap-4 rounded-2xl bg-slate-900 px-4 py-3 text-white shadow-xl border border-slate-800 transition hover:bg-slate-800 active:scale-[0.98] sm:w-auto"
        >
            <div class="flex items-center gap-3">
                <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/20 text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l2.2 11.2a2 2 0 002 1.6h7.9a2 2 0 001.9-1.4L21 8H6" />
                        <circle cx="10" cy="20" r="1" />
                        <circle cx="18" cy="20" r="1" />
                    </svg>
                    <span
                        x-text="itemCount"
                        class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-amber-500 px-1 text-[10px] font-bold text-white shadow"
                    ></span>
                </span>
                <div class="text-left">
                    <span class="block text-[10px] font-medium text-slate-400">Keranjang Belanja</span>
                    <span class="block text-sm font-semibold text-white sm:text-base" x-text="money(total)"></span>
                </div>
            </div>

            <div class="flex items-center gap-1.5 rounded-xl bg-amber-600 px-3.5 py-2 text-xs font-bold text-white transition hover:bg-amber-700">
                <span>Lihat Keranjang</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" />
                </svg>
            </div>
        </button>
    </div>

    {{-- Panel Keranjang Modal & Backdrop --}}
    <div
        x-cloak
        x-show="cartOpen"
        @keydown.escape.window="cartOpen = false"
        class="fixed inset-0 z-[9999] flex items-end justify-center sm:items-center sm:justify-end sm:p-6"
    >
        {{-- Backdrop Overlay --}}
        <div
            x-show="cartOpen"
            x-transition:enter="transition-opacity ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="cartOpen = false"
            class="fixed inset-0 bg-black/60"
        ></div>

        {{-- Content Drawer --}}
        <section
            x-show="cartOpen"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-y-full sm:translate-y-0 sm:translate-x-full"
            x-transition:enter-end="translate-y-0 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0 sm:translate-x-0"
            x-transition:leave-end="translate-y-full sm:translate-y-0 sm:translate-x-full"
            class="relative z-10 flex max-h-[90dvh] w-full flex-col overflow-hidden rounded-t-3xl bg-white sm:h-full sm:max-h-[95vh] sm:w-[420px] sm:rounded-2xl sm:shadow-xl"
        >
            {{-- Header Sheet --}}
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900 sm:text-lg">Detail Pesanan</h2>
                    <p class="text-xs text-slate-500" x-text="itemCount + ' varian dipilih'"></p>
                </div>

                <button
                    type="button"
                    @click="cartOpen = false"
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-slate-200"
                    aria-label="Tutup keranjang"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Daftar Item Keranjang --}}
            <div class="min-h-0 flex-1 overflow-y-auto p-4 sm:p-5 space-y-3">
                <template x-if="cart.length === 0">
                    <div class="py-12 text-center">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l2.2 11.2a2 2 0 002 1.6h7.9a2 2 0 001.9-1.4L21 8H6" />
                            </svg>
                        </div>
                        <p class="font-semibold text-slate-700">Keranjang masih kosong</p>
                        <p class="mt-1 text-xs text-slate-400">Pilih produk dari katalog untuk mulai bertransaksi.</p>
                        <button
                            type="button"
                            @click="cartOpen = false"
                            class="mt-4 rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white"
                        >Pilih Produk</button>
                    </div>
                </template>

                <template x-for="item in cart" :key="item.id">
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-xs text-slate-800 break-words" x-text="item.name"></p>
                            <p class="text-[11px] text-slate-500 mt-0.5" x-text="item.color + ' &#183; Ukuran ' + item.size"></p>
                            <p class="text-xs font-bold text-slate-900 mt-1.5" x-text="money(item.price * item.qty)"></p>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <div class="flex items-center rounded-lg border border-slate-200 bg-white">
                                <button type="button" @click="changeQty(item.id, -1)"
                                    class="h-8 w-8 flex items-center justify-center text-slate-600 font-bold hover:bg-slate-50 text-sm">&#8722;</button>
                                <span class="w-6 text-center text-xs font-bold text-slate-800" x-text="item.qty"></span>
                                <button type="button" @click="changeQty(item.id, 1)"
                                    :disabled="item.qty >= item.stock"
                                    class="h-8 w-8 flex items-center justify-center text-slate-600 font-bold hover:bg-slate-50 disabled:opacity-30 text-sm">+</button>
                            </div>

                            <button
                                type="button"
                                @click="removeProduct(item.id)"
                                class="p-1.5 text-slate-400 hover:text-red-600 transition"
                                title="Hapus item"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
                {{-- Metode Pembayaran --}}
                <div class="space-y-3 pt-2"><div class="rounded-xl bg-slate-50 p-3 border border-slate-100">
                        <label for="booth-payment" class="mb-1.5 block text-xs font-medium text-slate-600">Metode Pembayaran</label>
                        <select
                            id="booth-payment"
                            x-model="paymentMethod"
                            class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-800 outline-none focus:border-amber-500"
                        >
                            <option value="CASH">Tunai (Cash)</option>
                            <option value="QRIS">QRIS DOKU</option>
                        </select>
                    </div>
                </div>

                {{-- Ringkasan Biaya --}}
                <div class="space-y-1.5 border-t border-slate-100 pt-3 text-xs text-slate-500">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span x-text="money(subtotal)"></span>
                    </div>
                    <div class="flex justify-between text-sm font-semibold text-slate-900 pt-1">
                        <span>Total Pembayaran</span>
                        <span class="text-amber-700" x-text="money(total)"></span>
                    </div>
                </div>
            </div>

            {{-- Footer Tombol Aksi Pembayaran --}}
            <div class="border-t border-slate-100 bg-slate-50 p-4">
                <template x-if="checkoutMessage">
                    <div
                        role="status"
                        aria-live="polite"
                        class="mb-3 rounded-xl border p-3 text-xs font-medium"
                        :class="checkoutSuccess
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                            : 'border-red-200 bg-red-50 text-red-700'"
                        x-text="checkoutMessage"
                    ></div>
                </template>

                <button
                    type="button"
                    @click="checkout()"
                    :disabled="isSubmitting || cart.length === 0"
                    class="flex h-12 w-full items-center justify-center rounded-xl bg-amber-600 px-4 text-xs font-bold text-white transition hover:bg-amber-700 disabled:cursor-not-allowed disabled:bg-slate-300 disabled:text-slate-500"
                >
                    <span
                        x-text="isSubmitting
                            ? 'Memproses Pembayaran...'
                            : 'Proses Pembayaran'"
                    ></span>
                </button>
            </div>
        </section>
    </div>
    
    {{-- Modal Edit Stok --}}
    <div
        x-cloak
        x-show="stockModalOpen"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[9998] flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="booth-stock-title"
        @keydown.escape.window="stockModalOpen = false"
        @click.self="stockModalOpen = false"
    >
        <div
            x-show="stockModalOpen"
            x-transition
            class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl"
        >
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 id="booth-stock-title" class="text-base font-bold text-slate-900 sm:text-lg">Edit Stok Produk</h2>
                    <p class="mt-1 text-xs text-slate-500">Pilih produk dan ukuran, lalu tentukan stok terbaru.</p>
                </div>
                <button
                    type="button"
                    @click="stockModalOpen = false"
                    class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100"
                    aria-label="Tutup modal"
                ><span aria-hidden="true" class="text-xl leading-none">&times;</span></button>
            </div>

            <div class="space-y-4 p-5">
                <div>
                    <label for="stock-product" class="mb-1.5 block text-xs font-semibold text-slate-600">Produk</label>
                    <select
                        id="stock-product"
                        x-model="stockProductId"
                        @change="stockVariantId = ''; stockMessage = ''; loadStockVariants()"
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100"
                    >
                        <option value="">Pilih produk</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="stock-variant" class="mb-1.5 block text-xs font-semibold text-slate-600">Ukuran / Varian</label>
                    <select
                        id="stock-variant"
                        x-model="stockVariantId"
                        @change="loadSelectedStock()"
                        :disabled="!stockProductId"
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-50 disabled:text-slate-400"
                    >
                        <option value="">Pilih ukuran</option>
                        <template x-for="variant in stockVariants" :key="variant.id">
                            <option :value="String(variant.id)" x-text="variant.label"></option>
                        </template>
                    </select>
                </div>

                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <span class="inline-flex items-center gap-2 text-sm text-slate-600">
                            Stok saat ini
                        </span>
                    </div>
                    <div class="mt-3">
                        <label for="stock-new-value" class="mb-1.5 block text-xs font-semibold text-slate-600">Stok baru</label>
                        <input
                            id="stock-new-value"
                            type="number"
                            min="0"
                            step="1"
                            x-model.number="stockValue"
                            :disabled="!stockVariantId"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold outline-none focus:border-amber-500 focus:ring-2 focus:ring-amber-100 disabled:bg-slate-100"
                            placeholder="Masukkan jumlah stok"
                        >
                    </div>
                </div>

                <div
                    x-show="stockMessage"
                    x-text="stockMessage"
                    role="status"
                    class="rounded-xl px-3 py-2 text-xs font-medium"
                    :class="stockMessageType === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'"
                ></div>
            </div>

            <div class="flex gap-2 border-t border-slate-100 bg-slate-50 p-4">
                <button
                    type="button"
                    @click="stockModalOpen = false"
                    class="h-11 flex-1 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 hover:bg-slate-100"
                >Batal</button>
                <button
                    type="button"
                    @click="saveStock()"
                    :disabled="stockSaving || !stockVariantId"
                    class="h-11 flex-1 rounded-xl bg-amber-600 px-4 text-sm font-bold text-white hover:bg-amber-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                >
                    <span x-text="stockSaving ? 'Menyimpan...' : 'Simpan Stok'"></span>
                </button>
            </div>
        </div>
    </div>
    {{-- Popup sukses penyimpanan stok --}}
    <div
        x-cloak
        x-show="stockSuccessOpen"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[10001] flex items-center justify-center bg-slate-950/60 px-4 py-6 backdrop-blur-sm"
        role="alertdialog"
        aria-modal="true"
        aria-labelledby="stock-success-title"
        @keydown.escape.window="stockSuccessOpen = false"
        @click.self="stockSuccessOpen = false"
    >
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                </svg>
            </div>
            <h2 id="stock-success-title" class="mt-4 text-lg font-bold text-slate-900">Stok berhasil diperbarui!</h2>
            <p class="mt-2 text-sm text-slate-500">Perubahan stok sudah tersimpan.</p>
            <button
                type="button"
                @click="window.location.reload()"
                class="mt-5 w-full rounded-xl bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-700"
            >
                OK
            </button>
        </div>
    </div>
    {{-- Popup sukses pembayaran Booth --}}
    <div
        x-cloak
        x-show="checkoutModalOpen"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/70 px-4 py-6 backdrop-blur-sm"
        role="dialog"
        aria-modal="true"
        aria-labelledby="booth-success-title"
        @keydown.escape.prevent
    >
        <div
            x-show="checkoutModalOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-5 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl"
        >
            <div class="px-6 py-8 text-center sm:px-9">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 ring-8 ring-emerald-50/60">
                    <svg class="h-11 w-11 text-emerald-600 booth-success-check"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <path d="m5 12 4 4L19 6"/>
                    </svg>
                </div>

                <p class="mt-6 text-xs font-extrabold uppercase tracking-[0.2em]" :class="paymentStatus === 'PAID' ? 'text-emerald-600' : 'text-amber-600'" x-text="paymentStatus === 'PAID' ? 'Pembayaran Berhasil' : 'Menunggu Pembayaran'"></p>

                <h2 id="booth-success-title" class="mt-2 text-2xl font-extrabold text-slate-900" x-text="paymentStatus === 'PAID' ? 'Transaksi Berhasil!' : 'Selesaikan Pembayaran QRIS'"></h2>

                <p class="mt-2 text-sm leading-6 text-slate-500" x-text="paymentStatus === 'PAID' ? 'Pembayaran telah dikonfirmasi. Transaksi sudah lunas.' : 'Silakan pindai QRIS. Stok diperbarui setelah pembayaran dikonfirmasi DOKU.'"></p>

                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left">
                    <p class="text-xs font-medium text-slate-500">Nomor Invoice</p>
                    <p class="mt-1 break-all text-sm font-extrabold text-slate-900"
                       x-text="successInvoice"></p>

                    <div class="my-3 border-t border-dashed border-slate-300"></div>

                    <p class="text-xs font-medium text-slate-500">Total Dibayar</p>
                    <p class="mt-1 text-2xl font-extrabold text-emerald-700"
                       x-text="money(successTotal)"></p>

                    <p class="mt-1 text-xs font-semibold" :class="paymentStatus === 'PAID' ? 'text-emerald-700' : 'text-amber-700'" x-text="paymentStatus === 'PAID' ? (successPaymentMethod === 'CASH' ? 'Tunai - Lunas' : 'QRIS - Lunas') : 'QRIS - Menunggu Pembayaran'"></p>
                    <div x-show="successPaymentMethod === 'QRIS' && paymentStatus !== 'PAID'" class="mt-4 rounded-xl border border-slate-200 bg-white p-3 text-center">
                        <div class="mx-auto flex min-h-48 items-center justify-center [&_svg]:h-auto [&_svg]:max-w-full" x-show="qrisSvg" x-html="qrisSvg"></div>
                        <p class="mt-2 text-xs font-medium text-slate-500">Pindai QRIS menggunakan aplikasi pembayaran.</p>
                        <p class="mt-2 break-all text-xs text-slate-500" x-text="'Referensi: ' + qrisReferenceNo"></p>
                        <p class="mt-2 text-xs font-semibold text-amber-700" x-text="paymentStatusMessage"></p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="window.location.href = historyUrl"
                    class="mt-6 inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-[#AE7C18] px-5 text-sm font-bold text-white shadow-lg shadow-amber-900/10 transition hover:bg-[#8F6514] active:scale-[0.98]"
                >
                    Cek Transaksi
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M13 6l6 6-6 6"/>
                    </svg>
                </button>

                <p class="mt-3 text-xs text-slate-400">
                    Klik tombol di atas untuk membuka riwayat transaksi offline.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .booth-success-check {
    stroke-dasharray: 30;
    stroke-dashoffset: 30;
    animation: booth-check-draw 0.55s ease-out 0.15s forwards;
    }

    @keyframes booth-check-draw {
        to {
            stroke-dashoffset: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .booth-success-check {
            animation: none;
            stroke-dashoffset: 0;
        }
    }
</style>

<script>
    function boothPos() {
        return {
            search: '',
            cart: [],
            discount: 0,
            paymentMethod: 'CASH',
            cartOpen: false,

            isSubmitting: false,
            checkoutMessage: '',
            checkoutSuccess: false,

            checkoutModalOpen: false,
            successInvoice: '',
            successTotal: 0,
            successPaymentMethod: 'CASH',
            paymentStatus: 'PENDING',
            qrisSvg: '',
            qrisReferenceNo: '',
            qrisExpiredAt: null,
            paymentStatusMessage: 'Menunggu konfirmasi pembayaran dari DOKU...',
            paymentPollTimer: null,
            paymentSoundPlayed: false,
            historyUrl: @js(route('admin.offline-transactions.index')),
            paymentStatusUrlTemplate: @js(route('admin.kasir.payment-status', ['invoice' => '__INVOICE__'])),
            csrfToken: @js(csrf_token()),
            checkoutUrl: @js(route('admin.kasir.checkout')),
            stockUpdateUrl: @js(route('admin.kasir.update-stock')),
            stockModalOpen: false,
            stockProductId: '',
            stockVariantId: '',
            stockValue: 0,
            stockSaving: false,
            stockMessage: '',
            stockMessageType: 'success',
            stockSuccessOpen: false,
            stockVariants: [],
            selectedStockVariant: null,

            loadStockVariants() {
                const products = @js($products->map(function ($product) {
                    return [
                        'id' => (int) $product->id,
                        'name' => (string) $product->name,
                        'variants' => $product->variants->map(function ($variant) {
                            return [
                                'id' => (int) $variant->id,
                                'size' => (string) ($variant->size->name ?? $variant->size->size_name ?? '-'),
                                'color' => (string) ($variant->color->name ?? $variant->color->color_name ?? '-'),
                                'sku' => (string) ($variant->sku ?? ''),
                                'stock' => (int) ($variant->inventory->stock ?? 0),
                            ];
                        })->values(),
                    ];
                })->values()),
                product = products.find(item => String(item.id) === String(this.stockProductId));

                this.stockVariants = product
                    ? product.variants.map(variant => ({
                        ...variant,
                        label: variant.size
                    }))
                    : [];
            },

            loadSelectedStock() {
                this.selectedStockVariant = this.stockVariants.find(
                    variant => String(variant.id) === String(this.stockVariantId)
                ) || null;

                this.stockValue = this.selectedStockVariant
                    ? this.selectedStockVariant.stock
                    : 0;

                this.stockMessage = '';
            },

            get subtotal() {
                return this.cart.reduce((sum, item) => sum + item.price * item.qty, 0);
            },

            get appliedDiscount() {
                return Math.min(Math.max(Number(this.discount) || 0, 0), this.subtotal);
            },

            get total() {
                return Math.max(this.subtotal - this.appliedDiscount, 0);
            },

            get itemCount() {
                return this.cart.reduce((sum, item) => sum + item.qty, 0);
            },

            matchesProduct(name, code) {
                const keyword = this.search.trim().toLowerCase();
                return !keyword || [name, code].some(value =>
                    String(value ?? '').toLowerCase().includes(keyword)
                );
            },

            addProduct(product) {
                if (product.stock < 1) return;

                const existing = this.cart.find(item => item.id === product.id);

                if (existing) {
                    if (existing.qty < existing.stock) existing.qty++;
                } else {
                    this.cart.push({
                        ...product,
                        price: Number(product.price) || 0,
                        stock: Number(product.stock) || 0,
                        qty: 1
                    });
                }
            },

            async saveStock() {
                if (this.stockSaving) return;

                if (!this.stockVariantId) {
                    this.stockMessage = 'Pilih produk dan ukuran terlebih dahulu.';
                    this.stockMessageType = 'error';
                    return;
                }

                const newStock = Number(this.stockValue);

                if (!Number.isInteger(newStock) || newStock < 0) {
                    this.stockMessage = 'Stok harus berupa bilangan bulat minimal 0.';
                    this.stockMessageType = 'error';
                    return;
                }

                this.stockSaving = true;
                this.stockMessage = '';

                try {
                    const response = await fetch(this.stockUpdateUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            variant_id: Number(this.stockVariantId),
                            stock: newStock
                        })
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const messages = Object.values(data.errors || {}).flat();
                        throw new Error(
                            messages[0] || data.message || 'Stok gagal diperbarui.'
                        );
                    }

                    this.updateDisplayedStock(Number(data.variant_id), Number(data.stock));
                    this.stockValue = Number(data.stock);
                    this.stockMessage = data.message || 'Stok berhasil diperbarui.';
                    this.stockMessageType = 'success';
                    this.stockSuccessOpen = true;
                } catch (error) {
                    this.stockMessage = error.message || 'Terjadi kesalahan saat menyimpan stok.';
                    this.stockMessageType = 'error';
                } finally {
                    this.stockSaving = false;
                }
            },

            updateDisplayedStock(variantId, newStock) {
                document.querySelectorAll('[data-booth-variants]').forEach(element => {
                    const variants = JSON.parse(element.dataset.boothVariants || '[]');
                    const variant = variants.find(item => Number(item.id) === variantId);

                    if (variant) {
                        variant.stock = newStock;
                        element.dataset.boothVariants = JSON.stringify(variants);
                    }
                });

                this.cart.forEach(item => {
                    if (Number(item.id) === variantId) {
                        item.stock = newStock;
                        if (item.qty > newStock) item.qty = newStock;
                    }
                });

            },

            changeQty(id, change) {
                const item = this.cart.find(row => row.id === id);
                if (!item) return;

                const nextQty = item.qty + change;

                if (nextQty < 1) {
                    this.removeProduct(id);
                } else if (nextQty <= item.stock) {
                    item.qty = nextQty;
                }
            },

            removeProduct(id) {
                this.cart = this.cart.filter(item => item.id !== id);
            },

            async checkout() {
                if (this.isSubmitting) return;

                this.checkoutMessage = '';
                this.checkoutSuccess = false;

                if (this.cart.length === 0) {
                    this.checkoutMessage = 'Keranjang masih kosong.';
                    return;
                }

                if (
                    !Number.isFinite(Number(this.discount)) ||
                    Number(this.discount) < 0
                ) {
                    this.checkoutMessage = 'Nominal diskon tidak valid.';
                    return;
                }

                this.isSubmitting = true;

                try {
                    const response = await fetch(this.checkoutUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            payment_method: this.paymentMethod,
                            discount: Number(this.discount) || 0,
                            items: this.cart.map(item => ({
                                id: Number(item.id),
                                qty: Number(item.qty)
                            }))
                        })
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const validationMessages = Object.values(
                            data.errors || {}
                        ).flat();

                        throw new Error(
                            validationMessages[0] ||
                            data.message ||
                            'Pembayaran gagal diproses. Silakan coba lagi.'
                        );
                    }

                    this.checkoutSuccess = true;
                    this.checkoutMessage = '';
                    this.successInvoice = data.invoice_number;
                    this.successTotal = Number(data.total) || 0;
                    this.successPaymentMethod = data.payment_method || this.paymentMethod;
                    this.paymentStatus = data.status || (this.successPaymentMethod === 'CASH' ? 'PAID' : 'PENDING');
                    this.qrisSvg = data.qris_qr_code || '';
                    this.qrisReferenceNo = data.qris_reference_no || '';
                    this.qrisExpiredAt = data.qris_expired_at || null;
                    this.paymentSoundPlayed = false;
                    this.paymentStatusMessage = 'Menunggu konfirmasi pembayaran dari DOKU...';
                    this.cart = [];
                    this.discount = 0;
                    this.cartOpen = false;
                    this.checkoutModalOpen = true;

                    if (this.paymentStatus === 'PAID') {
                        this.paymentSoundPlayed = true;
                        this.playSuccessSound();
                    } else if (this.successPaymentMethod === 'QRIS') {
                        this.startPaymentPolling();
                    }

                } catch (error) {
                    this.checkoutSuccess = false;
                    this.checkoutMessage =
                        error.message || 'Terjadi kesalahan saat memproses pembayaran.';
                } finally {
                    this.isSubmitting = false;
                }
            },

            startPaymentPolling() {
                if (this.paymentPollTimer) clearTimeout(this.paymentPollTimer);
                this.pollPaymentStatus();
            },

            async pollPaymentStatus() {
                if (!this.successInvoice || this.paymentStatus === 'PAID') return;

                try {
                    const url = this.paymentStatusUrlTemplate.replace(
                        '__INVOICE__',
                        encodeURIComponent(this.successInvoice)
                    );
                    const response = await fetch(url, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await response.json().catch(() => ({}));

                    if (response.ok && data.status === 'PAID') {
                        this.paymentStatus = 'PAID';
                        this.paymentStatusMessage = 'Pembayaran berhasil dikonfirmasi.';
                        this.qrisSvg = '';
                        if (!this.paymentSoundPlayed) {
                            this.paymentSoundPlayed = true;
                            this.playSuccessSound();
                        }
                        return;
                    }

                    this.paymentStatusMessage = 'Menunggu pembayaran QRIS dikonfirmasi DOKU...';
                } catch (error) {
                    this.paymentStatusMessage = 'Koneksi status terganggu. Mencoba kembali...';
                }

                this.paymentPollTimer = setTimeout(() => this.pollPaymentStatus(), 3000);
            },

            playSuccessSound() {
                try {
                    if (!this.successAudio) {
                        this.successAudio = new Audio(
                            '/sounds/uangmasuk.mp3'
                        );
                        this.successAudio.preload = 'auto';
                        this.successAudio.volume = 1;
                    }

                    this.successAudio.currentTime = 0;
                    this.successAudio.play().catch(error => {
                        console.warn(
                            'Suara notifikasi tidak dapat diputar:',
                            error
                        );
                    });
                } catch (error) {
                    console.warn(
                        'Suara notifikasi tidak dapat diputar:',
                        error
                    );
                }
            },
            

            money(value) {
                return 'Rp' + Math.round(Number(value) || 0).toLocaleString('id-ID');
            }
        };
    }
</script>
@endsection
