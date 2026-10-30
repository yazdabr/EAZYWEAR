@extends('admin.layouts.app')

@section('title', 'Transaksi NNS')
@section('page-title', 'Transaksi NNS')

@section('content')

@php
    // DATA CONTOH UNTUK PREVIEW UI
    $nnsTransactions = [

    ];

    $statusClass = [
        'PAID' => 'bg-emerald-100 text-emerald-700',
        'COMPLETED' => 'bg-emerald-100 text-emerald-700',
        'PENDING' => 'bg-amber-100 text-amber-700',
        'CANCELLED' => 'bg-red-100 text-red-700',
        'EXPIRED' => 'bg-red-100 text-red-700',
    ];
@endphp

<div class="space-y-5 sm:space-y-8">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 sm:text-3xl">
                Transaksi NNS
            </h2>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Kelola transaksi jersey dengan layanan Number & Name Set.
            </p>
        </div>
    </div>

    {{-- FILTER FORM --}}
    <form method="GET" action="#" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:rounded-3xl sm:p-6">
        <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3 sm:mb-5 sm:pb-4">
            <div>
                <h3 class="text-base font-semibold text-slate-800 sm:text-xl">Filter Data</h3>
            </div>
            <span class="text-[11px] text-slate-400 sm:text-xs">Filter pelanggan & kriteria</span>
        </div>

        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-12 sm:gap-4">
            {{-- PENCARIAN --}}
            <div class="md:col-span-2 lg:col-span-4">
                <label class="mb-1 block text-xs font-medium text-slate-600 sm:mb-1.5">
                    Cari Nama / INV Pelanggan
                </label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari Nama / INV Pelanggan..."
                    class="h-[42px] w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-regular text-slate-700 transition-all duration-200 placeholder:text-slate-400 focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-[50px] sm:px-4 sm:text-sm"
                >
            </div>

            {{-- FILTER DI SISI KANAN --}}
            <div class="md:col-span-2 lg:col-span-8">
                <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-row sm:items-end sm:justify-end sm:gap-4">
                    {{-- STATUS --}}
                    <div class="col-span-1 w-full sm:w-52">
                        <label class="mb-1 block text-xs font-medium text-slate-600 sm:mb-1.5">Status</label>
                        <select
                            name="status"
                            onchange="this.form.submit()"
                            class="h-[42px] w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition-all duration-200 focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-[50px] sm:px-4 sm:text-sm"
                        >
                            <option value="">Semua Status</option>
                            @foreach (['PENDING', 'PAID', 'EXPIRED', 'CANCELLED', 'COMPLETED'] as $status)
                                <option value="{{ $status }}" @selected(request('status') === $status)>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- BULAN --}}
                    <div class="col-span-1 w-full sm:w-52">
                        <label class="mb-1 block text-xs font-medium text-slate-600 sm:mb-1.5">Bulan</label>
                        <select
                            name="month"
                            onchange="this.form.submit()"
                            class="h-[42px] w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition-all duration-200 focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-[50px] sm:px-4 sm:text-sm"
                        >
                            <option value="">Semua Bulan</option>
                            @foreach (range(1, 12) as $month)
                                <option value="{{ $month }}" @selected((string) request('month') === (string) $month)>
                                    {{ \Carbon\Carbon::create()->month($month)->locale('id')->translatedFormat('F') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- TAHUN --}}
                    <div class="col-span-1 w-full sm:w-40">
                        <label class="mb-1 block text-xs font-medium text-slate-600 sm:mb-1.5">Tahun</label>
                        <select
                            name="year"
                            onchange="this.form.submit()"
                            class="h-[42px] w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition-all duration-200 focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-[50px] sm:px-4 sm:text-sm"
                        >
                            <option value="">Semua Tahun</option>
                            @for ($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- RESET --}}
                    <div class="col-span-1 grid grid-cols-1 gap-2 sm:flex sm:items-center sm:justify-end">
                        <a
                            href="{{ route('admin.stadium-transactions.index') }}"
                            title="Atur Ulang Filter"
                            class="inline-flex h-[42px] w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 text-slate-600 transition-all duration-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 active:scale-[0.98] sm:h-[50px] sm:w-[50px] sm:px-0"
                        >
                            <x-heroicon-o-arrow-path class="h-4 w-4"/>
                            <span class="text-xs font-medium sm:hidden">Reset Filter</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- STATISTIK --}}
    <div class="grid gap-3 sm:gap-6 md:grid-cols-2 xl:grid-cols-4">

        <x-admin.stat-card
            title="Total Transaksi NNS"
            value="{{ number_format(count($nnsTransactions), 0, ',', '.') }}"
            growth="Data contoh"
            :positive="true"
            :neutral="true"
        >
            <x-slot:icon>
                <x-heroicon-o-receipt-percent class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Pesanan Pending"
            value="{{ number_format(collect($nnsTransactions)->where('status', 'PENDING')->count(), 0, ',', '.') }}"
            growth="Menunggu pembayaran"
            :positive="false"
            :neutral="true"
        >
            <x-slot:icon>
                <x-heroicon-o-clock class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Total Pendapatan"
            value="Rp {{ number_format(collect($nnsTransactions)->whereIn('status', ['PAID', 'COMPLETED'])->sum('total'), 0, ',', '.') }}"
            growth="Data contoh"
            :positive="true"
            :neutral="true"
        >
            <x-slot:icon>
                <x-heroicon-o-banknotes class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Total Jersey NNS"
            value="{{ number_format(collect($nnsTransactions)->sum('qty'), 0, ',', '.') }}"
            growth="Jumlah pcs"
            :positive="true"
            :neutral="true"
        >
            <x-slot:icon>
                <x-heroicon-o-tag class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

    </div>

    {{-- TABEL TRANSAKSI --}}
    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm sm:rounded-2xl">

        {{-- MOBILE: CARD --}}
        <div class="divide-y divide-slate-100 md:hidden">
            @forelse ($nnsTransactions as $transaction)
                <div class="space-y-3 p-4">

                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="break-all text-sm font-bold text-slate-800">
                                {{ $transaction['invoice'] }}
                            </p>
                            <p class="mt-1 text-[11px] text-slate-500">
                                {{ $transaction['date'] }}
                            </p>
                        </div>

                        <span class="inline-flex shrink-0 rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $statusClass[$transaction['status']] ?? 'bg-slate-100 text-slate-700' }}">
                            {{ $transaction['status'] }}
                        </span>
                    </div>

                    <div class="rounded-xl bg-slate-50 p-3">
                        <p class="text-sm font-semibold text-slate-800">
                            {{ $transaction['customer'] }}
                        </p>
                        <p class="mt-0.5 text-[11px] text-slate-500">
                            {{ $transaction['phone'] }}
                        </p>

                        <div class="mt-3 border-t border-slate-200 pt-3">
                            <p class="text-xs font-semibold text-slate-800">
                                {{ $transaction['product'] }}
                            </p>
                            <p class="mt-1 text-[11px] leading-relaxed text-slate-500">
                                Ukuran {{ $transaction['size'] }}
                                · {{ $transaction['name'] }} {{ $transaction['number'] }}
                                · NNS
                                · {{ $transaction['qty'] }} pcs
                            </p>
                        </div>
                    </div>

                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <p class="text-[11px] text-slate-500">
                                {{ $transaction['payment_method'] ?? '-' }}
                            </p>
                            <p class="mt-1 text-base font-bold text-slate-900">
                                Rp {{ number_format($transaction['total'], 0, ',', '.') }}
                            </p>
                        </div>

                        <a
                            href="{{ route('admin.nns-transactions.show') }}"
                            class="inline-flex min-h-9 shrink-0 items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-700"
                        >
                            <x-heroicon-o-eye class="h-4 w-4"/>
                            Lihat
                        </a>
                    </div>

                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <x-heroicon-o-receipt-percent class="mx-auto h-9 w-9 text-slate-300"/>
                    <p class="mt-3 text-sm font-semibold text-slate-600">
                        Belum ada transaksi NNS
                    </p>
                    <p class="mt-1 text-xs text-slate-400">
                        Transaksi Number & Name Set akan muncul di sini.
                    </p>
                </div>
            @endforelse
        </div>
        {{-- DESKTOP: TABLE --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full divide-y divide-slate-100 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-4">INVOICE</th>
                        <th class="px-5 py-4">Pelanggan</th>
                        <th class="px-5 py-4">RINGKASAN PESANAN</th>
                        <th class="px-5 py-4 text-center">Total</th>
                        <th class="px-5 py-4 text-center">Pembayaran</th>
                        <th class="px-5 py-4 text-center">Status</th>
                        <th class="px-5 py-4 text-center">Detail</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($nnsTransactions as $transaction)
                        <tr class="transition hover:bg-slate-50/70">

                            {{-- INVOICE --}}
                            <td class="whitespace-nowrap px-5 py-4">
                                <p class="font-semibold text-slate-800">
                                    {{ $transaction['invoice'] }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $transaction['date'] }}
                                </p>
                            </td>

                            {{-- PELANGGAN --}}
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-800">
                                    {{ $transaction['customer'] }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $transaction['phone'] }}
                                </p>
                            </td>

                            {{-- RINGKASAN PESANAN --}}
                            <td class="min-w-[280px] px-5 py-4">
                                <p class="font-semibold text-slate-800">
                                    {{ $transaction['product'] }}
                                </p>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">
                                    Ukuran {{ $transaction['size'] }}
                                    · {{ $transaction['name'] }} {{ $transaction['number'] }}
                                    · NNS
                                    · {{ $transaction['qty'] }} pcs
                                </p>
                            </td>

                            {{-- TOTAL --}}
                            <td class="whitespace-nowrap px-5 py-4 text-center font-bold text-slate-900">
                                Rp {{ number_format($transaction['total'], 0, ',', '.') }}
                            </td>

                            {{-- PEMBAYARAN --}}
                            <td class="whitespace-nowrap px-5 py-4 text-center text-slate-600">
                                {{ $transaction['payment_method'] ?? '-' }}
                            </td>

                            {{-- STATUS --}}
                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass[$transaction['status']] ?? 'bg-slate-100 text-slate-700' }}">
                                    {{ $transaction['status'] }}
                                </span>
                            </td>

                            {{-- DETAIL --}}
                            <td class="whitespace-nowrap px-5 py-4 text-center">
                                <a
                                    href="{{ route('admin.nns-transactions.show') }}"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-amber-400 hover:text-amber-700"
                                >
                                    <x-heroicon-o-eye class="h-4 w-4"/>
                                    Lihat
                                </a>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-14 text-center">
                                <x-heroicon-o-receipt-percent class="mx-auto h-10 w-10 text-slate-300"/>
                                <p class="mt-3 font-semibold text-slate-600">
                                    Belum ada transaksi NNS
                                </p>
                                <p class="mt-1 text-sm text-slate-400">
                                    Transaksi Number & Name Set akan muncul di sini.
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
                <span class="font-semibold text-slate-900">1</span>
                sampai
                <span class="font-semibold text-slate-900">2</span>
                dari
                <span class="font-semibold text-slate-900">2</span>
                transaksi
            </p>

            {{-- <x-admin.pagination :paginator="$transactions"/> --}}
        </div>

    </div>
</div>

@endsection


