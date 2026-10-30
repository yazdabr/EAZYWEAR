@extends('admin.layouts.app')

@section('title', 'Detail Transaksi NNS')
@section('page-title', 'Detail Transaksi NNS')

@section('content')

@php
    // DATA CONTOH UNTUK PREVIEW UI
    $transaction = [
        'invoice' => 'NNS-20261030-001',
        'date' => '30 Oktober 2026, 10:25 WITA',
        'status' => 'PAID',
        'customer' => 'Ahmad Rizki',
        'phone' => '081234567890',
        'payment_method' => 'QRIS',
        'paid_at' => '30 Oktober 2026, 10:30 WITA',
        'subtotal' => 175000,
        'discount' => 0,
        'shipping' => 0,
        'total' => 175000,
    ];

    $items = [
        [
            'product' => 'Jersey Supporter Home',
            'size' => 'L',
            'number' => '10',
            'name' => 'RIZKI',
            'qty' => 1,
            'unit_price' => 175000,
            'subtotal' => 175000,
        ],
    ];

    $statusClass = match ($transaction['status']) {
        'PAID', 'COMPLETED' => 'bg-emerald-100 text-emerald-700',
        'PENDING' => 'bg-amber-100 text-amber-700',
        'CANCELLED', 'EXPIRED' => 'bg-red-100 text-red-700',
        default => 'bg-slate-100 text-slate-700',
    };

    $totalQty = collect($items)->sum('qty');
@endphp

<div class="space-y-5">

    {{-- HEADER TRANSAKSI --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="break-all text-xl font-bold text-slate-900">
                    {{ $transaction['invoice'] }}
                </h1>

                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $statusClass }}">
                    {{ $transaction['status'] === 'PAID' ? 'Lunas' : $transaction['status'] }}
                </span>
            </div>

            <p class="mt-2 text-sm text-slate-500">
                {{ $transaction['date'] }}
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.nns-transactions.index') }}"
               class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                <x-heroicon-o-arrow-left class="mr-2 h-4 w-4"/>
                Kembali
            </a>

            <button type="button" onclick="window.print()"
                    class="inline-flex items-center justify-center rounded-xl bg-[#AE7C18] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#96690F]">
                <x-heroicon-o-printer class="mr-2 h-4 w-4"/>
                Cetak
            </button>
        </div>
    </div>

    {{-- INFORMASI PELANGGAN DAN PEMBAYARAN --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

        {{-- PELANGGAN --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                    <x-heroicon-o-user class="h-5 w-5"/>
                </div>
                <div>
                    <h2 class="font-bold text-slate-900">Informasi Pelanggan</h2>
                    <p class="text-xs text-slate-500">Data pemesan NNS</p>
                </div>
            </div>

            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                Nama Pelanggan
            </p>
            <p class="mt-1 font-semibold text-slate-900">
                {{ $transaction['customer'] }}
            </p>

            <p class="mt-4 text-xs font-medium uppercase tracking-wide text-slate-400">
                Nomor Telepon
            </p>
            <p class="mt-1 font-medium text-slate-800">
                {{ $transaction['phone'] }}
            </p>
        </div>

        {{-- PEMBAYARAN --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <x-heroicon-o-banknotes class="h-5 w-5"/>
                </div>
                <div>
                    <h2 class="font-bold text-slate-900">Informasi Pembayaran</h2>
                    <p class="text-xs text-slate-500">Ringkasan pembayaran NNS</p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 py-2">
                <span class="text-sm text-slate-500">Metode Pembayaran</span>
                <span class="font-semibold text-slate-900">
                    {{ strtoupper($transaction['payment_method']) }}
                </span>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-100 py-2">
                <span class="text-sm text-slate-500">Status</span>
                <span class="font-semibold {{ $transaction['status'] === 'PAID' ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $transaction['status'] === 'PAID' ? 'Lunas' : $transaction['status'] }}
                </span>
            </div>

            @if ($transaction['paid_at'])
                <div class="flex items-center justify-between gap-3 border-t border-slate-100 py-2">
                    <span class="text-sm text-slate-500">Waktu Pembayaran</span>
                    <span class="text-right text-sm font-medium text-slate-800">
                        {{ $transaction['paid_at'] }}
                    </span>
                </div>
            @endif
        </div>
    </div>

    {{-- RINCIAN PRODUK DAN RINGKASAN PEMBAYARAN --}}
    <div class="grid grid-cols-1 items-stretch gap-5 lg:grid-cols-2">

        {{-- RINCIAN PRODUK --}}
        <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 p-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <x-heroicon-o-tag class="h-5 w-5"/>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-900">Rincian Produk NNS</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $totalQty }} pcs · {{ count($items) }} jenis produk
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex flex-1 flex-col gap-4 p-5">

                @foreach ($items as $item)
                    <div class="rounded-xl border border-slate-100 p-4">

                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h3 class="font-semibold text-slate-900">
                                    {{ $item['product'] }}
                                </h3>
                                <p class="mt-1 text-sm text-slate-500">
                                    Ukuran {{ $item['size'] }} · {{ $item['qty'] }} pcs
                                </p>
                            </div>

                            <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                {{ $item['qty'] }} pcs
                            </span>
                        </div>

                        {{-- NUMBER DAN NAME SET --}}
                        <div class="mt-4 grid grid-cols-2 gap-3">

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                                    Number
                                </p>
                                <p class="mt-2 text-3xl font-black text-slate-900">
                                    {{ $item['number'] ?: '-' }}
                                </p>
                            </div>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-slate-400">
                                    Name Set
                                </p>
                                <p class="mt-2 break-words text-xl font-black uppercase text-slate-900">
                                    {{ $item['name'] ?: '-' }}
                                </p>
                            </div>

                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3">
                            <div>
                                <p class="text-xs text-slate-500">
                                    {{ $item['qty'] }} x Rp{{ number_format($item['unit_price'], 0, ',', '.') }}
                                </p>
                            </div>
                            <p class="shrink-0 text-sm font-bold text-slate-900">
                                Rp{{ number_format($item['subtotal'], 0, ',', '.') }}
                            </p>
                        </div>

                    </div>
                @endforeach

            </div>
        </div>

        {{-- RINGKASAN PEMBAYARAN --}}
        <div class="h-full rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                    <x-heroicon-o-calculator class="h-5 w-5"/>
                </div>
                <div>
                    <h2 class="font-bold text-slate-900">Ringkasan Pembayaran</h2>
                    <p class="mt-1 text-sm text-slate-500">Rincian biaya transaksi</p>
                </div>
            </div>

            <div class="mt-5 flex justify-between gap-4 py-3 text-sm">
                <span class="text-slate-500">Subtotal</span>
                <span class="text-right font-medium text-slate-900">
                    Rp{{ number_format($transaction['subtotal'], 0, ',', '.') }}
                </span>
            </div>

            <div class="flex justify-between gap-4 border-t border-slate-100 py-3 text-sm">
                <span class="text-slate-500">Diskon</span>
                <span class="text-right font-medium text-slate-900">
                    -Rp{{ number_format($transaction['discount'], 0, ',', '.') }}
                </span>
            </div>

            @if ($transaction['shipping'] > 0)
                <div class="flex justify-between gap-4 border-t border-slate-100 py-3 text-sm">
                    <span class="text-slate-500">Ongkos Kirim</span>
                    <span class="text-right font-medium text-slate-900">
                        Rp{{ number_format($transaction['shipping'], 0, ',', '.') }}
                    </span>
                </div>
            @endif

            <div class="mt-2 border-t border-slate-200 pt-5">
                <p class="text-sm font-semibold text-slate-500">
                    Total Pembayaran
                </p>
                <p class="mt-2 break-words text-2xl font-extrabold text-emerald-700">
                    Rp{{ number_format($transaction['total'], 0, ',', '.') }}
                </p>

                <div class="mt-4 inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-bold {{ $statusClass }}">
                    <x-heroicon-o-check-circle class="h-4 w-4"/>
                    {{ $transaction['status'] === 'PAID' ? 'Pembayaran Lunas' : $transaction['status'] }}
                </div>
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
