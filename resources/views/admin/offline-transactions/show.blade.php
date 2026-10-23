@extends('admin.layouts.app')

@section('title', 'Detail Transaksi Offline')
@section('page-title', 'Detail Transaksi Offline')

@section('content')
<div class="space-y-5">

    {{-- Header transaksi --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="break-all text-xl font-bold text-slate-900">
                    {{ $transaction->invoice_number }}
                </h1>

                <span class="rounded-full px-3 py-1 text-xs font-bold
                    {{ $transaction->status === 'PAID'
                        ? 'bg-emerald-100 text-emerald-700'
                        : ($transaction->status === 'PENDING'
                            ? 'bg-amber-100 text-amber-700'
                            : 'bg-slate-100 text-slate-700') }}">
                    {{ $transaction->status === 'PAID'
                        ? 'Lunas'
                        : ($transaction->status === 'PENDING'
                            ? 'Menunggu Pembayaran'
                            : $transaction->status) }}
                </span>
            </div>

            <p class="mt-2 text-sm text-slate-500">
                {{ $transaction->transaction_date?->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d F Y, H:i') ?? '-' }}
                WITA
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.offline-transactions.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <x-heroicon-o-arrow-left class="mr-2 h-4 w-4" />
                Kembali
            </a>

            <button type="button" onclick="window.print()"
                    class="inline-flex items-center justify-center rounded-xl bg-[#AE7C18] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#96690F]">
                <x-heroicon-o-printer class="mr-2 h-4 w-4" />
                Cetak
            </button>
        </div>
    </div>

    {{-- Informasi transaksi --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <x-heroicon-o-user class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="font-bold text-slate-900">Informasi Pelanggan</h2>
                    <p class="text-xs text-slate-500">Data pembeli di booth</p>
                </div>
            </div>

            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Nama Pelanggan</p>
            <p class="mt-1 font-semibold text-slate-900">
                {{ $transaction->customer?->name ?? $transaction->shipping_name ?? 'Pelanggan Booth' }}
            </p>

            @if($transaction->customer?->phone ?? $transaction->shipping_phone)
                <p class="mt-4 text-xs font-medium uppercase tracking-wide text-slate-400">Nomor Telepon</p>
                <p class="mt-1 font-medium text-slate-800">
                    {{ $transaction->customer?->phone ?? $transaction->shipping_phone }}
                </p>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <x-heroicon-o-banknotes class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="font-bold text-slate-900">Informasi Pembayaran</h2>
                    <p class="text-xs text-slate-500">Ringkasan transaksi offline</p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 py-2">
                <span class="text-sm text-slate-500">Metode Pembayaran</span>
                <span class="font-semibold text-slate-900">
                    {{ strtoupper($transaction->payment_method ?? 'CASH') }}
                </span>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-100 py-2">
                <span class="text-sm text-slate-500">Status</span>
                <span class="font-semibold {{ $transaction->status === 'PAID' ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $transaction->status === 'PAID' ? 'Lunas' : $transaction->status }}
                </span>
            </div>

            @if($transaction->paid_at)
                <div class="flex items-center justify-between gap-3 border-t border-slate-100 py-2">
                    <span class="text-sm text-slate-500">Waktu Pembayaran</span>
                    <span class="text-right text-sm font-medium text-slate-800">
                        {{ $transaction->paid_at->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y, H:i') }} WITA
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- Rincian produk dan ringkasan pembayaran: 2 kolom --}}
    <div class="grid grid-cols-1 items-start gap-5 lg:grid-cols-2">

        {{-- Kolom kiri: Rincian Produk --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <h2 class="font-bold text-slate-900">Rincian Produk</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $transaction->items->sum('qty') }} barang dalam transaksi ini
                </p>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($transaction->items as $item)
                    @php
                        $variant = $item->productVariant;
                        $product = $variant?->product;
                    @endphp

                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-slate-900">
                                    {{ $product?->name ?? 'Produk tidak tersedia' }}
                                </h3>

                                <div class="mt-2 space-y-1 text-xs text-slate-500">
                                    @if($variant?->size?->name)
                                        <p>Ukuran: {{ $variant->size->name }}</p>
                                    @endif
                                    @if($item->custom_name)
                                        <p>Nama jersey: {{ $item->custom_name }}</p>
                                    @endif
                                    @if($item->custom_number)
                                        <p>Nomor jersey: {{ $item->custom_number }}</p>
                                    @endif
                                </div>

                                <p class="mt-3 text-sm text-slate-500">
                                    {{ $item->qty }} × Rp{{ number_format((float) $item->price, 0, ',', '.') }}
                                </p>
                            </div>

                            <p class="shrink-0 text-sm font-bold text-slate-900">
                                Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-sm text-slate-500">
                        Tidak ada rincian produk untuk transaksi ini.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Kolom kanan: Ringkasan Pembayaran --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="font-bold text-slate-900">Ringkasan Pembayaran</h2>
            <p class="mt-1 text-sm text-slate-500">Rincian biaya transaksi</p>

            <div class="mt-5 flex justify-between gap-4 py-3 text-sm">
                <span class="text-slate-500">Subtotal</span>
                <span class="text-right font-medium text-slate-900">
                    Rp{{ number_format((float) $transaction->subtotal, 0, ',', '.') }}
                </span>
            </div>

            <div class="flex justify-between gap-4 border-t border-slate-100 py-3 text-sm">
                <span class="text-slate-500">Diskon</span>
                <span class="text-right font-medium text-slate-900">
                    -Rp{{ number_format((float) $transaction->discount, 0, ',', '.') }}
                </span>
            </div>

            @if((float) $transaction->shipping > 0)
                <div class="flex justify-between gap-4 border-t border-slate-100 py-3 text-sm">
                    <span class="text-slate-500">Ongkos Kirim</span>
                    <span class="text-right font-medium text-slate-900">
                        Rp{{ number_format((float) $transaction->shipping, 0, ',', '.') }}
                    </span>
                </div>
            @endif

            <div class="mt-2 border-t border-slate-200 pt-5">
                <p class="text-sm font-semibold text-slate-500">Total Pembayaran</p>
                <p class="mt-2 break-words text-2xl font-extrabold text-emerald-700">
                    Rp{{ number_format((float) $transaction->total, 0, ',', '.') }}
                </p>

                <div class="mt-4 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold
                    {{ $transaction->status === 'PAID'
                        ? 'bg-emerald-50 text-emerald-700'
                        : 'bg-amber-50 text-amber-700' }}">
                    <x-heroicon-o-check-circle class="h-4 w-4" />
                    {{ $transaction->status === 'PAID' ? 'Pembayaran Lunas' : $transaction->status }}
                </div>
            </div>
        </div>

    </div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }

        main, main * {
            visibility: visible;
        }

        main {
            position: absolute;
            inset: 0;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        a, button {
            display: none !important;
        }

        .shadow-sm {
            box-shadow: none !important;
        }
    }
</style>
@endsection
