@extends('admin.layouts.app')

@section('title', 'Transaksi Offline')
@section('page-title', 'Transaksi Offline')

@section('content')

<div class="space-y-4 sm:space-y-6">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 sm:text-3xl">
                Transaksi Offline
            </h2>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Riwayat penjualan offline Eazywear.
            </p>
        </div>

        <div class="grid w-full grid-cols-1 gap-2 lg:w-auto lg:min-w-0">
            <a
                href="{{ route('admin.kasir') }}"
                class="hidden items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 py-3 font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] active:scale-95 lg:inline-flex"
            >
                Kembali ke Kasir
            </a>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-2 sm:gap-4 xl:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:rounded-2xl sm:p-5">
            <p class="text-xs text-slate-500 sm:text-sm">Total Transaksi</p>
            <p class="mt-1.5 text-xl font-bold text-slate-900 sm:mt-2 sm:text-2xl">
                {{ number_format($summary['total_transactions'], 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:rounded-2xl sm:p-5">
            <p class="text-xs text-slate-500 sm:text-sm">Transaksi Lunas</p>
            <p class="mt-1.5 text-xl font-bold text-emerald-600 sm:mt-2 sm:text-2xl">
                {{ number_format($summary['paid_transactions'], 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:rounded-2xl sm:p-5">
            <p class="text-xs text-slate-500 sm:text-sm">Menunggu Pembayaran</p>
            <p class="mt-1.5 text-xl font-bold text-amber-600 sm:mt-2 sm:text-2xl">
                {{ number_format($summary['pending_transactions'], 0, ',', '.') }}
            </p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:rounded-2xl sm:p-5">
            <p class="text-xs text-slate-500 sm:text-sm">Total Penjualan</p>
            <p class="mt-1.5 text-lg font-bold text-slate-900 sm:mt-2 sm:text-xl">
                Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
            </p>
            <p class="mt-1 text-[10px] leading-snug text-slate-400 sm:text-xs">Transaksi dengan status penjualan yang diakui</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.offline-transactions.index') }}"
          class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm sm:rounded-2xl sm:p-5">
        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 sm:gap-3 xl:grid-cols-5">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Cari invoice, nama, telepon..."
                class="min-w-0 rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-100 sm:rounded-xl sm:px-4 sm:py-3"
            >

            <select name="status"
                    class="min-w-0 rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none sm:rounded-xl sm:px-4 sm:py-3">
                <option value="">Semua Status</option>
                @foreach (['PENDING', 'PAID', 'EXPIRED', 'CANCELLED', 'COMPLETED'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                        {{ $status }}
                    </option>
                @endforeach
            </select>

            <select name="month"
                    class="min-w-0 rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none sm:rounded-xl sm:px-4 sm:py-3">
                <option value="">Semua Bulan</option>
                @foreach (range(1, 12) as $month)
                    <option value="{{ $month }}" @selected((string) request('month') === (string) $month)>
                        {{ \Carbon\Carbon::create()->month($month)->locale('id')->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>

            <input
                type="number"
                name="year"
                value="{{ request('year') }}"
                placeholder="Tahun"
                min="2000"
                max="2100"
                class="min-w-0 rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none sm:rounded-xl sm:px-4 sm:py-3"
            >

            <div class="flex gap-2">
                <button type="submit"
                        class="min-h-10 flex-1 rounded-lg bg-[#AE7C18] px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-[#96690F] sm:rounded-xl sm:px-4 sm:py-3">
                    <x-heroicon-o-magnifying-glass class="mr-1 inline h-4 w-4"/>
                    Cari
                </button>
                <a href="{{ route('admin.offline-transactions.index') }}"
                   class="min-h-10 rounded-lg border border-slate-300 px-3 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 sm:rounded-xl sm:px-4 sm:py-3">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm sm:rounded-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-3 py-3 sm:px-5 sm:py-4">
            <div>
                <h3 class="font-bold text-slate-900">Riwayat Penjualan Offline</h3>
                <p class="mt-1 text-xs text-slate-500">
                    {{ $transactions->total() }} transaksi ditemukan
                </p>
            </div>
        </div>

        {{-- MOBILE: CARD TRANSAKSI --}}
        <div class="divide-y divide-slate-100 md:hidden">
            @forelse ($transactions as $transaction)
                @php
                    $statusClass = match ($transaction->status) {
                        'PAID', 'COMPLETED', 'ORDER_COMPLETED' => 'bg-emerald-100 text-emerald-700',
                        'PENDING' => 'bg-amber-100 text-amber-700',
                        'CANCELLED', 'ORDER_CANCELLED', 'EXPIRED' => 'bg-red-100 text-red-700',
                        default => 'bg-slate-100 text-slate-700',
                    };
                @endphp

                <div class="space-y-3 p-3 sm:p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="break-words text-sm font-bold text-slate-800">
                                {{ $transaction->invoice_number }}
                            </p>
                            <p class="mt-1 text-[11px] text-slate-500">
                                {{ $transaction->transaction_date
                                    ? $transaction->transaction_date->copy()->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y, H:i')
                                    : '-' }}
                            </p>
                        </div>

                        <span class="inline-flex shrink-0 rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $statusClass }}">
                            {{ $transaction->status ?? 'PENDING' }}
                        </span>
                    </div>

                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="text-xs font-semibold text-slate-800">
                            {{ $transaction->customer?->name ?? $transaction->shipping_name ?? 'Pelanggan Booth' }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-slate-500">
                            {{ $transaction->customer?->phone ?? $transaction->shipping_phone ?? '-' }}
                        </p>

                        <div class="mt-3 space-y-2 border-t border-slate-200 pt-2">
                            @if (str_starts_with($transaction->invoice_number ?? '', 'BOOTH-LEGACY-'))
    @php
        $legacyGroups = [
            'Stadium Home, Away & Third' => ['qty' => 0, 'items' => 0],
            'Supporter Home, Away & Third' => ['qty' => 0, 'items' => 0],
        ];

        foreach ($transaction->items as $item) {
            $name = $item->productVariant?->product?->name ?? '';
            $group = str_contains($name, 'Supporter')
                ? 'Supporter Home, Away & Third'
                : 'Stadium Home, Away & Third';

            $legacyGroups[$group]['qty'] += (int) $item->qty;
            $legacyGroups[$group]['items']++;
        }
    @endphp

    @foreach ($legacyGroups as $groupName => $group)
        @if ($group['items'] > 0)
            <div class="mb-2">
                <p class="font-medium text-slate-700">{{ $groupName }}</p>
                <p class="text-xs text-slate-500">
                    {{ $group['qty'] }} pcs | {{ $group['items'] }} jenis produk
                </p>
            </div>
        @endif
    @endforeach

    <p class="text-xs text-slate-500">
        Total {{ $transaction->items->count() }} jenis produk | {{ $transaction->items->sum('qty') }} pcs
    </p>
@else
    @foreach ($transaction->items as $item)
        <div class="mb-2 last:mb-0">
            <p class="font-medium text-slate-700">
                {{ $item->productVariant?->product?->name ?? 'Produk' }}
                @if ($item->productVariant?->size?->name)
                    ({{ $item->productVariant->size->name }})
                @endif
            </p>
            <p class="text-xs text-slate-500">
                {{ $item->qty }} x Rp {{ number_format((float) $item->price, 0, ',', '.') }}
            </p>
        </div>
    @endforeach
@endif
                        </div>
                    </div>

                    <div class="flex items-end justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[11px] text-slate-500">
                                {{ $transaction->payment_method ?? '-' }}
                            </p>
                            <p class="mt-1 text-base font-bold text-slate-900">
                                Rp {{ number_format((float) $transaction->total, 0, ',', '.') }}
                            </p>
                            @if ((float) $transaction->discount > 0)
                                <p class="mt-0.5 text-[11px] text-emerald-600">
                                    Diskon Rp {{ number_format((float) $transaction->discount, 0, ',', '.') }}
                                </p>
                            @endif
                        </div>

                        <a href="{{ route('admin.offline-transactions.show', $transaction->id) }}"
                           class="inline-flex min-h-9 shrink-0 items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-700">
                            <x-heroicon-o-eye class="h-4 w-4"/>
                            Lihat
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <x-heroicon-o-receipt-percent class="mx-auto h-9 w-9 text-slate-300"/>
                    <p class="mt-3 text-sm font-semibold text-slate-600">Belum ada transaksi offline</p>
                    <p class="mt-1 text-xs text-slate-400">Transaksi kasir booth akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

        {{-- DESKTOP: TABEL TRANSAKSI --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">Transaksi</th>
                        <th class="px-5 py-4">Pelanggan</th>
                        <th class="px-5 py-4">Item</th>
                        <th class="px-5 py-4">Pembayaran</th>
                        <th class="px-5 py-4 text-center">Total</th>
                        <th class="px-5 py-4 text-center">Status</th>
                        <th class="px-5 py-4 text-center">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($transactions as $transaction)
                        <tr class="transition hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-semibold text-slate-800">{{ $transaction->invoice_number }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $transaction->transaction_date
                                        ? $transaction->transaction_date->copy()->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y, H:i')
                                        : '-' }}
                                </p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-800">
                                    {{ $transaction->customer?->name ?? $transaction->shipping_name ?? 'Pelanggan Booth' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $transaction->customer?->phone ?? $transaction->shipping_phone ?? '-' }}
                                </p>
                            </td>
                            <td class="min-w-48 px-5 py-4">
                                @if (str_starts_with($transaction->invoice_number ?? '', 'BOOTH-LEGACY-'))
    @php
        $legacyGroups = [
            'Stadium Home, Away & Third' => ['qty' => 0, 'items' => 0],
            'Supporter Home, Away & Third' => ['qty' => 0, 'items' => 0],
        ];

        foreach ($transaction->items as $item) {
            $name = $item->productVariant?->product?->name ?? '';
            $group = str_contains($name, 'Supporter')
                ? 'Supporter Home, Away & Third'
                : 'Stadium Home, Away & Third';

            $legacyGroups[$group]['qty'] += (int) $item->qty;
            $legacyGroups[$group]['items']++;
        }
    @endphp

    @foreach ($legacyGroups as $groupName => $group)
        @if ($group['items'] > 0)
            <div class="mb-2">
                <p class="font-medium text-slate-700">{{ $groupName }}</p>
                <p class="text-xs text-slate-500">
                    {{ $group['qty'] }} pcs | {{ $group['items'] }} jenis produk
                </p>
            </div>
        @endif
    @endforeach

    <p class="text-xs text-slate-500">
        Total {{ $transaction->items->count() }} jenis produk | {{ $transaction->items->sum('qty') }} pcs
    </p>
@else
    @foreach ($transaction->items as $item)
        <div class="mb-2 last:mb-0">
            <p class="font-medium text-slate-700">
                {{ $item->productVariant?->product?->name ?? 'Produk' }}
                @if ($item->productVariant?->size?->name)
                    ({{ $item->productVariant->size->name }})
                @endif
            </p>
            <p class="text-xs text-slate-500">
                {{ $item->qty }} x Rp {{ number_format((float) $item->price, 0, ',', '.') }}
            </p>
        </div>
    @endforeach
@endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                {{ $transaction->payment_method ?? '-' }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-center font-bold text-slate-900">
                                Rp {{ number_format((float) $transaction->total, 0, ',', '.') }}
                                @if ((float) $transaction->discount > 0)
                                    <p class="mt-1 text-xs font-normal text-emerald-600">
                                        Diskon Rp {{ number_format((float) $transaction->discount, 0, ',', '.') }}
                                    </p>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                @php
                                    $statusClass = match ($transaction->status) {
                                        'PAID', 'COMPLETED', 'ORDER_COMPLETED' => 'bg-emerald-100 text-emerald-700',
                                        'PENDING' => 'bg-amber-100 text-amber-700',
                                        'CANCELLED', 'ORDER_CANCELLED', 'EXPIRED' => 'bg-red-100 text-red-700',
                                        default => 'bg-slate-100 text-slate-700',
                                    };
                                @endphp
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                    {{ $transaction->status ?? 'PENDING' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <a href="{{ route('admin.offline-transactions.show', $transaction->id) }}"
                                   class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-700">
                                    <x-heroicon-o-eye class="h-4 w-4"/>
                                    Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center">
                                <x-heroicon-o-receipt-percent class="mx-auto h-10 w-10 text-slate-300"/>
                                <p class="mt-3 font-semibold text-slate-600">Belum ada transaksi offline</p>
                                <p class="mt-1 text-sm text-slate-400">
                                    Transaksi dari kasir booth akan muncul di halaman ini.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- ================= PAGINATION ================= --}}
        <div class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 px-4 py-4 text-center sm:px-6 sm:py-5 md:flex-row md:text-left">
            <p class="text-xs font-medium text-slate-500 sm:text-sm">
                Menampilkan
                <span class="font-semibold text-slate-900">{{ $transactions->firstItem() ?? 0 }}</span>
                sampai
                <span class="font-semibold text-slate-900">{{ $transactions->lastItem() ?? 0 }}</span>
                dari
                <span class="font-semibold text-slate-900">{{ $transactions->total() }}</span>
                transaksi
            </p>

            <x-admin.pagination :paginator="$transactions"/>
        </div>
    </div>
</div>
@endsection

