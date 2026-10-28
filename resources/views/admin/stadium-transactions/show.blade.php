@extends('admin.layouts.app')

@section('title', 'Detail Transaksi Stadion')
@section('page-title', 'Detail Transaksi Stadion')

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
            <a href="{{ route('admin.stadium-transactions.index') }}"
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
                    <p class="text-xs text-slate-500">Data pembeli Stadion</p>
                </div>
            </div>

            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Nama Pelanggan</p>
            <p class="mt-1 font-semibold text-slate-900">
                {{ $transaction->customer?->name ?? $transaction->shipping_name ?? 'Pelanggan Stadion' }}
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
                    <p class="text-xs text-slate-500">Ringkasan transaksi Stadion</p>
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
    <div class="grid grid-cols-1 items-stretch gap-5 lg:grid-cols-2">

        {{-- Kolom kiri: Rincian Produk --}}
        @if ((str_starts_with($transaction->invoice_number ?? '', 'BOOTH-LEGACY-') || str_starts_with($transaction->invoice_number ?? '', 'STADIUM-LEGACY-')))
            @php
                $legacyProducts = [];

                foreach ($transaction->items as $item) {
                    $product = $item->productVariant?->product;
                    $variant = $item->productVariant;
                    $name = $product?->name ?? 'Produk tidak tersedia';
                    $size = $variant?->size?->name;
                    $key = $name . '|' . ($size ?? '');

                    if (!isset($legacyProducts[$key])) {
                        $legacyProducts[$key] = [
                            'name' => $name,
                            'size' => $size,
                            'qty' => 0,
                        ];
                    }

                    $legacyProducts[$key]['qty'] += (int) $item->qty;
                }

                $legacyProducts = collect($legacyProducts)->values();
                $uniqueProductCount = $legacyProducts->count();
            @endphp

            <div class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 p-5">
                    <h2 class="font-bold text-slate-900">Rincian Produk</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        {{ $transaction->items->sum('qty') }} barang · {{ $uniqueProductCount }} jenis produk
                    </p>
                </div>

                <div class="flex flex-1 flex-col gap-4 p-5">
                    @if ($uniqueProductCount <= 2)
                        @foreach ($legacyProducts as $product)
                            <div class="rounded-xl border border-slate-100 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-semibold text-slate-900">{{ $product['name'] }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">
                                            @if ($product['size'])
                                                Ukuran {{ $product['size'] }} ·
                                            @endif
                                            {{ $product['qty'] }} pcs
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                        {{ $product['qty'] }} pcs
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        @php
                            $legacyGroups = [];

                            foreach ($transaction->items as $item) {
                                $name = $item->productVariant?->product?->name ?? 'Produk';
                                $version = stripos($name, 'Supporter') !== false
                                    ? 'Supporter'
                                    : 'Stadium';

                                preg_match('/\b(Home|Away|Third)\b/i', $name, $match);
                                $kit = isset($match[1])
                                    ? ucfirst(strtolower($match[1]))
                                    : 'Produk';

                                $key = $version . '|' . $kit;

                                if (!isset($legacyGroups[$key])) {
                                    $legacyGroups[$key] = [
                                        'version' => $version,
                                        'kit' => $kit,
                                        'qty' => 0,
                                        'items' => 0,
                                    ];
                                }

                                $legacyGroups[$key]['qty'] += (int) $item->qty;
                                $legacyGroups[$key]['items']++;
                            }

                            $legacyGroups = collect($legacyGroups)
                                ->groupBy('version')
                                ->map(function ($products, $version) {
                                    $kits = $products->pluck('kit')->unique()->values();

                                    $labels = $kits->count() > 1
                                        ? $kits->slice(0, -1)->implode(', ') . ' & ' . $kits->last()
                                        : $kits->first();

                                    return [
                                        'name' => $version . ' ' . $labels,
                                        'qty' => $products->sum('qty'),
                                        'items' => $products->sum('items'),
                                    ];
                                });
                        @endphp

                        @foreach ($legacyGroups as $groupName => $group)
                            @if ($group['items'] > 0)
                                <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-100 p-4">
                                    <div>
                                        <h3 class="font-semibold text-slate-900">{{ $group['name'] }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $group['items'] }} jenis produk · {{ $group['qty'] }} pcs
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                        {{ $group['qty'] }} pcs
                                    </span>
                                </div>
                            @endif
                        @endforeach

                        <button
                            type="button"
                            onclick="const m=document.getElementById('legacy-product-modal'); m.classList.remove('hidden'); m.classList.add('flex'); document.body.classList.add('overflow-hidden'); requestAnimationFrame(()=>{m.classList.remove('opacity-0'); const p=m.querySelector('.legacy-modal-panel'); if(p){p.classList.remove('opacity-0','scale-95','translate-y-2'); p.classList.add('opacity-100','scale-100','translate-y-0')} })"
                            class="mt-auto inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#96690F]"
                        >
                            <x-heroicon-o-eye class="h-4 w-4"/>
                            Lihat Detail Pesanan Produk
                        </button>
                    @endif
                </div>
            </div>

            <div
                id="legacy-product-modal"
                class="fixed inset-0 z-[9999] hidden items-center justify-center overflow-y-auto bg-slate-950/50 p-3 opacity-0 backdrop-blur-sm transition-opacity duration-200 sm:p-6"
                onclick="if (event.target === this) { this.classList.add('hidden'); this.classList.remove('flex'); document.body.classList.remove('overflow-hidden') }"
            >
                <div class="legacy-modal-panel my-auto flex max-h-[85vh] w-full max-w-3xl scale-95 translate-y-2 flex-col overflow-hidden rounded-2xl bg-white opacity-0 shadow-2xl transition-all duration-200 ease-out">
                    <div class="flex items-center justify-between border-b border-slate-200 p-4 sm:p-5">
                        <div>
                            <h2 class="font-bold text-slate-900">Detail Pesanan Produk</h2>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $transaction->items->sum('qty') }} pcs · {{ $transaction->items->count() }} jenis produk
                            </p>
                        </div>
                        <button
                            type="button"
                            onclick="const m=document.getElementById('legacy-product-modal'); m.classList.add('hidden'); m.classList.remove('flex','opacity-0'); const p=m.querySelector('.legacy-modal-panel'); if(p){p.classList.add('opacity-0','scale-95','translate-y-2');p.classList.remove('opacity-100','scale-100','translate-y-0')} document.body.classList.remove('overflow-hidden')"
                            class="rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                            aria-label="Tutup detail produk"
                        >
                            <x-heroicon-o-x-mark class="h-5 w-5"/>
                        </button>
                    </div>

                    <div class="overflow-y-auto overscroll-contain">
                        @foreach ($transaction->items as $item)
                            @php
                                $variant = $item->productVariant;
                                $product = $variant?->product;
                            @endphp
                            <div class="border-b border-slate-100 p-4 last:border-b-0 sm:p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="text-sm font-semibold text-slate-900">
                                            {{ $product?->name ?? 'Produk tidak tersedia' }}
                                        </h3>
                                        <div class="mt-2 space-y-1 text-xs text-slate-500">
                                            @if ($variant?->size?->name)
                                                <p>Ukuran: {{ $variant->size->name }}</p>
                                            @endif
                                            @if ($item->is_longsleeve)
                                                <p>Lengan panjang</p>
                                            @endif
                                            @if ($item->custom_name)
                                                <p>{{ mb_strtoupper($item->custom_name, 'UTF-8') === 'NNS HISTORIS' ? 'NNS' : mb_strtoupper($item->custom_name, 'UTF-8') }}</p>
                                            @endif
                                            @if ($item->custom_number && mb_strtoupper((string) $item->custom_name, 'UTF-8') !== 'NNS HISTORIS')
                                                <p>Nomor jersey: {{ $item->custom_number }}</p>
                                            @endif
                                        </div>
                                        <p class="mt-3 text-xs text-slate-500">
                                            {{ $item->qty }} x Rp{{ number_format(max(0, (float) $item->price - (float) $item->longsleeve_price - (float) $item->patch_price - (in_array(mb_strtoupper(trim((string) $item->custom_name), 'UTF-8'), ['NNS', 'NNS HISTORIS'], true) ? 150000 : 0)), 0, ',', '.') }}
                                            @if ((float) $item->longsleeve_price > 0)
                                                + Rp{{ number_format((float) $item->longsleeve_price, 0, ',', '.') }} lengan panjang
                                            @endif
                                            @if ((float) $item->patch_price > 0)
                                                + Rp{{ number_format((float) $item->patch_price, 0, ',', '.') }} Patch
                                            @endif
                                            @if (mb_strtoupper(trim((string) $item->custom_name), 'UTF-8') === 'NNS HISTORIS' || mb_strtoupper(trim((string) $item->custom_name), 'UTF-8') === 'NNS')
                                                + Rp150.000 NNS
                                            @endif
                                        </p>
                                    </div>
                                    <p class="shrink-0 text-sm font-bold text-slate-900">
                                        Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between gap-3 border-t border-slate-200 p-4">
                        <span class="text-sm text-slate-500">Total transaksi</span>
                        <span class="font-bold text-slate-900">
                            Rp{{ number_format((float) $transaction->total, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
        @else
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
                                        @if ($variant?->size?->name)
                                            <p>Ukuran: {{ $variant->size->name }}</p>
                                        @endif
                                        @if ($item->custom_name)
                                            <p>Nama jersey: {{ mb_strtoupper($item->custom_name, 'UTF-8') }}</p>
                                        @endif
                                        @if ($item->custom_number)
                                            <p>Nomor jersey: {{ $item->custom_number }}</p>
                                        @endif
                                    </div>
                                    <p class="mt-3 text-sm text-slate-500">
                                        {{ $item->qty }} x Rp{{ number_format((float) $item->price, 0, ',', '.') }}
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
        @endif
        {{-- Kolom kanan: Ringkasan Pembayaran --}}
        <div class="h-full rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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
