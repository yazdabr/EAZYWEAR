@extends('layouts.website')

@section('title', 'Checkout - Eazywear Indonesia')

@section('content')
<section class="bg-gray-50 py-5 sm:py-10 lg:py-12">
    <x-ui.container>
        {{-- Header --}}
        <div class="mb-5 sm:mb-7">
            <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500 transition hover:text-[#AE7C18] sm:text-sm">
                <x-heroicon-o-arrow-left class="h-4 w-4"/>
                Kembali ke Keranjang
            </a>
            <div class="mt-3 sm:mt-4">
                <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-[#AE7C18] sm:text-xs">CHECKOUT</p>
                <h1 class="mt-1 text-2xl font-bold leading-tight text-slate-900 sm:text-3xl lg:text-4xl">Lengkapi Pesanan Anda</h1>
                <p class="mt-2 max-w-2xl text-xs leading-5 text-gray-500 sm:text-sm sm:leading-6">Masukkan data penerima, pilih metode pengiriman, dan tentukan metode pembayaran.</p>
            </div>
        </div>

        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-600 sm:text-sm">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-600 sm:text-sm">
                <p class="font-semibold">Periksa kembali data checkout Anda.</p>
                <ul class="mt-1.5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}">
            @csrf
            
            {{-- Wrapper Utama Mobile (flex-col) & Desktop (grid-cols-3) --}}
            <div class="flex flex-col gap-5 lg:grid lg:grid-cols-3 lg:items-start lg:gap-6">
                
                {{-- FORM KIRI: Data Pemesan & Alamat (Mobile: Ke-2 | Desktop: Kolom Kiri / Span 2) --}}
                <div class="order-2 space-y-4 lg:order-1 lg:col-span-2">
                    {{-- Customer Information --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-4">
                            <h2 class="text-base font-bold text-slate-900 sm:text-lg">Data Pemesan</h2>
                            <p class="mt-1 text-[11px] leading-4 text-gray-500 sm:text-xs">Gunakan email dan nomor WhatsApp yang aktif.</p>
                        </div>
                        <div class="grid gap-3.5 sm:grid-cols-2 sm:gap-4">
                            <div class="sm:col-span-2">
                                <label for="name" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" placeholder="Masukkan nama lengkap" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                            </div>
                            <div>
                                <label for="email" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Email <span class="text-red-500">*</span></label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@gmail.com" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                            </div>
                            <div>
                                <label for="phone" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel" placeholder="08xxxxxxxxxx" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Shipping Address --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-4">
                            <h2 class="text-base font-bold text-slate-900 sm:text-lg">Alamat Pengiriman</h2>
                            <p class="mt-1 text-[11px] leading-4 text-gray-500 sm:text-xs">Pastikan alamat pengiriman ditulis dengan lengkap dan benar.</p>
                        </div>
                        <div class="space-y-3.5">
                            <div>
                                <label for="shipping_address" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Alamat Lengkap <span class="text-red-500">*</span></label>
                                <textarea id="shipping_address" name="shipping_address" rows="2" required autocomplete="street-address" placeholder="Nama jalan, nomor rumah, RT/RW, patokan, dan detail lainnya" class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs leading-5 text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:rounded-xl sm:px-4 sm:py-3 sm:text-sm sm:leading-6">{{ old('shipping_address') }}</textarea>
                            </div>
                            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <label for="shipping_district" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Kecamatan <span class="text-red-500">*</span></label>
                                    <input id="shipping_district" name="shipping_district" type="text" value="{{ old('shipping_district') }}" required placeholder="Kecamatan" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                                </div>
                                <div>
                                    <label for="shipping_city" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Kota / Kab. <span class="text-red-500">*</span></label>
                                    <input id="shipping_city" name="shipping_city" type="text" value="{{ old('shipping_city') }}" required placeholder="Kota / Kabupaten" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                                </div>
                                <div>
                                    <label for="shipping_province" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Provinsi <span class="text-red-500">*</span></label>
                                    <input id="shipping_province" name="shipping_province" type="text" value="{{ old('shipping_province') }}" required placeholder="Provinsi" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                                </div>
                                <div>
                                    <label for="shipping_postal_code" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Kode Pos <span class="text-red-500">*</span></label>
                                    <input id="shipping_postal_code" name="shipping_postal_code" type="text" value="{{ old('shipping_postal_code') }}" required inputmode="numeric" autocomplete="postal-code" placeholder="70654" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Shipping Method --}}
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-4">
                            <h2 class="text-base font-bold text-slate-900 sm:text-lg">Pengiriman</h2>
                        </div>
                        <div class="space-y-2.5">
                            @foreach($shippingMethods as $method)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-[#AE7C18] bg-[#AE7C18]/5 px-3.5 py-3 sm:gap-4 sm:px-4">
                                    <input type="radio" name="shipping_method" value="{{ $method['value'] }}" checked class="h-4 w-4 accent-[#AE7C18]">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-semibold text-slate-900 sm:text-sm">{{ $method['name'] }}</p>
                                        <p class="mt-0.5 text-[10px] leading-4 text-gray-500 sm:text-xs">{{ $method['description'] }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-3 rounded-lg bg-gray-50 px-3 py-2.5">
                            <p class="text-[10px] leading-4 text-gray-500 sm:text-xs sm:leading-5">Biaya pengiriman akan ditentukan pada proses pemesanan berikutnya.</p>
                        </div>
                    </div>
                </div>

                {{-- SIDEBAR KANAN (Ringkasan + Pembayaran) --}}
                <div class="contents lg:flex lg:flex-col lg:gap-5 lg:order-2 lg:col-span-1">
                    
                    {{-- 1. ORDER SUMMARY (Mobile: Order 1 | Desktop: Normal Flow Atas) --}}
                    <div class="order-1 lg:order-none">
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-[#AE7C18] sm:text-xs">
                                        ORDER
                                    </p>
                                    <h2 class="mt-0.5 text-base font-bold text-slate-900 sm:text-lg">
                                        Ringkasan
                                    </h2>
                                </div>
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-semibold text-gray-600 sm:text-xs">
                                    {{ $totalItems }} item
                                </span>
                            </div>

                            <div class="mt-4 space-y-3">
                                @foreach($cart as $item)
                                    <div class="flex gap-3">
                                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-gray-100 sm:h-14 sm:w-14">
                                            <img
                                                src="{{ $item['image'] }}"
                                                alt="{{ $item['product_name'] }}"
                                                class="h-full w-full object-cover"
                                            >
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <p class="line-clamp-2 text-[11px] font-semibold leading-4 text-slate-900 sm:text-xs">
                                                {{ $item['product_name'] }}
                                            </p>

                                            <div class="mt-0.5 flex flex-wrap gap-x-2 text-[10px] text-gray-500 sm:text-[11px]">
                                                <span>Size: {{ $item['size_name'] }}</span>
                                                <span>× {{ $item['qty'] }}</span>
                                            </div>
                                            @if(!empty($item['custom_name']))
                                                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700 sm:text-[11px]">
                                                    Nama Jersey: {{ $item['custom_name'] }}
                                                </p>
                                            @endif

                                            <p class="mt-0.5 text-xs font-semibold text-[#AE7C18] sm:text-sm">
                                                Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="my-4 border-t border-gray-100"></div>

                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between text-xs text-gray-600 sm:text-sm">
                                    <span>Subtotal</span>
                                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>

                                <div class="flex items-center justify-between text-xs text-gray-600 sm:text-sm">
                                    <span>Pengiriman</span>
                                    <span class="text-[10px] text-gray-400 sm:text-xs">
                                        Akan dihitung
                                    </span>
                                </div>
                            </div>

                            <div class="my-4 border-t border-gray-100"></div>

                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-slate-900 sm:text-sm">
                                        Total
                                    </p>
                                    <p class="mt-0.5 text-[10px] text-gray-400 sm:text-xs">
                                        Belum termasuk ongkir
                                    </p>
                                </div>

                                <p class="text-xl font-bold text-[#AE7C18] sm:text-2xl">
                                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                                </p>
                            </div>

                            <a
                                href="{{ route('cart.index') }}"
                                class="mt-4 inline-flex w-full items-center justify-center rounded-full border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-600 transition hover:border-[#AE7C18] hover:text-[#AE7C18]"
                            >
                                Ubah Keranjang
                            </a>
                        </div>
                    </div>

                    {{-- 2. PAYMENT & SUBMIT (Mobile: Order 3 | Desktop: Normal Flow Bawah Ringkasan) --}}
                    <div class="order-3 space-y-4 lg:order-none">
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                            <div class="mb-4">
                                <h2 class="text-base font-bold text-slate-900 sm:text-lg">
                                    Pembayaran
                                </h2>

                                <p class="mt-1 text-[11px] text-gray-500 sm:text-xs">
                                    Pilih metode pembayaran yang akan digunakan.
                                </p>
                            </div>

                            <div class="space-y-2.5">
                                @foreach($paymentMethods as $method)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-3.5 py-3 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5 has-[:checked]:border-[#AE7C18] has-[:checked]:bg-[#AE7C18]/5 sm:gap-4 sm:px-4">
                                        <input
                                            type="radio"
                                            name="payment_method"
                                            value="{{ $method['value'] }}"
                                            @checked($loop->first)
                                            class="h-4 w-4 accent-[#AE7C18]"
                                        >

                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#AE7C18]/10">
                                            @if($method['value'] === 'QRIS')
                                                <x-heroicon-o-qr-code class="h-4 w-4 text-[#AE7C18]"/>
                                            @else
                                                <x-heroicon-o-building-library class="h-4 w-4 text-[#AE7C18]"/>
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-slate-900 sm:text-sm">
                                                {{ $method['name'] }}
                                            </p>

                                            <p class="mt-0.5 text-[10px] leading-4 text-gray-500 sm:text-xs">
                                                {{ $method['description'] }}
                                            </p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Submit Button --}}
                        <button
                            type="submit"
                            class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-full bg-[#AE7C18] px-6 text-xs font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#8F6514] active:scale-[0.99] sm:h-12 sm:text-sm"
                        >
                            Buat Pesanan
                            <x-heroicon-o-arrow-right class="h-4 w-4 sm:h-5 sm:w-5"/>
                        </button>
                    </div>

                </div>

            </div>
        </form>
    </x-ui.container>
</section>
@endsection