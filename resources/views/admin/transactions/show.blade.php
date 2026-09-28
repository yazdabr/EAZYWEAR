@extends('admin.layouts.app')

@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi')

@section('content')

@php
    $statusMap = [
        'ORDER_CREATED' => [
            'label' => 'Pesanan Dibuat',
            'color' => 'bg-blue-100 text-blue-700',
            'icon' => 'shopping-cart',
        ],
        'PAYMENT_CONFIRMED' => [
            'label' => 'Pembayaran Dikonfirmasi',
            'color' => 'bg-emerald-100 text-emerald-700',
            'icon' => 'credit-card',
        ],
        'ORDER_PROCESSING' => [
            'label' => 'Pesanan Diproses',
            'color' => 'bg-amber-100 text-amber-700',
            'icon' => 'cog',
        ],
        'ORDER_SHIPPED' => [
            'label' => 'Pesanan Dikirim',
            'color' => 'bg-indigo-100 text-indigo-700',
            'icon' => 'truck',
        ],
        'ORDER_COMPLETED' => [
            'label' => 'Pesanan Selesai',
            'color' => 'bg-green-100 text-green-700',
            'icon' => 'check-circle',
        ],
        'ORDER_CANCELLED' => [
            'label' => 'Pesanan Dibatalkan',
            'color' => 'bg-red-100 text-red-700',
            'icon' => 'x-circle',
        ],
    ];

    $latestHistory = $transaction->orderStatusHistories
        ->sortByDesc('created_at')
        ->first();

    $currentStatus = $statusMap[$latestHistory?->status ?? ''] ?? [
        'label' => $transaction->status,
        'color' => 'bg-slate-100 text-slate-700',
        'icon' => 'clock',
    ];
@endphp

<div class="space-y-4 sm:space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">

        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">

                <h1 class="text-lg sm:text-xl font-bold text-slate-900 break-all">
                    {{ $transaction->invoice_number }}
                </h1>

                <span class="rounded-full px-2.5 py-0.5 sm:px-3 sm:py-1 text-xs font-bold {{ $currentStatus['color'] }}">
                    {{ $currentStatus['label'] }}
                </span>

            </div>

            <p class="mt-1 sm:mt-2 text-xs sm:text-sm text-slate-500">
                {{ $transaction->transaction_date?->setTimezone('Asia/Makassar')->format('d M Y H:i') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3">

            {{-- PROSES PESANAN --}}
            @if($transaction->status === 'PAID')
                <button
                    type="button"
                    onclick="processOrder('{{ $transaction->id }}')"
                    id="process-order-btn"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/30"
                >
                    <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                    Proses Pesanan
                </button>
            @endif

            {{-- CEK PEMBAYARAN --}}
            @if(
                $transaction->payment_method === 'VA'
                && $transaction->status === 'PENDING'
            )
                <button
                    type="button"
                    onclick="checkDokuPayment('{{ $transaction->id }}')"
                    id="check-doku-btn"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500/30"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                        />
                    </svg>

                    Cek Pembayaran
                </button>
            @endif

            {{-- KEMBALI --}}
            <a
                href="{{ route('admin.transactions') }}"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                Kembali
            </a>

            {{-- CETAK --}}
            <a
                href="{{ route('admin.transactions.print', $transaction->invoice_number) }}"
                target="_blank"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-xl bg-[#AE7C18] px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F]"
            >
                Cetak Invoice
            </a>

        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- INFORMASI PELANGGAN --}}
    {{-- ========================================================= --}}

    <div class="rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">

        <div class="mb-4 sm:mb-5 flex items-center gap-2.5 sm:gap-3">

            <div class="flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]">
                <x-heroicon-o-user class="h-4 w-4 sm:h-5 sm:w-5" />
            </div>

            <h2 class="font-bold text-slate-900 text-sm sm:text-base">
                Informasi Pelanggan
            </h2>

        </div>

        <div class="grid gap-4 sm:gap-5 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 text-xs sm:text-sm">

            <div>
                <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">
                    Nama
                </p>

                <p class="mt-1 font-semibold text-slate-900">
                    {{ $transaction->shipping_name ?? $transaction->customer?->name }}
                </p>
            </div>

            <div>
                <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">
                    Telepon
                </p>

                <p class="mt-1 font-semibold text-slate-900">
                    {{ $transaction->shipping_phone ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">
                    Email
                </p>

                <p class="mt-1 font-semibold text-slate-900 break-all">
                    {{ $transaction->shipping_email ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">
                    Metode Pengiriman
                </p>

                <p class="mt-1 font-semibold text-slate-900">
                    {{ $transaction->shipping_method ?? '-' }}
                </p>
            </div>

            <div class="sm:col-span-2 lg:col-span-4">

                <p class="text-[10px] sm:text-xs uppercase text-slate-400 font-medium">
                    Alamat Pengiriman
                </p>

                <p class="mt-1 leading-snug sm:leading-6 font-semibold text-slate-900">
                    {{ $transaction->shipping_address }}
                </p>

                <p class="mt-1 text-xs text-slate-500">
                    {{ $transaction->shipping_district }},
                    {{ $transaction->shipping_city }},
                    {{ $transaction->shipping_province }}
                    {{ $transaction->shipping_postal_code }}
                </p>

            </div>

        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- PRODUK + PEMBAYARAN --}}
    {{-- ========================================================= --}}

    <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">

        {{-- PRODUK --}}
        <div class="lg:col-span-2">

            <div class="h-full rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">

                <div class="mb-4 sm:mb-5 flex items-center gap-2.5 sm:gap-3">

                    <div class="flex h-8 w-8 sm:h-10 sm:w-10 shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]">
                        <x-heroicon-o-shopping-bag class="h-4 w-4 sm:h-5 sm:w-5" />
                    </div>

                    <h2 class="font-bold text-slate-900 text-sm sm:text-base">
                        Produk
                    </h2>

                </div>

                <div class="space-y-3 sm:space-y-4">

                    @foreach($transaction->items as $item)

                        @php
                            $product = $item->productVariant?->product;
                        @endphp

                        <div class="flex items-start sm:items-center gap-3 sm:gap-4 rounded-xl sm:rounded-2xl bg-slate-50 p-3 sm:p-4">

                            <div class="h-12 w-12 sm:h-16 sm:w-16 shrink-0 overflow-hidden rounded-lg sm:rounded-xl bg-slate-200">

                                @if($product?->images?->first())

                                    <img
                                        src="{{ asset('storage/'.$product->images->first()->image) }}"
                                        class="h-full w-full object-cover"
                                    >

                                @else

                                    <div class="flex h-full items-center justify-center text-[10px] sm:text-xs text-slate-400">
                                        No Image
                                    </div>

                                @endif

                            </div>

                            <div class="min-w-0 flex-1">

                                <p class="font-bold text-slate-900 text-xs sm:text-base">
                                    {{ $product?->name ?? '-' }}
                                </p>

                                <p class="mt-0.5 text-xs text-slate-500">
                                    Ukuran:
                                    {{ $item->productVariant?->size?->name ?? '-' }}
                                </p>

                                @if($item->custom_name)
                                    <p class="text-xs text-[#AE7C18]">
                                        Nama: {{ $item->custom_name }}
                                    </p>
                                @endif

                                @if($item->custom_number)
                                    <p class="text-xs text-slate-600">
                                        Nomor: {{ $item->custom_number }}
                                    </p>
                                @endif

                            </div>

                            <div class="text-right shrink-0">

                                <p class="font-bold text-slate-900 text-xs sm:text-sm">
                                    Rp {{ number_format($item->subtotal,0,',','.') }}
                                </p>

                                <p class="text-[10px] sm:text-xs text-slate-400">
                                    {{ $item->qty }} pcs
                                </p>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        </div>

        {{-- PEMBAYARAN + RINGKASAN --}}
        <div class="space-y-4 sm:space-y-6">

            {{-- PAYMENT --}}
            <div class="rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">

                <h2 class="mb-3 sm:mb-4 font-bold text-slate-900 text-sm sm:text-base">
                    Pembayaran
                </h2>

                <div class="space-y-2.5 text-xs sm:text-sm">

                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">
                            Metode
                        </span>

                        <span class="font-bold text-slate-900 text-right">
                            {{ $transaction->payment_method }}
                        </span>
                    </div>

                    @if($transaction->va_number)

                        <div class="flex justify-between gap-4">

                            <span class="text-slate-500">
                                VA Number
                            </span>

                            <span class="font-bold text-slate-900 text-right break-all">
                                {{ $transaction->va_number }}
                            </span>

                        </div>

                        <div class="flex justify-between gap-4">

                            <span class="text-slate-500">
                                Expired
                            </span>

                            <span class="text-slate-700 text-right">
                                {{ $transaction->va_expired_at
                                    ? $transaction->va_expired_at
                                        ->setTimezone('Asia/Makassar')
                                        ->format('d M Y H:i')
                                    : '-'
                                }}
                            </span>

                        </div>

                    @endif

                </div>

            </div>

            {{-- SUMMARY --}}
            <div class="rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">

                <h2 class="mb-3 sm:mb-4 font-bold text-slate-900 text-sm sm:text-base">
                    Ringkasan
                </h2>

                <div class="space-y-2.5 text-xs sm:text-sm">

                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">
                            Subtotal
                        </span>

                        <span class="font-medium text-slate-900">
                            Rp {{ number_format($transaction->subtotal,0,',','.') }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">
                            Diskon
                        </span>

                        <span class="font-medium text-slate-900">
                            Rp {{ number_format($transaction->discount,0,',','.') }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-slate-500">
                            Pengiriman
                        </span>

                        <span class="font-medium text-slate-900">
                            Rp {{ number_format($transaction->shipping,0,',','.') }}
                        </span>
                    </div>

                    <div class="border-t border-slate-100 pt-3 flex justify-between gap-4 text-sm sm:text-base font-bold">

                        <span class="text-slate-900">
                            Total
                        </span>

                        <span class="text-[#AE7C18]">
                            Rp {{ number_format($transaction->total,0,',','.') }}
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ========================================================= --}}
    {{-- SHIPPING + TIMELINE --}}
    {{-- ========================================================= --}}

    @if(in_array($transaction->status, ['ORDER_PROCESSING', 'ORDER_SHIPPED', 'ORDER_COMPLETED'], true))

        <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">

            {{-- SHIPPING --}}
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
                                    Informasi kurir dan nomor resi
                                </p>
                            </div>

                        </div>

                    </div>

                    {{-- ORDER PROCESSING --}}
                    @if($transaction->status === 'ORDER_PROCESSING')

                        <form
                            id="ship-order-form"
                            onsubmit="shipOrder(event, '{{ $transaction->id }}')"
                            class="space-y-4"
                        >

                            <div class="grid gap-4 sm:grid-cols-2">

                                <div>

                                    <label class="mb-1.5 block text-xs font-medium text-slate-600">
                                        Kurir
                                    </label>

                                    <input
                                        type="text"
                                        name="courier"
                                        required
                                        maxlength="100"
                                        placeholder="Contoh: JNE, J&T, SiCepat"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-[#AE7C18] focus:ring-2 focus:ring-[#AE7C18]/10"
                                    >

                                </div>

                                <div>

                                    <label class="mb-1.5 block text-xs font-medium text-slate-600">
                                        Nomor Resi
                                    </label>

                                    <input
                                        type="text"
                                        name="tracking_number"
                                        required
                                        maxlength="100"
                                        placeholder="Masukkan nomor resi"
                                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-[#AE7C18] focus:ring-2 focus:ring-[#AE7C18]/10"
                                    >

                                </div>

                            </div>

                            <div class="flex justify-end">

                                <button
                                    type="submit"
                                    id="ship-order-btn"
                                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-3 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F]"
                                >
                                    <x-heroicon-o-check class="h-4 w-4" />
                                    Simpan
                                </button>

                            </div>

                        </form>

                    {{-- ORDER SHIPPED / COMPLETED --}}
                    @else

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

                            <div class="mt-4 flex justify-end">

                                <button
                                    type="button"
                                    id="edit-shipping-btn"
                                    onclick="toggleShippingEdit()"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-[#AE7C18]/30 bg-[#AE7C18]/10 px-4 py-2 text-xs sm:text-sm font-semibold text-[#96690F] transition hover:bg-[#AE7C18]/15"
                                >
                                    <x-heroicon-o-pencil-square class="h-4 w-4" />

                                    Edit Pengiriman
                                </button>

                            </div>

                        </div>

                        {{-- EDIT SHIPPING --}}
                        <form
                            id="edit-shipping-form"
                            onsubmit="updateShipping(event, '{{ $transaction->id }}')"
                            class="hidden mt-4"
                        >

                            <div class="rounded-2xl border border-[#AE7C18]/20 bg-[#AE7C18]/5 p-4 sm:p-5">

                                <div class="mb-4">

                                    <p class="text-sm font-bold text-slate-900">
                                        Edit Detail Pengiriman
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Perbarui kurir atau nomor resi pesanan.
                                    </p>

                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">

                                    <div>

                                        <label class="mb-1.5 block text-xs font-medium text-slate-600">
                                            Kurir
                                        </label>

                                        <input
                                            type="text"
                                            name="courier"
                                            value="{{ $transaction->courier }}"
                                            maxlength="100"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-[#AE7C18] focus:ring-2 focus:ring-[#AE7C18]/10"
                                        >

                                    </div>

                                    <div>

                                        <label class="mb-1.5 block text-xs font-medium text-slate-600">
                                            Nomor Resi
                                        </label>

                                        <input
                                            type="text"
                                            name="tracking_number"
                                            value="{{ $transaction->tracking_number }}"
                                            maxlength="100"
                                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition focus:border-[#AE7C18] focus:ring-2 focus:ring-[#AE7C18]/10"
                                        >

                                    </div>

                                </div>

                                <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">

                                    <button
                                        type="button"
                                        onclick="toggleShippingEdit()"
                                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                    >
                                        Batal
                                    </button>

                                    <button
                                        type="submit"
                                        id="update-shipping-btn"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/30"
                                    >
                                        <x-heroicon-o-check class="h-4 w-4" />
                                        Simpan Perubahan
                                    </button>

                                </div>

                            </div>

                        </form>

                    @endif

                </div>

            </div>

            {{-- TIMELINE --}}
            <div>

                <div class="h-full rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">

                    <h2 class="mb-4 sm:mb-5 font-bold text-slate-900 text-sm sm:text-base">
                        Timeline Status
                    </h2>

                    <div class="space-y-4 sm:space-y-5">

                        @foreach($transaction->orderStatusHistories->sortBy('created_at') as $history)

                            @php
                                $status = $statusMap[$history->status] ?? null;
                            @endphp

                            <div class="flex gap-3">

                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#AE7C18]/10 text-[#AE7C18]">

                                    @switch($status['icon'] ?? null)

                                        @case('shopping-cart')
                                            <x-heroicon-o-shopping-cart class="h-4 w-4"/>
                                            @break

                                        @case('credit-card')
                                            <x-heroicon-o-credit-card class="h-4 w-4"/>
                                            @break

                                        @case('cog')
                                            <x-heroicon-o-cog-6-tooth class="h-4 w-4"/>
                                            @break

                                        @case('truck')
                                            <x-heroicon-o-truck class="h-4 w-4"/>
                                            @break

                                        @case('check-circle')
                                            <x-heroicon-o-check-circle class="h-4 w-4"/>
                                            @break

                                        @case('x-circle')
                                            <x-heroicon-o-x-circle class="h-4 w-4"/>
                                            @break

                                        @default
                                            <x-heroicon-o-clock class="h-4 w-4"/>

                                    @endswitch

                                </div>

                                <div class="min-w-0 flex-1">

                                    <p class="font-bold text-slate-900 text-xs sm:text-sm">
                                        {{ $status['label'] ?? $history->status }}
                                    </p>

                                    @if($history->note)

                                        <p class="mt-0.5 text-xs leading-relaxed text-slate-500">
                                            {{ $history->note }}
                                        </p>

                                    @endif

                                    <p class="mt-0.5 text-[10px] sm:text-xs text-slate-400">
                                        {{ $history->created_at
                                            ->setTimezone('Asia/Makassar')
                                            ->format('d M Y H:i') 
                                        }}
                                    </p>

                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            </div>

        </div>

    @endif

</div>

<script>

function checkDokuPayment(id)
{
    const btn = document.getElementById('check-doku-btn');

    if (!btn) {
        return;
    }

    btn.disabled = true;
    btn.innerText = 'Mengecek...';

    fetch(
        `/admin/transactions/${id}/check-payment`,
        {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }
    )
    .then(res => res.json())
    .then(data => {

        alert(data.message);

        if (data.success) {
            location.reload();
        }

    })
    .catch(() => {
        alert('Terjadi kesalahan.');
    })
    .finally(() => {

        btn.disabled = false;
        btn.innerText = 'Cek Pembayaran';

    });
}

function processOrder(id)
{
    const btn = document.getElementById('process-order-btn');

    if (!btn) {
        return;
    }

    btn.disabled = true;
    btn.innerText = 'Memproses...';

    fetch(
        `/admin/transactions/${id}/process`,
        {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }
    )
    .then(res => res.json())
    .then(data => {

        alert(data.message);

        if (data.success) {
            location.reload();
        }

    })
    .catch(() => {
        alert('Terjadi kesalahan.');
    })
    .finally(() => {

        btn.disabled = false;
        btn.innerText = 'Proses Pesanan';

    });
}

function shipOrder(event, id)
{
    event.preventDefault();

    const form = document.getElementById('ship-order-form');
    const btn = document.getElementById('ship-order-btn');

    if (!form || !btn) {
        return;
    }

    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerText = 'Memproses...';

    fetch(
        `/admin/transactions/${id}/ship`,
        {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PATCH'
            },
            body: formData
        }
    )
    .then(res => res.json())
    .then(data => {

        alert(data.message);

        if (data.success) {
            location.reload();
        }

    })
    .catch(() => {
        alert('Terjadi kesalahan.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerText = 'Simpan';
    });
}

function toggleShippingEdit()
{
    const display = document.getElementById('shipping-display');
    const form = document.getElementById('edit-shipping-form');

    if (!display || !form) {
        return;
    }

    display.classList.toggle('hidden');
    form.classList.toggle('hidden');
}

function updateShipping(event, id)
{
    event.preventDefault();

    const form = document.getElementById('edit-shipping-form');
    const btn = document.getElementById('update-shipping-btn');

    if (!form || !btn) {
        return;
    }

    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    fetch(
        `/admin/transactions/${id}/shipping`,
        {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'X-HTTP-Method-Override': 'PATCH'
            },
            body: formData
        }
    )
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
        if (error?.errors) {
            const firstError = Object.values(error.errors)[0]?.[0];

            alert(firstError ?? error.message ?? 'Gagal memperbarui detail pengiriman.');
            return;
        }

        alert(
            error?.message ??
            'Terjadi kesalahan saat memperbarui detail pengiriman.'
        );
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerText = 'Simpan Perubahan';
    });
}
</script>

@endsection
