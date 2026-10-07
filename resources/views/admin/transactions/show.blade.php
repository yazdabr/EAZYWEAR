@extends('admin.layouts.app')

@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi')

@section('content')

@php
$statusMap = [
    'ORDER_CREATED' => ['label' => 'Pesanan Dibuat', 'color' => 'bg-blue-100 text-blue-700', 'icon' => 'shopping-cart'],
    'PAYMENT_CONFIRMED' => ['label' => 'Pembayaran Dikonfirmasi', 'color' => 'bg-emerald-100 text-emerald-700', 'icon' => 'credit-card'],
    'ORDER_PROCESSING' => ['label' => 'Pesanan Diproses', 'color' => 'bg-amber-100 text-amber-700', 'icon' => 'cog'],
    'ORDER_SHIPPED' => ['label' => 'Pesanan Dikirim', 'color' => 'bg-indigo-100 text-indigo-700', 'icon' => 'truck'],
    'ORDER_COMPLETED' => ['label' => 'Pesanan Selesai', 'color' => 'bg-green-100 text-green-700', 'icon' => 'check-circle'],
    'ORDER_CANCELLED' => ['label' => 'Pesanan Dibatalkan', 'color' => 'bg-red-100 text-red-700', 'icon' => 'x-circle'],
];

$latestHistory = $transaction->orderStatusHistories->sortByDesc('created_at')->first();
$currentStatus = $statusMap[$latestHistory?->status ?? ''] ?? ['label' => $transaction->status, 'color' => 'bg-slate-100 text-slate-700', 'icon' => 'clock'];
@endphp

<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <h1 class="text-lg sm:text-xl font-bold text-slate-900 break-all">{{ $transaction->invoice_number }}</h1>
                <span class="rounded-full px-2.5 py-0.5 sm:px-3 sm:py-1 text-xs font-bold {{ $currentStatus['color'] }}">{{ $currentStatus['label'] }}</span>
            </div>
            <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-slate-500">{{ $transaction->transaction_date?->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y H:i') }} WITA</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            @if($transaction->status === 'PAID')
                <button type="button" onclick="processOrder('{{ $transaction->id }}')" id="process-order-btn" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/30">
                    <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                    Proses Pesanan
                </button>
            @endif

            @if($transaction->payment_method === 'VA' && $transaction->status === 'PENDING')
                <button type="button" onclick="checkDokuPayment('{{ $transaction->id }}')" id="check-doku-btn" class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                    Cek Pembayaran
                </button>
            @endif

            <a href="{{ route('admin.transactions') }}" class="inline-flex items-center justify-center whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Kembali</a>
            <a href="{{ route('admin.transactions.print', $transaction->invoice_number) }}" target="_blank" class="inline-flex items-center justify-center whitespace-nowrap rounded-xl bg-[#AE7C18] px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F]">Cetak Invoice</a>
        </div>
    </div>

    <div class="rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
        <!-- Header -->
        <div class="mb-5 flex items-center gap-2.5 sm:gap-3 border-b border-slate-100 pb-4">
            <div class="flex h-9 w-9 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]">
                <x-heroicon-o-user class="h-5 w-5" />
            </div>
            <div>
                <h2 class="font-bold text-slate-900 text-sm sm:text-base">Informasi Pelanggan</h2>
                <p class="text-xs text-slate-500">Rincian kontak dan pengiriman pesanan</p>
            </div>
        </div>

        <!-- Grid Content (2 Kolom Seimbang) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs sm:text-sm">
            
            <!-- Seksi 1: Kontak Pelanggan -->
            <div class="space-y-4 rounded-xl bg-slate-50/70 p-4 border border-slate-100">
                <span class="text-[12px] font-bold uppercase tracking-wider text-[#AE7C18]">Kontak Pelanggan</span>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                    <div class="sm:col-span-2">
                        <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">Nama</p>
                        <p class="mt-0.5 font-semibold text-slate-900">{{ $transaction->shipping_name ?? $transaction->customer?->name }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">Telepon</p>
                        <p class="mt-0.5 font-semibold text-slate-900">{{ $transaction->shipping_phone ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">Email</p>
                        <p class="mt-0.5 font-semibold text-slate-900 break-all">{{ $transaction->shipping_email ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Seksi 2: Metode & Alamat Pengiriman -->
            <div class="space-y-4 rounded-xl bg-slate-50/70 p-4 border border-slate-100 flex flex-col justify-between">
                <div class="space-y-3.5">
                    <span class="text-[12px] font-bold uppercase tracking-wider text-[#AE7C18]">Detail Logistik</span>
                    
                    <!-- Grid Berdampingan untuk Metode & Jadwal -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 pt-1">
                        <div>
                            <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">Metode Pengiriman</p>
                            <p class="mt-0.5 font-semibold text-slate-900">{{ $transaction->shipping_method ?? '-' }}</p>
                        </div>

                        @if(
                            $transaction->shipping_method === 'Ambil di Tempat' &&
                            $transaction->pickup_date &&
                            $transaction->pickup_time_start &&
                            $transaction->pickup_time_end
                        )
                            <div>
                                <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">Jadwal Pengambilan</p>
                                <p class="mt-0.5 font-semibold text-slate-900">
                                    {{ \Carbon\Carbon::parse($transaction->pickup_date)->locale('id')->translatedFormat('l, d F Y') }}<span class="font-normal text-[#AE7C18]">, {{ \Carbon\Carbon::parse($transaction->pickup_time_start)->format('H:i') }}–{{ \Carbon\Carbon::parse($transaction->pickup_time_end)->format('H:i') }} WITA</span>
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Alamat Pengiriman -->
                    <div class="pt-1">
                        <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">Alamat Pengiriman</p>
                        <p class="mt-0.5 leading-snug font-semibold text-slate-900">{{ $transaction->shipping_address }}</p>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $transaction->shipping_district }}, {{ $transaction->shipping_city }}, {{ $transaction->shipping_province }} {{ $transaction->shipping_postal_code }}</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <div class="h-full rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <div class="mb-4 sm:mb-5 flex items-center gap-2.5 sm:gap-3">
                    <div class="flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]"><x-heroicon-o-shopping-bag class="h-4 w-4 sm:h-5 sm:w-5" /></div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base">Produk</h2>
                </div>

                <div class="space-y-3 sm:space-y-4">
                    @foreach($transaction->items as $item)
                        @php $product = $item->productVariant?->product; @endphp
                        <div class="flex items-start sm:items-center gap-3 sm:gap-4 rounded-xl sm:rounded-2xl bg-slate-50 p-3 sm:p-4">
                            <div class="h-12 w-12 sm:h-16 sm:w-16 shrink-0 overflow-hidden rounded-lg sm:rounded-xl bg-slate-200">
                                @if($product?->images?->first())
                                    <img src="{{ asset('storage/'.$product->images->first()->image) }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full items-center justify-center text-[10px] sm:text-xs text-slate-400">No Image</div>
                                @endif
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-slate-900 text-xs sm:text-base">{{ $product?->name ?? '-' }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">Ukuran: {{ $item->productVariant?->size?->name ?? '-' }}</p>
                                @if($item->custom_name)<p class="text-xs text-[#AE7C18]">Nama: {{ $item->custom_name }}</p>@endif
                                @if($item->custom_number)<p class="text-xs text-slate-600">Nomor: {{ $item->custom_number }}</p>@endif
                            </div>

                            <div class="text-right shrink-0">
                                <p class="font-bold text-slate-900 text-xs sm:text-sm">Rp {{ number_format($item->subtotal,0,',','.') }}</p>
                                <p class="text-[10px] sm:text-xs text-slate-400">{{ $item->qty }} pcs</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="space-y-4 sm:space-y-6">
            <div class="rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <h2 class="mb-3 sm:mb-4 font-bold text-slate-900 text-sm sm:text-base">Pembayaran</h2>
                <div class="space-y-2.5 text-xs sm:text-sm">
                    <div class="flex justify-between gap-4"><span class="text-slate-500">Metode</span><span class="font-bold text-slate-900 text-right">{{ $transaction->payment_method }}</span></div>

                    @if($transaction->va_number)
                        <div class="flex justify-between gap-4"><span class="text-slate-500">VA Number</span><span class="font-bold text-slate-900 text-right break-all">{{ $transaction->va_number }}</span></div>
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">Expired</span>
                            <span class="text-slate-700 text-right">{{ $transaction->va_expired_at ? $transaction->va_expired_at->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y H:i') . ' WITA' : '-' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <h2 class="mb-3 sm:mb-4 font-bold text-slate-900 text-sm sm:text-base">Ringkasan</h2>
                <div class="space-y-2.5 text-xs sm:text-sm">
                    <div class="flex justify-between gap-4"><span class="text-slate-500">Subtotal</span><span class="font-medium text-slate-900">Rp {{ number_format($transaction->subtotal,0,',','.') }}</span></div>
                    <div class="flex justify-between gap-4"><span class="text-slate-500">Diskon</span><span class="font-medium text-slate-900">Rp {{ number_format($transaction->discount,0,',','.') }}</span></div>
                    <div class="flex justify-between gap-4"><span class="text-slate-500">Pengiriman</span><span class="font-medium text-slate-900">Rp {{ number_format($transaction->shipping,0,',','.') }}</span></div>
                    <div class="border-t border-slate-100 pt-3 flex justify-between gap-4 text-sm sm:text-base font-bold"><span class="text-slate-900">Total</span><span class="text-[#AE7C18]">Rp {{ number_format($transaction->total,0,',','.') }}</span></div>
                </div>
            </div>
        </div>
    </div>

    @if(in_array($transaction->status, ['ORDER_PROCESSING', 'ORDER_SHIPPED', 'ORDER_COMPLETED'], true))
        <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="h-full rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                    <div class="mb-4 sm:mb-5 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 sm:gap-3">
                            <div class="flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]">
                                <x-heroicon-o-truck class="h-4 w-4 sm:h-5 sm:w-5" />
                            </div>

                            <div>
                                <h2 class="font-bold text-slate-900 text-sm sm:text-base">
                                    Pengiriman
                                </h2>

                                <p class="mt-0.5 text-[10px] sm:text-xs text-slate-400">
                                    Pengiriman otomatis melalui Biteship
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($transaction->status === 'ORDER_PROCESSING' && $transaction->shipping_method === 'Kurir')

                        {{-- BELUM ADA PENGIRIMAN --}}
                        <div class="rounded-2xl border border-[#AE7C18]/20 bg-[#AE7C18]/5 p-5 sm:p-6">
                            <div class="flex flex-col items-center text-center">

                                <div class="flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-full bg-[#AE7C18]/10 text-[#AE7C18]">
                                    <x-heroicon-o-truck class="h-6 w-6 sm:h-7 sm:w-7" />
                                </div>

                                <p class="mt-4 font-bold text-slate-900 text-sm sm:text-base">
                                    Siap Diproses
                                </p>

                                <p class="mt-1.5 max-w-lg text-xs sm:text-sm leading-relaxed text-slate-500">
                                    Pengiriman akan dibuat otomatis melalui
                                    <strong class="text-slate-700">Biteship</strong>.
                                    Kurir dan nomor resi akan ditentukan secara otomatis
                                    berdasarkan layanan pengiriman yang tersedia.
                                </p>

                                <div class="mt-4 w-full rounded-xl border border-slate-200 bg-white p-3 sm:p-4">
                                    <div class="flex items-center justify-center gap-2 text-xs sm:text-sm text-slate-600">
                                        <x-heroicon-o-information-circle class="h-4 w-4 shrink-0 text-[#AE7C18]" />

                                        <span>
                                            Tidak perlu memasukkan kurir atau nomor resi secara manual.
                                        </span>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    id="ship-order-btn"
                                    onclick="shipOrder('{{ $transaction->id }}')"
                                    class="mt-5 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/30"
                                >
                                    <x-heroicon-o-truck class="h-4 w-4" />
                                    Proses Pengiriman
                                </button>

                                <p class="mt-3 text-[11px] leading-relaxed text-slate-400">
                                    Setelah pengiriman berhasil dibuat, informasi kurir dan nomor resi
                                    akan muncul otomatis di halaman ini.
                                </p>
                            </div>
                        </div>

                    @elseif($transaction->status === 'ORDER_PROCESSING' && $transaction->shipping_method === 'Ambil di Tempat')

                        {{-- AMBIL DI TEMPAT --}}
                        <div class="flex flex-col items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 p-6 text-center">

                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                <x-heroicon-o-check-circle class="h-6 w-6" />
                            </div>

                            <p class="mt-3 font-bold text-slate-900">
                                Pesanan Ambil di Tempat
                            </p>

                            <p class="mt-1 max-w-md text-xs sm:text-sm leading-relaxed text-slate-500">
                                Pesanan ini akan diselesaikan secara manual setelah pelanggan
                                mengambil pesanan langsung di Kantor Eazywear.
                            </p>

                            <button
                                type="button"
                                id="complete-order-btn"
                                onclick="completeOrder('{{ $transaction->id }}')"
                                class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/30"
                            >
                                <x-heroicon-o-check-circle class="h-4 w-4" />
                                Konfirmasi Pesanan Diambil
                            </button>

                            <p class="mt-3 text-[11px] text-slate-400">
                                Tombol ini hanya digunakan setelah pesanan benar-benar diambil pelanggan.
                            </p>
                        </div>

                    @else

                        {{-- PENGIRIMAN SUDAH DIBUAT --}}
                        <div id="shipping-display">

                            <div class="grid gap-3 sm:grid-cols-2">

                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <p class="text-[10px] uppercase font-medium tracking-wide text-slate-400">
                                        Kurir
                                    </p>

                                    <p class="mt-1.5 font-bold text-slate-900 text-sm sm:text-base">
                                        {{ $transaction->courier ?? '-' }}
                                    </p>
                                </div>

                                <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                    <p class="text-[10px] uppercase font-medium tracking-wide text-slate-400">
                                        Nomor Resi
                                    </p>

                                    <p class="mt-1.5 font-bold text-slate-900 text-sm sm:text-base break-all">
                                        {{ $transaction->tracking_number ?? '-' }}
                                    </p>
                                </div>

                            </div>

                            @if($transaction->biteship_order_id)
                                <div class="mt-3 grid gap-3 sm:grid-cols-2">

                                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                        <p class="text-[10px] uppercase font-medium tracking-wide text-slate-400">
                                            Status Biteship
                                        </p>

                                        <p class="mt-1.5 font-bold text-slate-900 text-sm sm:text-base">
                                            {{ $transaction->biteship_status
                                                ? str_replace('_', ' ', ucfirst($transaction->biteship_status))
                                                : '-' }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl border border-slate-100 bg-slate-50 p-4">
                                        <p class="text-[10px] uppercase font-medium tracking-wide text-slate-400">
                                            Biteship Order ID
                                        </p>

                                        <p class="mt-1.5 font-semibold text-slate-700 text-xs sm:text-sm break-all">
                                            {{ $transaction->biteship_order_id }}
                                        </p>
                                    </div>

                                </div>
                            @endif

                            @if($transaction->status === 'ORDER_SHIPPED' && $transaction->shipping_method === 'Kurir')

                                <div class="mt-4 rounded-xl border border-indigo-100 bg-indigo-50 p-4">
                                    <div class="flex items-start gap-3">

                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                                            <x-heroicon-o-information-circle class="h-5 w-5" />
                                        </div>

                                        <div class="min-w-0">

                                            <p class="text-sm font-bold text-indigo-900">
                                                Menunggu Konfirmasi Pengiriman
                                            </p>

                                            <p class="mt-1 text-xs sm:text-sm leading-relaxed text-indigo-700">
                                                Pesanan ini dikirim melalui Biteship.
                                                Status pesanan akan diperbarui otomatis setelah Biteship
                                                menerima status
                                                <strong>delivered</strong>.
                                            </p>

                                            @if($transaction->biteship_status)
                                                <p class="mt-2 text-xs text-indigo-600">
                                                    Status Biteship:
                                                    <span class="font-bold">
                                                        {{ str_replace('_', ' ', ucfirst($transaction->biteship_status)) }}
                                                    </span>
                                                </p>
                                            @endif

                                        </div>
                                    </div>
                                </div>

                            @elseif($transaction->status === 'ORDER_COMPLETED' && $transaction->shipping_method === 'Kurir')

                                <div class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                                    <div class="flex items-start gap-3">

                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                            <x-heroicon-o-check-circle class="h-5 w-5" />
                                        </div>

                                        <div class="min-w-0">

                                            <p class="text-sm font-bold text-emerald-900">
                                                Pengiriman Selesai
                                            </p>

                                            <p class="mt-1 text-xs sm:text-sm leading-relaxed text-emerald-700">
                                                Pesanan telah diterima pelanggan dan status
                                                pengiriman telah dikonfirmasi oleh Biteship.
                                            </p>

                                            @if($transaction->biteship_status)
                                                <p class="mt-2 text-xs text-emerald-600">
                                                    Status Biteship:
                                                    <span class="font-bold">
                                                        {{ str_replace('_', ' ', ucfirst($transaction->biteship_status)) }}
                                                    </span>
                                                </p>
                                            @endif

                                        </div>
                                    </div>
                                </div>

                            @endif

                        </div>

                    @endif
                </div>
            </div>
            <div>
                <div class="h-full rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                    <h2 class="mb-4 sm:mb-5 font-bold text-slate-900 text-sm sm:text-base">Timeline Status</h2>

                    <div class="space-y-4 sm:space-y-5">
                        @foreach($transaction->orderStatusHistories->sortBy('created_at') as $history)
                            @php $status = $statusMap[$history->status] ?? null; @endphp

                            <div class="flex gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#AE7C18]/10 text-[#AE7C18]">
                                    @switch($status['icon'] ?? null)
                                        @case('shopping-cart') <x-heroicon-o-shopping-cart class="h-4 w-4"/> @break
                                        @case('credit-card') <x-heroicon-o-credit-card class="h-4 w-4"/> @break
                                        @case('cog') <x-heroicon-o-cog-6-tooth class="h-4 w-4"/> @break
                                        @case('truck') <x-heroicon-o-truck class="h-4 w-4"/> @break
                                        @case('check-circle') <x-heroicon-o-check-circle class="h-4 w-4"/> @break
                                        @case('x-circle') <x-heroicon-o-x-circle class="h-4 w-4"/> @break
                                        @default <x-heroicon-o-clock class="h-4 w-4"/>
                                    @endswitch
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-slate-900 text-xs sm:text-sm">{{ $status['label'] ?? $history->status }}</p>
                                    @if($history->note)<p class="mt-0.5 text-xs leading-relaxed text-slate-500">{{ $history->note }}</p>@endif
                                    <p class="mt-0.5 text-[10px] sm:text-xs text-slate-400">{{ $history->created_at->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y H:i') }} WITA</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Confirmation Modal -->
<div
    id="action-confirmation-modal"
    class="fixed inset-0 z-[100] hidden"
    aria-labelledby="confirmation-modal-title"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div
        id="confirmation-modal-backdrop"
        class="absolute inset-0 bg-slate-900/50 backdrop-blur-[2px] transition-opacity"
    ></div>

    <!-- Modal -->
    <div class="relative flex min-h-full items-center justify-center p-4 sm:p-6">
        <div
            id="confirmation-modal-panel"
            class="w-full max-w-md scale-95 rounded-2xl sm:rounded-3xl bg-white p-5 sm:p-6 shadow-2xl transition-transform"
        >
            <!-- Icon -->
            <div class="flex items-start gap-3 sm:gap-4">
                <div
                    id="confirmation-modal-icon"
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]"
                >
                    <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
                </div>

                <div class="min-w-0 flex-1">
                    <h3
                        id="confirmation-modal-title"
                        class="text-sm sm:text-base font-bold text-slate-900"
                    >
                        Konfirmasi
                    </h3>

                    <p
                        id="confirmation-modal-message"
                        class="mt-1.5 text-xs sm:text-sm leading-relaxed text-slate-500"
                    >
                        Apakah Anda yakin ingin melanjutkan?
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-5 sm:mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                <button
                    type="button"
                    id="confirmation-modal-cancel"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-200"
                >
                    Batal
                </button>

                <button
                    type="button"
                    id="confirmation-modal-confirm"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/30"
                >
                    <x-heroicon-o-check class="h-4 w-4" />
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function checkDokuPayment(id) {
    const btn = document.getElementById('check-doku-btn');
    if (!btn) return;
    btn.disabled = true;
    btn.innerText = 'Mengecek...';

    fetch(`/admin/transactions/${id}/check-payment`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    })
    .catch(() => {
        alert('Terjadi kesalahan.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerText = 'Cek Pembayaran';
    });
}

function processOrder(id) {
    const btn = document.getElementById('process-order-btn');
    if (!btn) return;
    btn.disabled = true;
    btn.innerText = 'Memproses...';

    fetch(`/admin/transactions/${id}/process`, {
        method: 'PATCH',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'}
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) location.reload();
    })
    .catch(() => {
        alert('Terjadi kesalahan.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerText = 'Proses Pesanan';
    });
}

let confirmationAction = null;

function openConfirmationModal({
    title,
    message,
    confirmText = 'Ya, Lanjutkan',
    action
}) {
    const modal = document.getElementById('action-confirmation-modal');
    const panel = document.getElementById('confirmation-modal-panel');
    const titleEl = document.getElementById('confirmation-modal-title');
    const messageEl = document.getElementById('confirmation-modal-message');
    const confirmBtn = document.getElementById('confirmation-modal-confirm');

    if (!modal || !panel || !titleEl || !messageEl || !confirmBtn) return;

    titleEl.textContent = title;
    messageEl.textContent = message;
    confirmBtn.innerHTML = `
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
            />
        </svg>
        ${confirmText}
    `;

    confirmationAction = action;

    modal.classList.remove('hidden');

    requestAnimationFrame(() => {
        panel.classList.remove('scale-95');
        panel.classList.add('scale-100');
    });
}

function closeConfirmationModal() {
    const modal = document.getElementById('action-confirmation-modal');
    const panel = document.getElementById('confirmation-modal-panel');

    if (!modal || !panel) return;

    panel.classList.remove('scale-100');
    panel.classList.add('scale-95');

    setTimeout(() => {
        modal.classList.add('hidden');
        confirmationAction = null;
    }, 150);
}

document.getElementById('confirmation-modal-cancel')?.addEventListener('click', () => {
    closeConfirmationModal();
});

document.getElementById('confirmation-modal-backdrop')?.addEventListener('click', () => {
    closeConfirmationModal();
});

document.getElementById('confirmation-modal-confirm')?.addEventListener('click', () => {
    if (typeof confirmationAction === 'function') {
        const action = confirmationAction;

        confirmationAction = null;
        closeConfirmationModal();

        action();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        const modal = document.getElementById('action-confirmation-modal');

        if (modal && !modal.classList.contains('hidden')) {
            closeConfirmationModal();
        }
    }
});

function shipOrder(id) {
    openConfirmationModal({
        title: 'Konfirmasi Proses Pengiriman',
        message: 'Pastikan barang sudah siap untuk diantar. Setelah dikonfirmasi, sistem akan membuat pengiriman melalui Biteship dan nomor resi akan dibuat secara otomatis.',
        confirmText: 'Ya, Proses Pengiriman',
        action: () => executeShipOrder(id)
    });
}

function executeShipOrder(id) {
    const btn = document.getElementById('ship-order-btn');

    if (!btn) return;

    btn.disabled = true;
    btn.innerHTML = `
        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v3m6.364.636l-2.121 2.121M21 12h-3m-.636 6.364l-2.121-2.121M12 21v-3m-6.364-.636l2.121-2.121M3 12h3m.636-6.364l2.121 2.121"
            />
        </svg>
        Membuat Pengiriman...
    `;

    fetch(`/admin/transactions/${id}/ship`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(async res => {
        const contentType = res.headers.get('content-type') || '';

        if (!contentType.includes('application/json')) {
            const text = await res.text();

            console.error('Response bukan JSON:', {
                status: res.status,
                contentType,
                response: text
            });

            throw {
                message: `Server mengembalikan response tidak valid (HTTP ${res.status}).`
            };
        }

        const data = await res.json();

        if (!res.ok) {
            throw data;
        }

        return data;
    })
    .then(data => {
        alert(data.message);

        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        console.error('Gagal membuat pengiriman:', error);

        alert(
            error?.message ??
            'Terjadi kesalahan saat membuat pengiriman.'
        );
    })
    .finally(() => {
        btn.disabled = false;

        btn.innerHTML = `
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 7l2-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"
                />
            </svg>
            Proses Pengiriman
        `;
    });
}


function completeOrder(id) {
    openConfirmationModal({
        title: 'Konfirmasi Pesanan Diambil',
        message: 'Pastikan pesanan sudah siap dan benar-benar sudah diambil oleh pelanggan. Jika dilanjutkan, pesanan akan langsung ditandai sebagai selesai.',
        confirmText: 'Ya, Pesanan Sudah Diambil',
        action: () => executeCompleteOrder(id)
    });
}

function executeCompleteOrder(id) {
    const btn = document.getElementById('complete-order-btn');

    if (!btn) return;

    btn.disabled = true;

    btn.innerHTML = `
        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v3m6.364.636l-2.121 2.121M21 12h-3m-.636 6.364l-2.121-2.121M12 21v-3m-6.364-.636l2.121-2.121M3 12h3m.636-6.364l2.121 2.121"
            />
        </svg>
        Menyelesaikan...
    `;

    fetch(`/admin/transactions/${id}/complete`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(async res => {
        const data = await res.json();

        if (!res.ok) {
            throw data;
        }

        return data;
    })
    .then(data => {
        alert(data.message);

        if (data.success) {
            location.reload();
        }
    })
    .catch(error => {
        alert(
            error?.message ??
            'Terjadi kesalahan saat menyelesaikan pesanan.'
        );
    })
    .finally(() => {
        btn.disabled = false;

        btn.innerHTML = `
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7"
                />
            </svg>
            Konfirmasi Pesanan Diambil
        `;
    });
}
</script>

@endsection
