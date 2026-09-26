@extends('layouts.website')

@section('title', 'Detail Pesanan - Eazywear Indonesia')

@section('content')

@php
$statusMap = [
    'ORDER_CREATED' => [
        'label' => 'Pesanan Dibuat',
        'color' => 'bg-blue-50 text-blue-700 border-blue-200',
        'icon'  => 'shopping-cart',
    ],
    'PAYMENT_CONFIRMED' => [
        'label' => 'Pembayaran Berhasil',
        'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'icon'  => 'credit-card',
    ],
    'ORDER_PROCESSING' => [
        'label' => 'Pesanan Dipproses',
        'color' => 'bg-amber-50 text-amber-700 border-amber-200',
        'icon'  => 'cog',
    ],
    'ORDER_SHIPPED' => [
        'label' => 'Pesanan Dikirim',
        'color' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'icon'  => 'truck',
    ],
    'ORDER_COMPLETED' => [
        'label' => 'Pesanan Selesai',
        'color' => 'bg-green-50 text-green-700 border-green-200',
        'icon'  => 'check-circle',
    ],
    'ORDER_CANCELLED' => [
        'label' => 'Pesanan Dibatalkan',
        'color' => 'bg-red-50 text-red-700 border-red-200',
        'icon'  => 'x-circle',
    ],
    'EXPIRED' => [
        'label' => 'Pembayaran Kadaluarsa',
        'color' => 'bg-slate-100 text-slate-700 border-slate-200',
        'icon'  => 'clock',
    ],
];

$currentHistory = $transaction
    ->orderStatusHistories
    ->sortByDesc('created_at')
    ->first();

$currentStatus = $statusMap[$currentHistory?->status ?? $transaction->status] ?? [
    'label' => $transaction->status,
    'color' => 'bg-slate-100 text-slate-700 border-slate-200',
    'icon'  => 'clock',
];
@endphp

<section class="bg-slate-50/80 py-6 sm:py-10">
    <x-ui.container>
        <div class="mx-auto max-w-6xl space-y-4 sm:space-y-6">

            {{-- HEADER / INVOICE CARD --}}
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-[10px] font-bold tracking-wider uppercase text-slate-400">
                            No. Invoice
                        </p>
                        <h1 class="text-lg sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                            {{ $transaction->invoice_number }}
                        </h1>
                        <p class="mt-0.5 text-xs sm:text-sm text-slate-500">
                            Waktu Transaksi: {{ $transaction->created_at->setTimezone('Asia/Makassar')->format('d M Y, h:i A') }}
                        </p>
                    </div>

                    <div class="self-start sm:self-center">
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $currentStatus['color'] }}">
                            {{ $currentStatus['label'] }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- NAVIGATION BUTTONS (SIDE BY SIDE IN MOBILE & DESKTOP) --}}
            <div class="grid grid-cols-2 sm:flex sm:items-center sm:justify-between gap-3">
                <a
                    href="{{ route('orders.tracking') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-xl border border-slate-200 bg-white px-3 sm:px-5 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition"
                >
                    <x-heroicon-o-arrow-left class="h-3.5 w-3.5 sm:h-4 sm:w-4 shrink-0"/>
                    <span class="truncate">Cek Pesanan Lain</span>
                </a>

                <a
                    href="{{ route('home') }}"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 sm:gap-2 rounded-xl bg-[#AE7C18] px-3 sm:px-5 py-2.5 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-[#8F6514] transition"
                >
                    <x-heroicon-o-home class="h-3.5 w-3.5 sm:h-4 sm:w-4 shrink-0"/>
                    <span class="truncate">Kembali ke Home</span>
                </a>
            </div>

            {{-- MAIN GRID: LEFT (TIMELINE & PRODUCTS), RIGHT (SUMMARY & CUSTOMER INFO) --}}
            <div class="grid gap-6 lg:grid-cols-12 items-start">

                {{-- KOLOM KIRI --}}
                <div class="flex flex-col gap-6 lg:col-span-7">

                    {{-- TIMELINE STATUS --}}
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-sm">
                        <h2 class="mb-4 text-base font-bold text-slate-900">
                            Status Pesanan
                        </h2>

                        <div class="space-y-4">
                            @forelse($transaction->orderStatusHistories->sortBy('created_at') as $history)
                                @php
                                    $status = $statusMap[$history->status] ?? [
                                        'label' => $history->status,
                                        'icon'  => 'clock'
                                    ];
                                @endphp

                                <div class="flex gap-3 sm:gap-4">
                                    <div class="flex flex-col items-center">
                                        <div class="flex h-8 w-8 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-full bg-[#AE7C18]/10 text-[#AE7C18]">
                                            @switch($status['icon'])
                                                @case('shopping-cart')
                                                    <x-heroicon-o-shopping-cart class="h-4 w-4 sm:h-5 sm:w-5"/>
                                                    @break
                                                @case('credit-card')
                                                    <x-heroicon-o-credit-card class="h-4 w-4 sm:h-5 sm:w-5"/>
                                                    @break
                                                @case('cog')
                                                    <x-heroicon-o-cog-6-tooth class="h-4 w-4 sm:h-5 sm:w-5"/>
                                                    @break
                                                @case('truck')
                                                    <x-heroicon-o-truck class="h-4 w-4 sm:h-5 sm:w-5"/>
                                                    @break
                                                @case('check-circle')
                                                    <x-heroicon-o-check-circle class="h-4 w-4 sm:h-5 sm:w-5"/>
                                                    @break
                                                @case('x-circle')
                                                    <x-heroicon-o-x-circle class="h-4 w-4 sm:h-5 sm:w-5"/>
                                                    @break
                                                @default
                                                    <x-heroicon-o-clock class="h-4 w-4 sm:h-5 sm:w-5"/>
                                            @endswitch
                                        </div>

                                        @if(!$loop->last)
                                            <div class="h-full w-px bg-slate-200 my-1"></div>
                                        @endif
                                    </div>

                                    <div class="pb-2">
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ $status['label'] }}
                                        </p>

                                        @if($history->note)
                                            <p class="mt-0.5 text-xs sm:text-sm text-slate-600">
                                                {{ $history->note }}
                                            </p>
                                        @endif

                                        <p class="mt-0.5 text-[11px] text-slate-400">
                                            {{ $history->created_at->format('d M Y, H:i') }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl bg-slate-50 p-4 text-center text-xs sm:text-sm text-slate-500">
                                    Belum ada pembaruan status pesanan.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- DETAIL PRODUK --}}
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-sm">
                        <h2 class="mb-4 text-base font-bold text-slate-900">
                            Rincian Produk
                        </h2>

                        <div class="divide-y divide-slate-100">
                            @foreach($transaction->items as $item)
                                @php
                                    $product = $item->productVariant?->product;
                                @endphp

                                <div class="flex gap-3 py-3 first:pt-0 last:pb-0 sm:gap-4">
                                    <div class="h-14 w-14 sm:h-16 sm:w-16 shrink-0 overflow-hidden rounded-lg bg-slate-100 border border-slate-200/60">
                                        @if($product?->images?->first())
                                            <img src="{{ asset('storage/'.$product->images->first()->image) }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full items-center justify-center text-[10px] text-slate-400">
                                                No Image
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex flex-1 flex-col justify-between">
                                        <div>
                                            <p class="text-xs sm:text-sm font-semibold text-slate-900 line-clamp-2">
                                                {{ $product?->name ?? '-' }}
                                            </p>
                                            <p class="text-xs text-slate-500 mt-0.5">
                                                Ukuran: <span class="font-medium text-slate-700">{{ $item->productVariant?->size?->name ?? '-' }}</span>
                                            </p>

                                            @if($item->custom_name || $item->custom_number)
                                                <div class="mt-1 flex flex-wrap gap-x-3 text-xs">
                                                    @if($item->custom_name)
                                                        <span class="text-[#AE7C18] font-medium">
                                                            Nama Jersey: {{ $item->custom_name }}
                                                        </span>
                                                    @endif
                                                    @if($item->custom_number)
                                                        <span class="text-slate-600 font-medium">
                                                            No: {{ $item->custom_number }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                        <div class="mt-2 flex items-center justify-between text-xs sm:text-sm">
                                            <span class="text-slate-500">{{ $item->qty }} x Rp {{ number_format($item->price ?? ($item->subtotal / $item->qty), 0, ',', '.') }}</span>
                                            <span class="font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- KOLOM KANAN --}}
                <div class="flex flex-col gap-6 lg:col-span-5 h-full">

                    {{-- INFORMASI PELANGGAN & PENGIRIMAN --}}
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-sm">
                        <div class="mb-3.5 flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-[#AE7C18]">
                                <x-heroicon-o-user class="h-4 w-4"/>
                            </div>
                            <h2 class="text-base font-bold text-slate-900">
                                Informasi Pengiriman
                            </h2>
                        </div>

                        <div class="space-y-3 text-xs sm:text-sm">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Penerima</p>
                                <p class="mt-0.5 font-semibold text-slate-800">
                                    {{ $transaction->shipping_name ?? $transaction->customer?->name ?? '-' }}
                                </p>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Telepon</p>
                                    <p class="mt-0.5 font-medium text-slate-800">
                                        {{ $transaction->shipping_phone ?? '-' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Kurir / Metode</p>
                                    <p class="mt-0.5 font-medium text-slate-800">
                                        {{ $transaction->shipping_method ?? '-' }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Email</p>
                                <p class="mt-0.5 font-medium text-slate-800 break-all">
                                    {{ $transaction->shipping_email ?? '-' }}
                                </p>
                            </div>

                            <div class="border-t border-slate-100 pt-3">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Alamat Lengkap</p>
                                <p class="mt-0.5 font-medium leading-relaxed text-slate-800">
                                    {{ $transaction->shipping_address ?? '-' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $transaction->shipping_district }}, {{ $transaction->shipping_city }}, {{ $transaction->shipping_province }} {{ $transaction->shipping_postal_code }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- RINGKASAN PEMBAYARAN --}}
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-sm">
                        <h2 class="mb-4 text-base font-bold text-slate-900">
                            Ringkasan Pembayaran
                        </h2>

                        <div class="space-y-2.5 text-xs sm:text-sm">
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal Produk</span>
                                <span class="font-medium text-slate-900">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                            </div>

                            <div class="flex justify-between text-slate-600">
                                <span>Biaya Pengiriman</span>
                                <span class="font-medium text-slate-900">Rp {{ number_format($transaction->shipping, 0, ',', '.') }}</span>
                            </div>

                            <div class="border-t border-slate-100 pt-3 mt-3 flex items-center justify-between">
                                <span class="font-bold text-slate-900 text-sm sm:text-base">Total Bayar</span>
                                <span class="text-base sm:text-lg font-extrabold text-[#AE7C18]">
                                    Rp {{ number_format($transaction->total, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>



                </div>

            </div>

        </div>
    </x-ui.container>
</section>

@endsection