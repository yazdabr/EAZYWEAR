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
            @php
                $paymentMethod = strtoupper((string) $transaction->payment_method);
                $isDokuVa = str_contains($paymentMethod, 'VA');
                $isDokuQris = str_contains($paymentMethod, 'QRIS');
            @endphp

            {{-- Tombol untuk memproses pesanan yang sudah dibayar --}}
            @if($transaction->status === 'PAID')
                <button type="button"
                    onclick="processOrder('{{ $transaction->id }}')"
                    id="process-order-btn"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/30">
                    <x-heroicon-o-cog-6-tooth class="h-4 w-4" />
                    Proses Pesanan
                </button>
            @endif

            {{-- Tombol untuk memeriksa pembayaran DOKU --}}
            @if(
                ($isDokuVa || $isDokuQris)
                && in_array($transaction->status, ['PENDING', 'EXPIRED'], true)
            )
                <button type="button"
                    onclick="checkDokuPayment('{{ $transaction->id }}')"
                    id="check-doku-btn"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500/30">
                    <x-heroicon-o-arrow-path class="h-4 w-4" />
                    Cek Pembayaran
                </button>
            @endif

            <a href="{{ route('admin.transactions') }}"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-xl border border-slate-200 bg-white px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Kembali
            </a>

            <a href="{{ route('admin.transactions.print', $transaction->invoice_number) }}"
                target="_blank"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-xl bg-[#AE7C18] px-3 py-2 sm:px-4 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F]">
                Cetak Invoice
            </a>
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
        <div class="grid grid-cols-1 {{ $transaction->fulfillments->isEmpty() ? 'md:grid-cols-2' : 'md:grid-cols-1' }} gap-6 text-xs sm:text-sm">
            
            {{-- Seksi 1: Kontak Pelanggan --}}
            <div class="space-y-4 rounded-xl bg-slate-50/70 p-4 border border-slate-100">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-[12px] font-bold uppercase tracking-wider text-[#AE7C18]">Kontak Pelanggan</span>

                    @if(
                        in_array($transaction->status, ['PAID', 'ORDER_PROCESSING'], true) &&
                        !$transaction->biteship_order_id &&
                        !$transaction->biteship_tracking_id &&
                        !$transaction->biteship_waybill_id &&
                        !$transaction->tracking_number
                    )
                        <button
                            type="button"
                            onclick="toggleCustomerContactEditor(true)"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-[#AE7C18]/40 bg-[#AE7C18]/10 px-3 py-1.5 text-xs font-semibold text-[#8A6110] shadow-sm transition hover:bg-[#AE7C18]/20 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/40"
                        >
                            <x-heroicon-o-pencil-square class="h-3.5 w-3.5" />
                            Edit Kontak
                        </button>
                    @endif
                </div>

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

                @if(
                    in_array($transaction->status, ['PAID', 'ORDER_PROCESSING'], true) &&
                    !$transaction->biteship_order_id &&
                    !$transaction->biteship_tracking_id &&
                    !$transaction->biteship_waybill_id &&
                    !$transaction->tracking_number
                )
                    <form
                        id="customer-contact-form"
                        action="{{ route('admin.transactions.customer-contact.update', $transaction) }}"
                        class="hidden space-y-3 border-t border-slate-200 pt-3"
                    >
                        @csrf
                        <div>
                            <label for="shipping_name" class="mb-1 block text-xs font-semibold text-slate-600">Nama pelanggan</label>
                            <input id="shipping_name" name="shipping_name" type="text" maxlength="255" required
                                value="{{ $transaction->shipping_name ?? $transaction->customer?->name }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20">
                        </div>
                        <div>
                            <label for="shipping_phone" class="mb-1 block text-xs font-semibold text-slate-600">Nomor telepon</label>
                            <input id="shipping_phone" name="shipping_phone" type="tel" maxlength="30" required
                                value="{{ $transaction->shipping_phone }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20">
                        </div>
                        <div>
                            <label for="shipping_email" class="mb-1 block text-xs font-semibold text-slate-600">Email (opsional)</label>
                            <input id="shipping_email" name="shipping_email" type="email" maxlength="255"
                                value="{{ $transaction->shipping_email }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20">
                        </div>
                        <p id="customer-contact-error" class="hidden text-xs text-red-600"></p>
                        <div class="flex flex-wrap gap-2">
                            <button id="customer-contact-save" type="submit"
                                class="rounded-lg bg-[#AE7C18] px-3 py-2 text-xs font-semibold text-white hover:bg-[#96690F]">
                                Simpan Kontak
                            </button>
                            <button type="button" onclick="toggleCustomerContactEditor(false)"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                Batal
                            </button>
                        </div>
                    </form>
                @endif
            </div>

            @if($transaction->fulfillments->isEmpty())
            <!-- Seksi 2: Metode & Alamat Pengiriman -->
            <div class="space-y-4 rounded-xl bg-slate-50/70 p-4 border border-slate-100 flex flex-col justify-between">
                <div class="space-y-3.5">
                    <span class="text-[12px] font-bold uppercase tracking-wider text-[#AE7C18]">Detail Logistik</span>
                    <p class=" text-xs leading-relaxed text-amber-800">
                        <strong>Informasi:</strong> Edit alamat hanya untuk Kurir sebelum resi dibuat.
                    </p>
                    
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
                                    {{ \Carbon\Carbon::parse($transaction->pickup_date)->locale('id')->translatedFormat('l, d F Y') }}<span class="font-normal text-[#AE7C18]">, {{ \Carbon\Carbon::parse($transaction->pickup_time_start)->format('H:i') }}-{{ \Carbon\Carbon::parse($transaction->pickup_time_end)->format('H:i') }} WITA</span>
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Alamat Pengiriman -->
                    <div class="pt-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="text-xs sm:text-sm uppercase text-slate-500 font-semibold">Alamat Pengiriman</p>
                            @if(
                                in_array($transaction->status, ['PAID', 'ORDER_PROCESSING'], true) &&
                                $transaction->shipping_method === 'Kurir' &&
                                !$transaction->biteship_order_id &&
                                !$transaction->biteship_tracking_id &&
                                !$transaction->biteship_waybill_id &&
                                !$transaction->tracking_number
                            )
                                <button
                                    type="button"
                                    onclick="toggleShippingAddressEditor(true)"
                                    class="inline-flex items-center gap-1 rounded-lg border border-[#AE7C18]/30 bg-white px-3 py-2 text-sm font-semibold text-[#AE7C18] transition hover:bg-[#AE7C18]/5"
                                >
                                    <x-heroicon-o-pencil-square class="h-3.5 w-3.5" />
                                    Edit Alamat
                                </button>
                            @endif
                        </div>

                        <p id="shipping-address-display" class="mt-1 text-sm leading-relaxed font-semibold text-slate-900 sm:text-base">{{ $transaction->shipping_address }}</p>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $transaction->shipping_district }}, {{ $transaction->shipping_city }}, {{ $transaction->shipping_province }} {{ $transaction->shipping_postal_code }}</p>

                        @if(
                            in_array($transaction->status, ['PAID', 'ORDER_PROCESSING'], true) &&
                            $transaction->shipping_method === 'Kurir' &&
                            !$transaction->biteship_order_id &&
                            !$transaction->biteship_tracking_id &&
                            !$transaction->biteship_waybill_id &&
                            !$transaction->tracking_number
                        )
                            <form
                                id="shipping-address-form"
                                action="{{ route('admin.transactions.shipping-address.update', $transaction) }}"
                                class="mt-3 hidden space-y-2"
                            >
                                @csrf
                                <label for="shipping_address" class="block text-xs font-semibold text-slate-600">Alamat pengiriman baru</label>
                                <textarea
                                    id="shipping_address"
                                    name="shipping_address"
                                    rows="3"
                                    maxlength="2000"
                                    required
                                    class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                                >{{ $transaction->shipping_address }}</textarea>
                                <p id="shipping-address-error" class="hidden text-xs text-red-600"></p>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        type="submit"
                                        id="shipping-address-save"
                                        class="rounded-lg bg-[#AE7C18] px-3 py-2 text-xs font-semibold text-white transition hover:bg-[#96690F]"
                                    >Simpan Alamat</button>
                                    <button
                                        type="button"
                                        onclick="toggleShippingAddressEditor(false)"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50"
                                    >Batal</button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
    @if($transaction->fulfillments->isNotEmpty())
        <section class="space-y-4 rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm">
            <div class="flex items-start gap-3 border-b border-slate-100 pb-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]">
                    <x-heroicon-o-truck class="h-5 w-5" />
                </div>
                <div>
                    <h2 class="font-bold text-slate-900 text-sm sm:text-base">Pemenuhan Pesanan</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                        @if($transaction->fulfillments->count() > 1)
                            Pesanan memiliki beberapa status pemenuhan. Setiap pesanan dapat memiliki metode pengiriman, ongkir, jadwal, dan status pemenuhan yang berbeda.
                        @elseif($transaction->fulfillments->count() === 1)
                            Atur metode pengiriman, ongkir, jadwal, dan status pemenuhan untuk pesanan ini.
                        @else
                            Belum ada status pemenuhan untuk transaksi ini.
                        @endif
                    </p>
                </div>
            </div>

            @if($transaction->shipments->isNotEmpty())
                <div class="mb-5 mx-auto grid w-full grid-cols-1 gap-4 xl:max-w-2xl">
                    @foreach($transaction->shipments as $shipment)
                        @php
                            $shipmentCanEditAddress = in_array($transaction->status, ['PAID', 'ORDER_PROCESSING', 'ORDER_SHIPPED'], true)
                                && $shipment->shipping_method === 'Kurir'
                                && !$shipment->biteship_order_id
                                && !$shipment->biteship_tracking_id
                                && !$shipment->biteship_waybill_id
                                && !$shipment->tracking_number;

                            $shipmentCanProcess = in_array($transaction->status, ['ORDER_PROCESSING', 'ORDER_SHIPPED'], true)
                                && $shipment->shipping_method === 'Kurir'
                                && !in_array($shipment->status, ['SHIPPED', 'COMPLETED', 'CANCELLED'], true);

                            $shipmentLabel = $shipment->shipment_type === 'combined'
                                ? 'Pengiriman Gabungan'
                                : 'Pengiriman ' . ($shipment->fulfillments->pluck('group_type')->contains('ready') ? 'Ready Stock' : 'Pre-Order');
                        @endphp

                        <article class="min-w-0 rounded-2xl border p-4 sm:p-5 shadow-sm {{ $shipment->shipment_type === 'combined' ? 'border-slate-200 bg-slate-50/60' : 'border-amber-200 bg-amber-50/40' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-sm font-bold text-slate-900">{{ $shipmentLabel }}</h3>
                                    <p class="mt-1 text-xs text-slate-500">{{ $shipment->shipping_method }}</p>
                                </div>
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $shipment->shipment_type === 'combined' ? 'bg-white text-slate-600 ring-1 ring-slate-200' : 'bg-amber-100 text-amber-800' }}">
                                    {{ !$shipment->status || $shipment->status === 'PENDING' ? 'Menunggu diproses' : $shipment->status }}
                                </span>
                            </div>

                            <div class="mt-4 space-y-3">
                                    @foreach($shipment->fulfillments as $shipmentFulfillment)
                                        <div class="rounded-xl border border-slate-200 bg-white p-4">
                                            <p class="mb-3 text-xs font-bold uppercase tracking-wide {{ $shipmentFulfillment->group_type === \App\Models\TransactionFulfillment::GROUP_READY ? 'text-emerald-700' : 'text-[#AE7C18]' }}">
                                                {{ $shipmentFulfillment->group_type === \App\Models\TransactionFulfillment::GROUP_READY ? 'Ready Stock' : 'Pre-Order' }}
                                            </p>
                                            <div class="space-y-3">
                                                @forelse($shipmentFulfillment->items as $shipmentItem)
                                                    <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3 last:border-b-0 last:pb-0">
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-sm font-bold leading-relaxed text-slate-900 sm:text-base">{{ $shipmentItem->productVariant?->product?->name ?? $shipmentItem->custom_name ?? 'Produk' }}</p>
                                                            <p class="mt-2 text-sm font-semibold">
                                                                <span class="text-slate-700">Ukuran:</span>
                                                                <span class="font-bold text-[#AE7C18]">{{ $shipmentItem->productVariant?->size?->name ?? '-' }}</span>
                                                                <span class="font-semibold text-[#AE7C18]">· Rp{{ number_format((float) ($shipmentItem->productVariant?->price ?? 0), 0, ',', '.') }}</span>
                                                                <span class="font-medium text-slate-500">· {{ $shipmentItem->qty }} pcs</span>
                                                            </p>
                                                            @if($shipmentItem->custom_name)
                                                                <p class="mt-1 text-xs text-slate-600">Nama: <span class="font-semibold">{{ $shipmentItem->custom_name }}</span></p>
                                                            @endif
                                                            @if($shipmentItem->custom_number)
                                                                <p class="mt-1 text-xs text-slate-600">Nomor: <span class="font-semibold">{{ $shipmentItem->custom_number }}</span></p>
                                                            @endif
                                                            @if($shipmentItem->is_longsleeve || $shipmentItem->is_patch)
                                                                <p class="mt-1 text-xs text-slate-500">
                                                                    @if($shipmentItem->is_longsleeve) Longsleeve @endif
                                                                    @if($shipmentItem->is_longsleeve && $shipmentItem->is_patch) · @endif
                                                                    @if($shipmentItem->is_patch) Patch @endif
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <p class="shrink-0 text-right text-sm font-bold text-slate-900">Rp{{ number_format((float) $shipmentItem->subtotal, 0, ',', '.') }}</p>
                                                    </div>
                                                @empty
                                                    <p class="text-xs text-slate-500">Belum ada item untuk kelompok ini.</p>
                                                @endforelse
                                            </div>
                                        </div>
                                    @endforeach
                            </div>

                            <div class="mt-4 rounded-xl border border-slate-100 bg-white p-3">
                                <div class="mb-2 flex items-center justify-between gap-3">
                                    <p class="text-xs font-semibold text-slate-500">Alamat pengiriman</p>
                                    @if($shipmentCanEditAddress)
                                        <button
                                            type="button"
                                            onclick="toggleFulfillmentAddressEditor('shipment-{{ $shipment->id }}', true)"
                                            class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-[#AE7C18]/40 bg-[#AE7C18]/10 px-3 py-1.5 text-xs font-semibold text-[#8A6110] shadow-sm transition hover:bg-[#AE7C18]/20 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/40">
                                            <x-heroicon-o-pencil-square class="h-3.5 w-3.5" />
                                            Edit Alamat
                                        </button>
                                    @endif
                                </div>
                                <p class="mt-2 text-sm font-semibold text-slate-800">{{ $shipment->shipping_name ?: $transaction->shipping_name }}</p>
                                <p class="mt-1 text-xs text-slate-600">{{ $shipment->shipping_phone ?: $transaction->shipping_phone }}</p>
                                <p class="mt-1 whitespace-pre-line text-xs leading-relaxed text-slate-600">{{ $shipment->shipping_address ?: $transaction->shipping_address }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ collect([$shipment->shipping_district ?: $transaction->shipping_district, $shipment->shipping_city ?: $transaction->shipping_city, $shipment->shipping_province ?: $transaction->shipping_province, $shipment->shipping_postal_code ?: $transaction->shipping_postal_code])->filter()->implode(', ') }}
                                </p>
                                @if($shipmentCanEditAddress)
                                    <form id="fulfillment-address-form-shipment-{{ $shipment->id }}"
                                        action="{{ route('admin.transactions.shipments.shipping-address.update', ['transaction' => $transaction, 'shipment' => $shipment]) }}"
                                        method="POST" class="mt-3 hidden space-y-2">
                                        @csrf
                                        @method('PATCH')
                                        <label for="shipment-address-{{ $shipment->id }}" class="block text-xs font-semibold text-slate-600">Ubah alamat sebelum membuat resi</label>
                                        <textarea id="shipment-address-{{ $shipment->id }}" name="shipping_address" rows="3" required maxlength="2000"
                                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">{{ $shipment->shipping_address ?: $transaction->shipping_address }}</textarea>
                                        <p id="fulfillment-address-error-shipment-{{ $shipment->id }}" class="hidden text-xs text-red-600"></p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="submit" class="rounded-lg bg-[#AE7C18] px-3 py-2 text-xs font-semibold text-white">Simpan Alamat</button>
                                            <button type="button" onclick="toggleFulfillmentAddressEditor('shipment-{{ $shipment->id }}', false)" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">Batal</button>
                                        </div>
                                    </form>
                                @endif
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-white p-3">
                                    <p class="text-[10px] font-semibold uppercase text-slate-400">Ongkos kirim</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900">Rp{{ number_format((float) $shipment->shipping_cost, 0, ',', '.') }}</p>
                                </div>
                                <div class="rounded-xl bg-white p-3">
                                    <p class="text-[10px] font-semibold uppercase text-slate-400">Kurir / layanan</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900">{{ $shipment->courier ?: $shipment->courier_code ?: '-' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $shipment->courier_service_code ?: '-' }}</p>
                                </div>
                            </div>

                            @if($shipment->tracking_number || $shipment->biteship_tracking_id || $shipment->biteship_waybill_id)
                                <div class="mt-3 rounded-xl bg-white p-3">
                                    <p class="text-xs font-semibold text-slate-500">Nomor resi / pelacakan</p>
                                    <p class="mt-1 break-all text-sm font-bold text-slate-900">{{ $shipment->tracking_number ?: $shipment->biteship_waybill_id ?: $shipment->biteship_tracking_id }}</p>
                                    @if($shipment->biteship_status)
                                        <p class="mt-1 text-xs text-slate-500">Status kurir: {{ $shipment->biteship_status }}</p>
                                    @endif
                                </div>
                            @endif

                            @if($shipmentCanProcess)
                                <form class="fulfillment-ship-form mt-4 space-y-3"
                                    data-action-title="Proses {{ $shipmentLabel }}"
                                    data-action-message="Pastikan alamat dan layanan kurir sudah benar sebelum melanjutkan."
                                    data-action-confirm-text="Ya, Proses Pengiriman"
                                    action="{{ route('admin.transactions.shipments.ship', ['transaction' => $transaction, 'shipment' => $shipment]) }}"
                                    method="POST">
                                    @csrf
                                    @method('PATCH')
                                    @if(!$shipment->courier_code || !$shipment->courier_service_code)
                                        <input type="text" name="courier" placeholder="Nama kurir manual" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                        <input type="text" name="tracking_number" placeholder="Nomor resi manual" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                    @endif
                                    <button type="submit" class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white">Proses Pengiriman</button>
                                </form>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
            @php
                $shippedFulfillmentIds = $transaction->shipments
                    ->where('shipment_type', 'combined')
                    ->flatMap(fn ($shipment) => $shipment->fulfillments->pluck('id'))
                    ->all();
            @endphp
            <div class="mx-auto grid w-full grid-cols-1 gap-4 {{ $transaction->fulfillments->count() > 1 ? 'xl:grid-cols-2' : 'xl:max-w-2xl' }}">
                @foreach($transaction->fulfillments as $fulfillment)
                    @if(in_array($fulfillment->id, $shippedFulfillmentIds, true))
                        @continue
                    @endif
                    @php
                        $isReadyGroup = $fulfillment->group_type === \App\Models\TransactionFulfillment::GROUP_READY;
                        $groupLabel = $isReadyGroup ? 'Ready Stock' : 'Pre-Order';
                        $groupStatusLabels = [
                            'PENDING' => 'Menunggu diproses',
                            'PROCESSING' => 'Sedang diproses',
                            'SHIPPED' => 'Dalam pengiriman',
                            'COMPLETED' => 'Selesai',
                            'CANCELLED' => 'Dibatalkan',
                        ];
                        $canEditGroupAddress = in_array($transaction->status, ['PAID', 'ORDER_PROCESSING', 'ORDER_SHIPPED'], true)
                            && $transaction->shipments->isEmpty()
                            && $fulfillment->shipping_method === 'Kurir'
                            && !$fulfillment->biteship_order_id
                            && !$fulfillment->biteship_tracking_id
                            && !$fulfillment->biteship_waybill_id
                            && !$fulfillment->tracking_number;
                        $canProcessGroup = in_array($transaction->status, ['ORDER_PROCESSING', 'ORDER_SHIPPED'], true)
                            && !in_array($fulfillment->status, ['SHIPPED', 'COMPLETED', 'CANCELLED'], true);
                    @endphp

                    <article class="min-w-0 rounded-2xl border border-slate-200 bg-slate-50/60 p-4 sm:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <span class="inline-flex text-[12px] font-bold uppercase tracking-wide {{ $isReadyGroup ? 'text-emerald-700' : 'text-[#AE7C18]' }}">
                                    {{ $groupLabel }}
                                </span>
                                <h3 class="mt-2 text-sm font-bold text-slate-900">
                                    {{ $fulfillment->shipping_method ?? 'Metode belum ditentukan' }}
                                </h3>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[12px] font-semibold text-slate-600 ring-1 ring-slate-200">
                                {{ $groupStatusLabels[$fulfillment->status] ?? $fulfillment->status ?? '-' }}
                            </span>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div>
                                <p class="text-[10px] uppercase tracking-wide text-slate-600 sm:text-xs font-medium">Produk</p>
                                <div class="mt-3 space-y-3">
                                    @forelse($fulfillment->items as $fulfillmentItem)
                                        <div class="flex items-start justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-bold leading-relaxed text-slate-900 sm:text-base">
                                                    {{ $fulfillmentItem->productVariant?->product?->name ?? $fulfillmentItem->custom_name ?? 'Produk' }}
                                                </p>

                                                <p class="mt-2 text-sm font-semibold">
                                                    <span class="text-slate-700">Ukuran:</span>
                                                    <span class="font-bold text-[#AE7C18]">{{ $fulfillmentItem->productVariant?->size?->name ?? '-' }}</span>
                                                    <span class="font-semibold text-[#AE7C18]">
                                                        · Rp{{ number_format((float) ($fulfillmentItem->productVariant?->price ?? 0), 0, ',', '.') }}
                                                    </span>
                                                    <span class="font-medium text-slate-500">· {{ $fulfillmentItem->qty }} pcs</span>
                                                </p>

                                                @if($fulfillmentItem->custom_name)
                                                    <p class="mt-1.5 text-sm">
                                                        <span class="font-semibold text-slate-700">Nama:</span>
                                                        <span class="font-bold uppercase text-[#AE7C18]">{{ $fulfillmentItem->custom_name }}</span>
                                                    </p>
                                                @endif

                                                @if($fulfillmentItem->custom_number)
                                                    <p class="mt-1.5 text-sm">
                                                        <span class="font-semibold text-slate-700">Nomor:</span>
                                                        <span class="font-bold text-[#AE7C18]">{{ $fulfillmentItem->custom_number }}</span>
                                                    </p>
                                                @endif
                                                @if($fulfillmentItem->is_longsleeve)
                                                    <p class="mt-1 text-sm">
                                                        <span class="font-semibold text-slate-700">Longsleeve</span>
                                                        <span class="font-semibold text-[#AE7C18]">
                                                            (+ Rp {{ number_format((int) $fulfillmentItem->longsleeve_price, 0, ',', '.') }})
                                                        </span>
                                                    </p>
                                                @endif

                                                @if($fulfillmentItem->is_patch)
                                                    <p class="mt-1 text-sm">
                                                        <span class="font-semibold text-slate-700">Patch</span>
                                                        <span class="font-semibold text-[#AE7C18]">
                                                            (+ Rp {{ number_format((int) $fulfillmentItem->patch_price, 0, ',', '.') }})
                                                        </span>
                                                    </p>
                                                @endif
                                            </div>

                                            <p class="shrink-0 text-right text-sm font-bold leading-relaxed text-slate-900 sm:text-base">
                                                Rp{{ number_format((float) $fulfillmentItem->subtotal, 0, ',', '.') }}
                                            </p>
                                        </div>
                                    @empty
                                        <p class="rounded-xl bg-white p-4 text-sm text-slate-600">
                                            Belum ada item yang terhubung ke pesanan ini.
                                        </p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl border border-slate-100 bg-white p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-slate-400 sm:text-xs font-medium">Ongkos kirim</p>
                                    <p class="mt-1 text-sm font-bold text-slate-900">
                                        Rp{{ number_format((float) $fulfillment->shipping_cost, 0, ',', '.') }}
                                    </p>
                                </div>
                                <div class="rounded-xl border border-slate-100 bg-white p-3">
                                    @if($fulfillment->shipping_method === 'Kurir')
                                        <p class="text-[10px] uppercase tracking-wide text-slate-400 sm:text-xs font-medium">Pengiriman</p>
                                        <p class="mt-1 text-sm font-bold text-slate-900">Menunggu proses pengiriman</p>
                                        <p class="mt-1 text-xs leading-relaxed text-slate-500">
                                            Pesanan akan diproses untuk pengiriman melalui kurir.
                                        </p>
                                    @else
                                        <p class="text-[10px] uppercase tracking-wide text-slate-400">Jadwal Pengambilan</p>
                                        <p class="mt-1 text-sm font-bold text-slate-900">
                                            @if($fulfillment->pickup_date)
                                                {{ \Carbon\Carbon::parse($fulfillment->pickup_date)->locale('id')->translatedFormat('d M Y') }}
                                                @if($fulfillment->pickup_time_start && $fulfillment->pickup_time_end)
                                                    <span class="block mt-1 font-bold text-slate-500">
                                                        {{ \Carbon\Carbon::parse($fulfillment->pickup_time_start)->format('H:i') }}-{{ \Carbon\Carbon::parse($fulfillment->pickup_time_end)->format('H:i') }} WITA
                                                    </span>
                                                @endif
                                            @else
                                                Mengikuti jadwal pemrosesan
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>

                            @if($fulfillment->shipping_method === 'Kurir')
                                <div class="rounded-xl border border-slate-100 bg-white p-3">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="text-[10px] uppercase tracking-wide text-slate-400 sm:text-xs font-medium">Alamat pengiriman</p>
                                        @if($canEditGroupAddress)
                                            <button type="button"
                                                onclick="toggleFulfillmentAddressEditor({{ $fulfillment->id }}, true)"
                                                class="inline-flex items-center gap-1.5 rounded-lg border border-[#AE7C18]/40 bg-[#AE7C18]/10 px-3 py-1.5 text-xs font-semibold text-[#8A6110] shadow-sm transition hover:bg-[#AE7C18]/20 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/40">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 20h9"/>
                                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                                                </svg>
                                                Edit Alamat
                                            </button>
                                        @endif
                                    </div>

                                    <p class="mt-1 font-semibold text-sm leading-relaxed text-slate-800">
                                        {{ $fulfillment->shipping_address ?: $transaction->shipping_address ?: '-' }}
                                    </p>
                                    <p class="mt-1 font-semibold text-[11px] text-slate-500">
                                        {{ $fulfillment->shipping_district ?: $transaction->shipping_district }},
                                        {{ $fulfillment->shipping_city ?: $transaction->shipping_city }},
                                        {{ $fulfillment->shipping_province ?: $transaction->shipping_province }}
                                        {{ $fulfillment->shipping_postal_code ?: $transaction->shipping_postal_code }}
                                    </p>

                                    @if($canEditGroupAddress)
                                        <form id="fulfillment-address-form-{{ $fulfillment->id }}"
                                            action="{{ route('admin.transactions.fulfillments.shipping-address.update', ['transaction' => $transaction, 'fulfillment' => $fulfillment]) }}"
                                            method="POST" class="mt-3 hidden space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <label for="fulfillment-address-{{ $fulfillment->id }}" class="block text-xs font-semibold text-slate-600">
                                                Alamat baru untuk {{ $groupLabel }}
                                            </label>
                                            <textarea id="fulfillment-address-{{ $fulfillment->id }}"
                                                name="shipping_address" rows="3" maxlength="2000" required
                                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800">{{ $fulfillment->shipping_address ?: $transaction->shipping_address }}</textarea>
                                            <p id="fulfillment-address-error-{{ $fulfillment->id }}" class="hidden text-xs text-red-600"></p>
                                            <div class="flex flex-wrap gap-2">
                                                <button type="submit" class="rounded-lg bg-[#AE7C18] px-3 py-2 text-xs font-semibold text-white">Simpan alamat</button>
                                                <button type="button" onclick="toggleFulfillmentAddressEditor({{ $fulfillment->id }}, false)"
                                                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">Batal</button>
                                            </div>
                                        </form>
                                    @endif
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-xl border border-slate-100 bg-white p-3">
                                        <p class="text-[10px] uppercase tracking-wide text-slate-400 sm:text-xs font-medium">Kurir</p>
                                        <p class="mt-1 break-words text-xs font-semibold text-slate-800">{{ $fulfillment->courier ?: '-' }}</p>
                                    </div>
                                    <div class="rounded-xl border border-slate-100 bg-white p-3">
                                        <p class="text-[10px] uppercase tracking-wide text-slate-400 sm:text-xs font-medium">Nomor resi</p>
                                        <p class="mt-1 break-all text-xs font-semibold text-slate-800">{{ $fulfillment->tracking_number ?: $fulfillment->biteship_waybill_id ?: '-' }}</p>
                                    </div>
                                </div>

                                @if($fulfillment->biteship_status)
                                    <p class="text-[11px] text-slate-500">
                                        Status Biteship:
                                        <span class="font-semibold">{{ str_replace('_', ' ', ucfirst($fulfillment->biteship_status)) }}</span>
                                    </p>
                                @endif

                                @if(
                                    $fulfillment->status === \App\Models\TransactionFulfillment::STATUS_SHIPPED
                                    && !$fulfillment->biteship_order_id
                                    && in_array($transaction->status, ['ORDER_PROCESSING', 'ORDER_SHIPPED'], true)
                                )
                                    <form class="fulfillment-complete-form"
                                        data-action-title="Konfirmasi {{ $groupLabel }} selesai"
                                        data-action-message="Pastikan {{ $groupLabel }} benar-benar sudah selesai sebelum melanjutkan."
                                        data-action-confirm-text="Ya, Konfirmasi Selesai"
                                        action="{{ route('admin.transactions.fulfillments.complete', ['transaction' => $transaction, 'fulfillment' => $fulfillment]) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <p class="fulfillment-action-error hidden mb-2 text-xs text-red-600"></p>
                                        <button type="submit"
                                            class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                            <x-heroicon-o-check-circle class="mr-1 inline h-4 w-4" />
                                            Konfirmasi pengiriman {{ $groupLabel }} selesai
                                        </button>
                                    </form>
                                @endif

                                @if($canProcessGroup && $transaction->shipments->isEmpty())
                                    <form class="fulfillment-ship-form space-y-2"
                                        data-action-title="Proses pengiriman {{ $groupLabel }}"
                                        data-action-message="Pengiriman {{ $groupLabel }} akan diproses sekarang. Lanjutkan?"
                                        data-action-confirm-text="Ya, Proses Pengiriman"
                                        action="{{ route('admin.transactions.fulfillments.ship', ['transaction' => $transaction, 'fulfillment' => $fulfillment]) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')

                                        @if(!$fulfillment->courier_code || !$fulfillment->courier_service_code)
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <div>
                                                    <label class="mb-1 block text-xs font-semibold text-slate-600">Nama kurir</label>
                                                    <input name="courier" value="{{ $fulfillment->courier }}" maxlength="100"
                                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs"
                                                        placeholder="Contoh: JNE">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-semibold text-slate-600">Nomor resi</label>
                                                    <input name="tracking_number" value="{{ $fulfillment->tracking_number }}" maxlength="255"
                                                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs"
                                                        placeholder="Masukkan nomor resi">
                                                </div>
                                            </div>
                                        @endif

                                        <p class="fulfillment-action-error hidden text-xs text-red-600"></p>
                                        <button type="submit" class="w-full rounded-xl bg-[#AE7C18] px-4 py-2.5 text-xs font-semibold text-white hover:bg-[#96690F]">
                                            <x-heroicon-o-truck class="mr-1 inline h-4 w-4" />
                                            Proses pengiriman {{ $groupLabel }}
                                        </button>
                                    </form>
                                @endif

                            @elseif($fulfillment->shipping_method === 'Ambil di Tempat')
                                <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-3">
                                    <p class="text-xs font-semibold text-emerald-800">Pengambilan langsung</p>
                                    <p class="mt-1 text-xs leading-relaxed text-emerald-700">
                                        Beri tau pelanggan bahwa pesanan nya sudah selesai.
                                    </p>
                                </div>

                                @if($canProcessGroup)
                                    <form class="fulfillment-complete-form"
                                        data-action-title="Konfirmasi {{ $groupLabel }} selesai"
                                        data-action-message="Pastikan {{ $groupLabel }} benar-benar sudah selesai sebelum melanjutkan."
                                        data-action-confirm-text="Ya, Konfirmasi Selesai"
                                        action="{{ route('admin.transactions.fulfillments.complete', ['transaction' => $transaction, 'fulfillment' => $fulfillment]) }}"
                                        method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <p class="fulfillment-action-error hidden mb-2 text-xs text-red-600"></p>
                                        <button type="submit" class="w-full rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                            <x-heroicon-o-check-circle class="mr-1 inline h-4 w-4" />
                                            Konfirmasi Barang Sudah Selesai
                                        </button>
                                    </form>
                                @endif
                            @endif

                            @if($fulfillment->status === \App\Models\TransactionFulfillment::STATUS_COMPLETED)
                                <p class="rounded-xl border border-emerald-100 bg-emerald-50 p-3 text-xs font-semibold text-emerald-700">
                                    Pesanan ini sudah selesai.
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
    <div class="grid grid-cols-1 items-start gap-4 sm:gap-6 lg:grid-cols-3">
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
                                        <span class="font-bold uppercase text-[#AE7C18]">{{ $item->custom_name }}</span>
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
                                <p class="font-bold text-slate-900 text-xs sm:text-sm">Rp {{ number_format($item->subtotal,0,',','.') }}</p>
                                <p class="text-[10px] sm:text-xs text-slate-400">{{ $item->qty }} pcs</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        <div class="space-y-4 sm:space-y-6">
            <div class="rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <h2 class="mb-3 sm:mb-4 font-bold text-slate-900 text-sm sm:text-base">Pembayaran</h2>
                <div class="space-y-2.5 text-xs sm:text-sm">
                    <div class="flex justify-between gap-4"><span class="font-medium text-slate-500">Metode</span><span class="font-bold text-slate-900 text-right">{{ $transaction->payment_method }}</span></div>

                    @if($transaction->va_number)
                        <div class="flex justify-between gap-4"><span class="font-medium text-slate-500">VA Number</span><span class="font-bold text-slate-900 text-right break-all">{{ $transaction->va_number }}</span></div>
                        <div class="flex justify-between gap-4">
                            <span class="font-medium text-slate-500">Expired</span>
                            <span class="text-slate-700 text-right">{{ $transaction->va_expired_at ? $transaction->va_expired_at->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y H:i') . ' WITA' : '-' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl sm:rounded-3xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <h2 class="mb-3 sm:mb-4 font-bold text-slate-900 text-sm sm:text-base">Ringkasan</h2>
                <div class="space-y-2.5 text-xs sm:text-sm">
                    <div class="flex justify-between gap-4"><span class="font-medium text-slate-500">Subtotal</span><span class="font-medium text-slate-900">Rp {{ number_format($transaction->subtotal,0,',','.') }}</span></div>
                    <div class="flex justify-between gap-4"><span class="font-medium text-slate-500">Diskon</span><span class="font-medium text-slate-900">Rp {{ number_format($transaction->discount,0,',','.') }}</span></div>
                    <div class="flex justify-between gap-4"><span class="font-medium text-slate-500">Pengiriman</span><span class="font-medium text-slate-900">Rp {{ number_format($transaction->shipping,0,',','.') }}</span></div>
                    <div class="border-t border-slate-100 pt-3 flex justify-between gap-4 text-sm sm:text-base font-bold"><span class="text-slate-900">Total</span><span class="text-[#AE7C18]">Rp {{ number_format($transaction->total,0,',','.') }}</span></div>
                </div>
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
                                    @if($history->note)<p class="mt-0.5 text-xs sm:text-sm leading-relaxed text-slate-500">{{ $history->note }}</p>@endif
                                    <p class="mt-0.5 text-[10px] sm:text-xs text-slate-400">{{ $history->created_at->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y H:i') }} WITA</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
    </div>

    @if(in_array($transaction->status, ['ORDER_PROCESSING', 'ORDER_SHIPPED', 'ORDER_COMPLETED'], true))
        <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">
            @if($transaction->fulfillments->isEmpty())
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
                                    onclick="openTransactionActionModal('ship', '{{ $transaction->id }}')"
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
                                onclick="openTransactionActionModal('complete', '{{ $transaction->id }}')"
                                class="mt-4 inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm transition hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/30"
                            >
                                <x-heroicon-o-check-circle class="h-4 w-4" />
                                Konfirmasi Pesanan Siap Diambil Pelanggan
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
            @endif
        </div>
    @endif
</div>

@include('admin.transactions.partials.action-confirmation-modal')

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

function openTransactionActionModal(type, id) {
    window.dispatchEvent(
        new CustomEvent('open-transaction-action', {
            detail: {
                type: type,
                id: id
            }
        })
    );
}
function toggleShippingAddressEditor(show) {
    const form = document.getElementById('shipping-address-form');
    const error = document.getElementById('shipping-address-error');
    if (!form) return;

    form.classList.toggle('hidden', !show);
    if (error) {
        error.textContent = '';
        error.classList.add('hidden');
    }
}

document.getElementById('shipping-address-form')?.addEventListener('submit', async function (event) {
    event.preventDefault();

    const form = this;
    const button = document.getElementById('shipping-address-save');
    const error = document.getElementById('shipping-address-error');
    const originalLabel = button.textContent;
    const body = new URLSearchParams(new FormData(form));

    button.disabled = true;
    button.textContent = 'Menyimpan...';
    error.textContent = '';
    error.classList.add('hidden');

    try {
        const response = await fetch(form.action, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body.toString()
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            const message = data.errors?.shipping_address?.[0] || data.message || 'Alamat gagal diperbarui.';
            error.textContent = message;
            error.classList.remove('hidden');
            return;
        }

        alert(data.message);
        window.location.reload();
    } catch (e) {
        error.textContent = 'Terjadi kesalahan saat menyimpan alamat. Silakan coba lagi.';
        error.classList.remove('hidden');
    } finally {
        button.disabled = false;
        button.textContent = originalLabel;
    }
});
function toggleCustomerContactEditor(show) {
    const form = document.getElementById('customer-contact-form');
    const error = document.getElementById('customer-contact-error');
    if (!form) return;

    form.classList.toggle('hidden', !show);
    if (error) {
        error.textContent = '';
        error.classList.add('hidden');
    }
}

document.getElementById('customer-contact-form')?.addEventListener('submit', async function (event) {
    event.preventDefault();

    const form = this;
    const button = document.getElementById('customer-contact-save');
    const error = document.getElementById('customer-contact-error');
    const originalLabel = button.textContent;
    const body = new URLSearchParams(new FormData(form));

    button.disabled = true;
    button.textContent = 'Menyimpan...';
    error.textContent = '';
    error.classList.add('hidden');

    try {
        const response = await fetch(form.action, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: body.toString()
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            const message =
                data.errors?.shipping_name?.[0] ||
                data.errors?.shipping_phone?.[0] ||
                data.errors?.shipping_email?.[0] ||
                data.errors?.contact?.[0] ||
                data.message ||
                'Kontak pelanggan gagal diperbarui.';

            error.textContent = message;
            error.classList.remove('hidden');
            return;
        }

        alert(data.message);
        window.location.reload();
    } catch (e) {
        error.textContent = 'Terjadi kesalahan saat menyimpan kontak. Silakan coba lagi.';
        error.classList.remove('hidden');
    } finally {
        button.disabled = false;
        button.textContent = originalLabel;
    }
});

function toggleFulfillmentAddressEditor(id, show) {
    const form = document.getElementById(`fulfillment-address-form-${id}`);
    const error = document.getElementById(`fulfillment-address-error-${id}`);
    if (!form) return;

    form.classList.toggle('hidden', !show);
    if (error) {
        error.textContent = '';
        error.classList.add('hidden');
    }
}

document.querySelectorAll('[id^="fulfillment-address-form-"]').forEach(form => {
    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const button = form.querySelector('button[type="submit"]');
        const error = form.querySelector('[id^="fulfillment-address-error-"]');
        const originalLabel = button.textContent;

        button.disabled = true;
        button.textContent = 'Menyimpan...';
        error?.classList.add('hidden');

        try {
            const response = await fetch(form.action, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                },
                body: new URLSearchParams(new FormData(form)).toString()
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                const message = data.errors?.shipping_address?.[0] || data.message || 'Alamat gagal diperbarui.';
                if (error) {
                    error.textContent = message;
                    error.classList.remove('hidden');
                } else {
                    alert(message);
                }
                return;
            }

            alert(data.message || 'Alamat berhasil diperbarui.');
            window.location.reload();
        } catch (e) {
            if (error) {
                error.textContent = 'Terjadi kesalahan saat menyimpan alamat.';
                error.classList.remove('hidden');
            } else {
                alert('Terjadi kesalahan saat menyimpan alamat.');
            }
        } finally {
            button.disabled = false;
            button.textContent = originalLabel;
        }
    });
});

function openFulfillmentActionConfirmation(form, type) {
    window.dispatchEvent(new CustomEvent('open-transaction-action', {
        detail: {
            type: type,
            id: '{{ $transaction->id }}',
            url: form.action,
            payload: Object.fromEntries(new FormData(form).entries()),
            title: form.dataset.actionTitle || (type === 'ship' ? 'Konfirmasi Proses Pengiriman' : 'Konfirmasi Pesanan Selesai'),
            message: form.dataset.actionMessage || 'Pastikan tindakan ini sudah benar sebelum melanjutkan.',
            confirmText: form.dataset.actionConfirmText || (type === 'ship' ? 'Ya, Proses Pengiriman' : 'Ya, Konfirmasi'),
            fulfillment: true
        }
    }));
}

document.querySelectorAll('.fulfillment-ship-form').forEach(form => {
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        openFulfillmentActionConfirmation(this, 'ship');
    });
});

document.querySelectorAll('.fulfillment-complete-form').forEach(form => {
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        openFulfillmentActionConfirmation(this, 'complete');
    });
});
</script>
@endsection
