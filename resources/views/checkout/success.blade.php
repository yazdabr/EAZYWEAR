@extends('layouts.website')
@section('title', 'Pesanan Berhasil - Eazywear Indonesia')
@section('content')
<section class="min-h-[70vh] bg-gray-50 py-8 sm:py-16 lg:py-20">
    <x-ui.container>
        <div class="mx-auto max-w-3xl">
            {{-- HEADER SUKSES --}}
            <div class="text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 sm:h-20 sm:w-20">
                    <x-heroicon-o-check class="h-8 w-8 text-emerald-600 sm:h-10 sm:w-10"/>
                </div>
                <p class="mt-4 text-[10px] font-semibold uppercase tracking-[0.3em] text-[#AE7C18] sm:mt-6 sm:text-xs">PESANAN DIBUAT</p>
                <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:mt-2 sm:text-4xl">Pesanan Berhasil Dibuat</h1>
                <p class="mx-auto mt-2 max-w-xl text-xs leading-5 text-gray-500 sm:mt-4 sm:text-base sm:leading-6">Terima kasih telah melakukan pemesanan di Eazywear. Silakan simpan nomor invoice berikut sebagai referensi pesanan Anda.</p>
            </div>

            {{-- KARTU PESANAN --}}
            <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm sm:mt-8 sm:rounded-3xl">
                {{-- INVOICE --}}
                <div class="bg-slate-50 px-5 py-5 sm:px-8 sm:py-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-[#AE7C18] sm:text-xs">Invoice</p>
                            <p class="mt-1 text-[10px] text-gray-500 sm:text-xs">Simpan nomor invoice ini untuk referensi pesanan Anda.</p>
                            <p class="mt-2 break-all text-lg font-extrabold tracking-wide text-slate-900 sm:mt-3 sm:text-2xl">{{ $transaction->invoice_number }}</p>
                        </div>
                        <span class="inline-flex shrink-0 rounded-full bg-amber-50 px-3 py-1 text-[10px] font-bold text-amber-700 sm:px-4 sm:py-1.5 sm:text-xs">{{ $transaction->status }}</span>
                    </div>
                </div>

                <div class="px-5 sm:px-8">
                    {{-- PELANGGAN & PENGIRIMAN --}}
                    <div class="grid gap-6 border-b border-gray-100 py-5 sm:grid-cols-2 sm:gap-10 sm:py-6">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 sm:text-xs">Pelanggan</p>
                            <p class="mt-1.5 text-sm font-bold text-slate-900 sm:text-base">{{ $transaction->shipping_name }}</p>
                            <p class="mt-1 text-xs text-gray-500 sm:text-sm">{{ $transaction->shipping_email }}</p>
                            <p class="mt-0.5 text-xs text-gray-500 sm:text-sm">{{ $transaction->shipping_phone }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 sm:text-xs">Alamat Pengiriman</p>
                            <p class="mt-1.5 text-sm font-bold leading-5 text-slate-900 sm:text-sm sm:leading-6">{{ $transaction->shipping_address }}</p>
                            <p class="mt-1 text-xs leading-5 text-gray-500 sm:text-sm sm:leading-6">{{ $transaction->shipping_district }}, {{ $transaction->shipping_city }}</p>
                            <p class="text-xs leading-5 text-gray-500 sm:text-sm sm:leading-6">{{ $transaction->shipping_province }} · {{ $transaction->shipping_postal_code }}</p>
                            @if($transaction->shipping_method)
                                <p class="mt-2 text-xs font-bold text-slate-900 sm:mt-3 sm:text-sm">Pengiriman: {{ $transaction->shipping_method }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- DETAIL PESANAN --}}
                    <div class="py-5 sm:py-6">
                        <h2 class="text-base font-bold text-slate-900 sm:text-lg">Detail Pesanan</h2>
                        <div class="mt-4 space-y-4 sm:mt-5 sm:space-y-5">
                            @foreach($transaction->items as $item)
                                <div class="flex items-start justify-between gap-3 text-xs sm:gap-4 sm:text-sm">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-slate-900">{{ $item->productVariant?->product?->name ?? '-' }}</p>
                                        <div class="mt-1 flex flex-wrap gap-x-3 text-[11px] text-gray-500 sm:text-xs">
                                            <span>Ukuran: {{ $item->productVariant?->size?->name ?? '-' }}</span>
                                            <span>Jumlah: {{ $item->qty }}</span>
                                        </div>
                                        @if(!empty($item->custom_name))
                                            <p class="mt-1.5 text-xs font-extrabold tracking-wide text-[#AE7C18] sm:text-sm">Nama Jersey: {{ $item->custom_name }}</p>
                                        @endif
                                        @if(!empty($item->custom_number))
                                            <p class="mt-1 text-xs font-extrabold tracking-wide text-slate-700 sm:text-sm">Nomor Punggung: {{ $item->custom_number }}</p>
                                        @endif
                                    </div>
                                    <p class="shrink-0 font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- RINGKASAN --}}
                    <div class="border-t border-gray-100 py-5 sm:py-6">
                        <div class="space-y-2.5 sm:space-y-3">
                            <div class="flex justify-between text-xs text-gray-600 sm:text-sm">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-600 sm:text-sm">
                                <span>Pengiriman</span>
                                <span>Rp {{ number_format($transaction->shipping, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-600 sm:text-sm">
                                <span>Metode Pembayaran</span>
                                <span class="font-bold text-slate-900">{{ $transaction->payment_method === 'TRANSFER' ? 'Transfer Bank' : 'QRIS' }}</span>
                            </div>
                            <div class="border-t border-gray-100 pt-3 sm:pt-4">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs font-bold text-slate-900 sm:text-base">Total</span>
                                    <span class="text-lg font-extrabold text-[#AE7C18] sm:text-2xl">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- NOTIFIKASI PEMBAYARAN --}}
            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:mt-6 sm:p-5">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-information-circle class="mt-0.5 h-5 w-5 shrink-0 text-amber-600"/>
                    <div>
                        <p class="text-xs font-semibold text-amber-900 sm:text-sm">Pesanan menunggu pembayaran</p>
                        <p class="mt-0.5 text-[11px] leading-4 text-amber-800 sm:mt-1 sm:text-sm sm:leading-6">Pesanan Anda telah tercatat dengan status <strong>PENDING</strong>. Instruksi pembayaran akan diaktifkan pada tahap pembayaran berikutnya.</p>
                    </div>
                </div>
            </div>

            {{-- TOMBOL --}}
            <div class="mt-6 flex flex-col gap-2.5 sm:mt-8 sm:flex-row sm:justify-center sm:gap-3">
                <a href="{{ route('catalog') }}" class="inline-flex h-11 items-center justify-center rounded-full border border-gray-200 bg-white px-6 text-xs font-semibold text-gray-700 transition hover:border-[#AE7C18] hover:text-[#AE7C18] sm:h-12 sm:text-sm">Belanja Lagi</a>
                <a href="{{ route('home') }}" class="inline-flex h-11 items-center justify-center rounded-full bg-[#AE7C18] px-6 text-xs font-semibold text-white shadow-md shadow-[#AE7C18]/20 transition hover:bg-[#8F6514] sm:h-12 sm:text-sm">Kembali ke Beranda</a>
            </div>
        </div>
    </x-ui.container>
</section>
@endsection