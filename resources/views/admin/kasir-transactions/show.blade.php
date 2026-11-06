@extends('admin.layouts.app')

@section('title', 'Detail Transaksi Kasir')
@section('page-title', 'Detail Transaksi Kasir')

@section('content')

@php
$statusMap = [
    'ORDER_CREATED' => ['label' => 'Pesanan Dibuat', 'color' => 'bg-blue-100 text-blue-700', 'icon' => 'shopping-cart'],
    'PAYMENT_CONFIRMED' => ['label' => 'Pembayaran Dikonfirmasi', 'color' => 'bg-emerald-100 text-emerald-700', 'icon' => 'credit-card'],
    'PENDING' => ['label' => 'Menunggu Pembayaran', 'color' => 'bg-amber-100 text-amber-700', 'icon' => 'clock'],
    'PAID' => ['label' => 'Pembayaran Berhasil', 'color' => 'bg-emerald-100 text-emerald-700', 'icon' => 'credit-card'],
    'ORDER_PROCESSING' => ['label' => 'Pesanan Diproses', 'color' => 'bg-amber-100 text-amber-700', 'icon' => 'cog'],
    'ORDER_SHIPPED' => ['label' => 'Pesanan Dikirim', 'color' => 'bg-indigo-100 text-indigo-700', 'icon' => 'truck'],
    'ORDER_COMPLETED' => ['label' => 'Pesanan Selesai', 'color' => 'bg-green-100 text-green-700', 'icon' => 'check-circle'],
    'ORDER_CANCELLED' => ['label' => 'Pesanan Dibatalkan', 'color' => 'bg-red-100 text-red-700', 'icon' => 'x-circle'],
    'CANCELLED' => ['label' => 'Pesanan Dibatalkan', 'color' => 'bg-red-100 text-red-700', 'icon' => 'x-circle'],
];

$latestHistory = $transaction->orderStatusHistories->sortByDesc('created_at')->first();
$currentStatus = $statusMap[$latestHistory?->status ?? $transaction->status ?? '']
    ?? ['label' => $transaction->status, 'color' => 'bg-slate-100 text-slate-700', 'icon' => 'clock'];
@endphp

<div class="space-y-4 sm:space-y-6">
    {{-- Header transaksi --}}
    <div class="flex flex-col gap-4 rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <h1 class="text-lg sm:text-xl font-bold text-slate-900 break-all">{{ $transaction->invoice_number }}</h1>
                <span class="rounded-full px-2.5 py-0.5 sm:px-3 sm:py-1 text-xs font-bold {{ $currentStatus['color'] }}">{{ $currentStatus['label'] }}</span>
                <span class="rounded-full bg-[#AE7C18]/10 px-2.5 py-0.5 text-xs font-semibold text-[#8A6110] sm:px-3 sm:py-1">
                    POS Kasir
                </span>
            </div>
            <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-slate-500">
                {{ $transaction->transaction_date?->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y H:i') }} WITA
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <a href="{{ route('admin.kasir-transactions.index') }}"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <x-heroicon-o-arrow-left class="mr-1.5 h-4 w-4" />
                Kembali
            </a>

            <a href="{{ route('admin.kasir-transactions.print', $transaction->invoice_number) }}"
                target="_blank"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-xl bg-[#AE7C18] px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F]">
                <x-heroicon-o-printer class="mr-1.5 h-4 w-4" />
                Cetak Invoice
            </a>
        </div>
    </div>

    {{-- Informasi pelanggan --}}
    <div class="rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
        <div class="mb-5 flex items-center gap-2.5 sm:gap-3 border-b border-slate-100 pb-4">
            <div class="flex h-9 w-9 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]">
                <x-heroicon-o-user class="h-5 w-5" />
            </div>
            <div>
                <h2 class="font-bold text-slate-900 text-sm sm:text-base">Informasi Pelanggan</h2>
                <p class="text-xs text-slate-500">Rincian kontak pelanggan transaksi kasir</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs sm:text-sm">
            <div class="rounded-xl bg-slate-50/70 p-4 border border-slate-100">
                <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">Nama</p>
                <p class="mt-0.5 font-semibold text-slate-900">
                    {{ $transaction->customer?->name ?? $transaction->shipping_name ?? 'Pelanggan Kasir' }}
                </p>
            </div>
            <div class="rounded-xl bg-slate-50/70 p-4 border border-slate-100">
                <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">Telepon</p>
                <p class="mt-0.5 font-semibold text-slate-900">
                    {{ $transaction->customer?->phone ?? $transaction->shipping_phone ?? '-' }}
                </p>
            </div>
            <div class="rounded-xl bg-slate-50/70 p-4 border border-slate-100">
                <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">Email</p>
                <p class="mt-0.5 font-semibold text-slate-900 break-all">
                    {{ $transaction->customer?->email ?? $transaction->shipping_email ?? '-' }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 items-start gap-4 sm:gap-6 lg:grid-cols-3">
        {{-- Produk --}}
        <div class="h-full rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
            <div class="mb-4 sm:mb-5 flex items-center gap-2.5 sm:gap-3">
                <div class="flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]">
                    <x-heroicon-o-shopping-bag class="h-4 w-4 sm:h-5 sm:w-5" />
                </div>
                <h2 class="font-bold text-slate-900 text-sm sm:text-base">Produk</h2>
            </div>

            <div class="space-y-3 sm:space-y-4">
                @forelse($transaction->items as $item)
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
                            <p class="text-xs font-semibold text-slate-900 sm:text-base">
                                {{ $product?->name ?? '-' }}
                            </p>

                            <p class="mt-1 text-xs sm:text-sm">
                                <span class="font-semibold text-slate-900">Ukuran:</span>
                                <span class="font-bold text-[#AE7C18]">{{ $item->productVariant?->size?->name ?? '-' }}</span>
                            </p>

                            @if($item->custom_name)
                                <p class="mt-0.5 text-xs sm:text-sm">
                                    <span class="font-semibold text-slate-900">Nama:</span>
                                    <span class="font-bold uppercase text-[#AE7C18]">{{ mb_strtoupper($item->custom_name, 'UTF-8') }}</span>
                                </p>
                            @endif

                            @if($item->custom_number)
                                <p class="mt-0.5 text-xs sm:text-sm">
                                    <span class="font-semibold text-slate-900">Nomor:</span>
                                    <span class="font-bold text-[#AE7C18]">{{ $item->custom_number }}</span>
                                </p>
                            @endif

                            @if($item->is_longsleeve)
                                <p class="mt-1 text-xs sm:text-sm">
                                    <span class="font-semibold text-slate-900">Longsleeve</span>
                                    <span class="font-semibold text-[#AE7C18]">
                                        (+ Rp {{ number_format((int) $item->longsleeve_price, 0, ',', '.') }})
                                    </span>
                                </p>
                            @endif

                            @if($item->is_patch)
                                <p class="mt-1 text-xs sm:text-sm">
                                    <span class="font-semibold text-slate-900">Patch</span>
                                    <span class="font-semibold text-[#AE7C18]">
                                        (+ Rp {{ number_format((int) $item->patch_price, 0, ',', '.') }})
                                    </span>
                                </p>
                            @endif
                        </div>

                        <div class="text-right shrink-0">
                            <p class="font-bold text-slate-900 text-xs sm:text-sm">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </p>
                            <p class="text-[10px] sm:text-xs text-slate-400">{{ $item->qty }} pcs</p>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
                        Tidak ada rincian produk untuk transaksi ini.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="space-y-4 sm:space-y-6">
            {{-- Pembayaran --}}
            <div class="rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <h2 class="mb-3 sm:mb-4 font-bold text-slate-900 text-sm sm:text-base">Pembayaran</h2>
                <div class="space-y-2.5 text-xs sm:text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="font-medium text-slate-500">Metode</span>
                        <span class="font-bold text-slate-900 text-right">{{ $transaction->payment_method ?? '-' }}</span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="font-medium text-slate-500">Status</span>
                        <span class="font-bold text-slate-900 text-right">{{ $currentStatus['label'] }}</span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="font-medium text-slate-500">Waktu Transaksi</span>
                        <span class="font-bold text-slate-900 text-right">
                            {{ $transaction->transaction_date?->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y H:i') ?? '-' }} WITA
                        </span>
                    </div>

                    @if($transaction->paid_at)
                        <div class="flex justify-between gap-4">
                            <span class="font-medium text-slate-500">Waktu Pembayaran</span>
                            <span class="font-bold text-slate-900 text-right">
                                {{ $transaction->paid_at->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y H:i') }} WITA
                            </span>
                        </div>
                    @endif

                    @if($transaction->qris_reference_no)
                        <div class="flex justify-between gap-4">
                            <span class="font-medium text-slate-500">Referensi QRIS</span>
                            <span class="font-bold text-slate-900 text-right break-all">{{ $transaction->qris_reference_no }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Ringkasan --}}
            <div class="rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <h2 class="mb-3 sm:mb-4 font-bold text-slate-900 text-sm sm:text-base">Ringkasan</h2>
                <div class="space-y-2.5 text-xs sm:text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="font-medium text-slate-500">Subtotal</span>
                        <span class="font-medium text-slate-900">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span class="font-medium text-slate-500">Diskon</span>
                        <span class="font-medium text-slate-900">Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-slate-100 pt-3 flex justify-between gap-4 text-sm sm:text-base font-bold">
                        <span class="text-slate-900">Total</span>
                        <span class="text-[#AE7C18]">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Timeline Status --}}
        <div>
            <div class="h-full rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <h2 class="mb-4 sm:mb-5 font-bold text-slate-900 text-sm sm:text-base">Timeline Status</h2>

                <div class="space-y-4 sm:space-y-5">
                    @forelse($transaction->orderStatusHistories->sortBy('created_at') as $history)
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
                                @if($history->note)
                                    <p class="mt-0.5 text-xs sm:text-sm leading-relaxed text-slate-500">{{ $history->note }}</p>
                                @endif
                                <p class="mt-0.5 text-[10px] sm:text-xs text-slate-400">
                                    {{ $history->created_at->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y H:i') }} WITA
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="flex gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#AE7C18]/10 text-[#AE7C18]">
                                <x-heroicon-o-clock class="h-4 w-4"/>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-slate-900 text-xs sm:text-sm">{{ $currentStatus['label'] }}</p>
                                <p class="mt-0.5 text-[10px] sm:text-xs text-slate-400">
                                    {{ $transaction->transaction_date?->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y H:i') ?? '-' }} WITA
                                </p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
