@extends('admin.layouts.app')

@section('title','Laporan Penjualan')
@section('page-title','Laporan Penjualan')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- HEADER + FILTER --}}
    <div class="flex flex-col gap-7 sm:gap-8">

        {{-- HEADER --}}
        <div class="shrink-0 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl md:text-3xl">
                    Laporan Penjualan
                </h1>
                <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">
                    Analisis kinerja penjualan dan pendapatan berdasarkan transaksi.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:flex sm:gap-3">
                <a
                    href="{{ route('admin.sales-reports.print',request()->query()) }}"
                    target="_blank"
                    class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 active:scale-[0.98] sm:h-[50px] sm:w-auto sm:gap-2 sm:px-5 sm:text-sm"
                >
                    <x-heroicon-o-printer class="h-4 w-4 shrink-0 sm:h-5 sm:w-5"/>
                    <span class="truncate">Cetak Laporan</span>
                </a>

                <a
                    href="{{ route('admin.sales-reports.export', request()->query()) }}"
                    class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-xl bg-[#AE7C18] px-3 text-xs font-semibold text-white shadow-md shadow-[#AE7C18]/20 transition hover:bg-[#96690F] active:scale-[0.98] sm:h-[50px] sm:w-auto sm:gap-2 sm:px-6 sm:text-sm"
                >
                    <x-heroicon-o-arrow-down-tray class="h-4 w-4 shrink-0 sm:h-5 sm:w-5"/>
                    <span class="truncate">Ekspor Laporan</span>
                </a>
            </div>

        </div>

        {{-- FILTER --}}
        <form
            method="GET"
            action="{{ route('admin.sales-reports') }}"
            class="rounded-2xl border border-slate-200/80 bg-white p-3.5 shadow-sm sm:p-8"
        >

            {{-- FILTER HEADER --}}
            <div class="mb-4 flex flex-col gap-1 border-b border-slate-100 pb-3 sm:mb-5 sm:flex-row sm:items-center sm:justify-between sm:pb-4">

                <div class="flex items-center gap-2.5">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18] sm:h-9 sm:w-9">
                        <x-heroicon-o-funnel class="h-4 w-4 sm:h-5 sm:w-5"/>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold text-slate-800 sm:text-lg">
                            Filter Data
                        </h3>

                        <p class="text-[11px] text-slate-500 sm:hidden">
                            Filter berdasarkan periode laporan
                        </p>
                    </div>
                </div>

                <span class="hidden text-xs text-slate-400 sm:inline-block">
                    Filter berdasarkan periode laporan
                </span>

            </div>

            {{-- FILTER CONTENT --}}
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">

                {{-- DARI TANGGAL --}}
                <div class="col-span-1 lg:col-span-3">
                    <label class="mb-1 block text-[11px] font-semibold text-slate-600 sm:text-xs">
                        Dari Tanggal
                    </label>

                    <input
                        type="date"
                        name="start_date"
                        value="{{ request('start_date') }}"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 text-xs font-medium text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-11 sm:px-3 sm:text-sm"
                    >
                </div>

                {{-- SAMPAI TANGGAL --}}
                <div class="col-span-1 lg:col-span-3">
                    <label class="mb-1 block text-[11px] font-semibold text-slate-600 sm:text-xs">
                        Sampai Tanggal
                    </label>

                    <input
                        type="date"
                        name="end_date"
                        value="{{ request('end_date') }}"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 text-xs font-medium text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-11 sm:px-3 sm:text-sm"
                    >
                </div>

                {{-- BULAN --}}
                <div class="col-span-1 lg:col-span-2">
                    <label class="mb-1 block text-[11px] font-semibold text-slate-600 sm:text-xs">
                        Bulan
                    </label>

                    <select
                        name="month"
                        onchange="this.form.submit()"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 text-xs font-medium text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-11 sm:px-3 sm:text-sm"
                    >
                        <option value="">Semua Bulan</option>
                        <option value="1" @selected(request('month') == 1)>Januari</option>
                        <option value="2" @selected(request('month') == 2)>Februari</option>
                        <option value="3" @selected(request('month') == 3)>Maret</option>
                        <option value="4" @selected(request('month') == 4)>April</option>
                        <option value="5" @selected(request('month') == 5)>Mei</option>
                        <option value="6" @selected(request('month') == 6)>Juni</option>
                        <option value="7" @selected(request('month') == 7)>Juli</option>
                        <option value="8" @selected(request('month') == 8)>Agustus</option>
                        <option value="9" @selected(request('month') == 9)>September</option>
                        <option value="10" @selected(request('month') == 10)>Oktober</option>
                        <option value="11" @selected(request('month') == 11)>November</option>
                        <option value="12" @selected(request('month') == 12)>Desember</option>
                    </select>
                </div>

                {{-- TAHUN --}}
                <div class="col-span-1 lg:col-span-2">
                    <label class="mb-1 block text-[11px] font-semibold text-slate-600 sm:text-xs">
                        Tahun
                    </label>

                    <select
                        name="year"
                        onchange="this.form.submit()"
                        class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 text-xs font-medium text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-11 sm:px-3 sm:text-sm"
                    >
                        <option value="">Semua Tahun</option>

                        @foreach($years as $year)
                            <option
                                value="{{ $year }}"
                                @selected((string)request('year') === (string)$year)
                            >
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- ACTION --}}
                <div class="col-span-2 mt-1 sm:mt-0 lg:col-span-2">
                    <div class="flex items-center gap-2">

                        <button
                            type="submit"
                            class="inline-flex h-10 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#AE7C18] px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-[#96690F] active:scale-[0.98] sm:h-11 sm:text-sm"
                        >
                            <x-heroicon-o-magnifying-glass class="h-4 w-4"/>
                            <span>Filter</span>
                        </button>

                        <a
                            href="{{ route('admin.sales-reports') }}"
                            title="Atur Ulang Filter"
                            class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 transition hover:bg-slate-100 active:scale-[0.98] sm:h-11 sm:w-11"
                        >
                            <x-heroicon-o-arrow-path class="h-4 w-4"/>
                        </a>

                    </div>
                </div>

            </div>
        </form>

    </div>

    {{-- STATISTIK --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-6 xl:grid-cols-4">
        {{-- Total Pendapatan --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 sm:p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between gap-1">
                <div>
                    <p class="text-[11px] font-medium text-slate-500 sm:text-sm">Total Pendapatan</p>
                    <h3 class="mt-1 text-sm sm:text-2xl font-bold tracking-tight text-slate-900 truncate">Rp{{ number_format($totalRevenue,0,',','.') }}</h3>
                </div>
                <div class="flex h-8 w-8 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-100">
                    <x-heroicon-o-banknotes class="h-4 w-4 text-emerald-600 sm:h-6 sm:w-6"/>
                </div>
            </div>
            <div class="mt-3 sm:mt-4 flex flex-wrap items-center gap-1.5 sm:gap-2">
                <span class="inline-flex items-center gap-0.5 sm:gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] sm:text-xs font-semibold text-emerald-700">
                    <x-heroicon-o-arrow-trending-up class="h-3 w-3 sm:h-3.5 sm:w-3.5"/>
                    {{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}%
                </span>
                <span class="text-[10px] sm:text-xs text-slate-400 truncate">vs lalu</span>
            </div>
        </div>

        {{-- Total Transaksi --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 sm:p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between gap-1">
                <div>
                    <p class="text-[11px] font-medium text-slate-500 sm:text-sm">Total Transaksi</p>
                    <h3 class="mt-1 text-sm sm:text-2xl font-bold tracking-tight text-slate-900 truncate">{{ number_format($totalTransactions,0,',','.') }}</h3>
                </div>
                <div class="flex h-8 w-8 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl bg-blue-100">
                    <x-heroicon-o-receipt-percent class="h-4 w-4 text-blue-600 sm:h-6 sm:w-6"/>
                </div>
            </div>
            <div class="mt-3 sm:mt-4 flex flex-wrap items-center gap-1.5 sm:gap-2">
                <span class="inline-flex items-center gap-0.5 sm:gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-[10px] sm:text-xs font-semibold text-blue-700">
                    <x-heroicon-o-arrow-trending-up class="h-3 w-3 sm:h-3.5 sm:w-3.5"/>
                    {{ $transactionGrowth >= 0 ? '+' : '' }}{{ $transactionGrowth }}%
                </span>
                <span class="text-[10px] sm:text-xs text-slate-400 truncate">vs lalu</span>
            </div>
        </div>

        {{-- Nilai Pesanan Rata-rata --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 sm:p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between gap-1">
                <div>
                    <p class="text-[11px] font-medium text-slate-500 sm:text-sm">Rata-rata Order</p>
                    <h3 class="mt-1 text-sm sm:text-2xl font-bold tracking-tight text-slate-900 truncate">Rp{{ number_format($averageOrderValue,0,',','.') }}</h3>
                </div>
                <div class="flex h-8 w-8 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl bg-violet-100">
                    <x-heroicon-o-chart-bar class="h-4 w-4 text-violet-600 sm:h-6 sm:w-6"/>
                </div>
            </div>
            <div class="mt-3 sm:mt-4 flex flex-wrap items-center gap-1.5 sm:gap-2">
                <span class="inline-flex items-center gap-0.5 sm:gap-1 rounded-full bg-violet-100 px-2 py-0.5 text-[10px] sm:text-xs font-semibold text-violet-700">
                    <x-heroicon-o-arrow-trending-up class="h-3 w-3 sm:h-3.5 sm:w-3.5"/>
                    {{ $averageGrowth >= 0 ? '+' : '' }}{{ $averageGrowth }}%
                </span>
                <span class="text-[10px] sm:text-xs text-slate-400 truncate">vs lalu</span>
            </div>
        </div>

        {{-- Produk Terjual --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 sm:p-6 shadow-sm flex flex-col justify-between">
            <div class="flex items-start justify-between gap-1">
                <div>
                    <p class="text-[11px] font-medium text-slate-500 sm:text-sm">Produk Terjual</p>
                    <h3 class="mt-1 text-sm sm:text-2xl font-bold tracking-tight text-slate-900 truncate">{{ number_format($totalProductsSold,0,',','.') }}</h3>
                </div>
                <div class="flex h-8 w-8 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100">
                    <x-heroicon-o-cube class="h-4 w-4 text-amber-600 sm:h-6 sm:w-6"/>
                </div>
            </div>
            <div class="mt-3 sm:mt-4 flex flex-wrap items-center gap-1.5 sm:gap-2">
                <span class="inline-flex items-center gap-0.5 sm:gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] sm:text-xs font-semibold text-amber-700">
                    <x-heroicon-o-arrow-trending-up class="h-3 w-3 sm:h-3.5 sm:w-3.5"/>
                    {{ $productGrowth >= 0 ? '+' : '' }}{{ $productGrowth }}%
                </span>
                <span class="text-[10px] sm:text-xs text-slate-400 truncate">vs lalu</span>
            </div>
        </div>
    </div>

    {{-- GRAFIK --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm sm:p-6">
        <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3 sm:pb-4">
            <div class="min-w-0">
                <h3 class="text-base font-semibold text-slate-900 sm:text-xl">Ringkasan Pendapatan</h3>
                <p class="mt-0.5 text-[11px] text-slate-500 sm:text-sm">Pendapatan berdasarkan transaksi berhasil.</p>
            </div>

            <span class="shrink-0 rounded-full bg-[#AE7C18]/10 px-2.5 py-1 text-[10px] font-semibold text-[#AE7C18] sm:px-3 sm:text-xs">
                {{ $monthlyRevenue->count() }} Periode
            </span>
        </div>

        @php
            $maxRevenue = max(1, $monthlyRevenue->max('revenue') ?? 1);
        @endphp

        <div
            x-data="{ activeBar: null }"
            @click.away="activeBar = null"
            class="relative"
        >
            {{-- AREA GRAFIK --}}
            <div class="overflow-x-auto overscroll-x-contain pt-8">
                <div class="mt-4 flex min-w-[420px] items-end gap-2 border-b border-slate-100 px-3 pb-4 pt-6 sm:min-w-0 sm:gap-4 sm:px-2">
                    @forelse($monthlyRevenue as $index => $month)
                        @php
                            $height = $month['revenue'] > 0
                                ? max(8, ($month['revenue'] / $maxRevenue) * 85)
                                : 2;
                        @endphp

                        <div class="flex h-[180px] min-w-[64px] flex-1 flex-col justify-end sm:h-[280px] sm:min-w-[70px]">
                            <div class="relative flex h-full items-end justify-center">
                                
                                {{-- TOOLTIP TIAP BATANG --}}
                                <div
                                    x-cloak
                                    x-show="activeBar === {{ $index }}"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="pointer-events-none absolute bottom-full mb-2 z-30 whitespace-nowrap rounded-lg bg-slate-900 px-2.5 py-1 text-[10px] font-semibold text-white shadow-lg sm:text-xs"
                                    style="bottom: {{ $height }}%;"
                                >
                                    Rp{{ number_format($month['revenue'], 0, ',', '.') }}
                                </div>

                                {{-- BATANG GRAFIK --}}
                                <div
                                    class="w-full max-w-[28px] cursor-pointer rounded-t-lg bg-[#AE7C18] transition-all hover:bg-[#96690F] sm:max-w-[42px]"
                                    :class="{ 'bg-[#96690F]': activeBar === {{ $index }} }"
                                    style="height:{{ $height }}%"
                                    @click="activeBar = (activeBar === {{ $index }} ? null : {{ $index }})"
                                ></div>
                            </div>

                            <span class="mt-2 block whitespace-nowrap text-center text-[10px] font-medium leading-tight text-slate-500 sm:text-xs">
                                {{ $month['label'] }}
                            </span>
                        </div>
                    @empty
                        <div class="flex h-[180px] w-full items-center justify-center text-xs text-slate-400 sm:h-[280px] sm:text-sm">
                            Belum ada data pendapatan.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-6">
        {{-- PRODUK --}}
        <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div>
                <div class="flex items-center justify-between border-b border-slate-200 p-3 sm:px-6 sm:py-5">
                    <div>
                        <h3 class="text-xs font-semibold text-slate-900 sm:text-xl">Penjualan per Produk</h3>
                        <p class="hidden text-xs text-slate-500 sm:block">Produk terlaris berdasarkan unit terjual.</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full bg-[#AE7C18]/10 px-2 py-0.5 text-[10px] font-semibold text-[#AE7C18] sm:px-3 sm:py-1 sm:text-xs">
                        {{ $topProducts->count() }} <span class="ml-1 hidden sm:inline">Produk</span><span class="sm:hidden">&nbsp;Produk</span>
                    </span>
                </div>

                {{-- Mobile View: Card List --}}
                <div class="divide-y divide-slate-100 sm:hidden">
                    @forelse($topProducts as $product)
                        <div class="p-2.5 space-y-1.5">
                            <div class="flex items-center justify-between gap-1">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#AE7C18]/10 text-[#AE7C18]">
                                        <x-heroicon-o-cube class="h-3 w-3"/>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-bold text-slate-900 truncate" title="{{ $product['name'] }}">{{ $product['name'] }}</p>
                                        <p class="text-[9px] text-slate-400">{{ number_format($product['units'],0,',','.') }} unit</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-slate-50 text-[10px]">
                                <span class="text-slate-400 text-[9px]">Pendapatan:</span>
                                <span class="font-bold text-slate-900 text-[10px]">Rp{{ number_format($product['revenue'],0,',','.') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-[10px] text-slate-400">Belum ada data.</div>
                    @endforelse
                </div>

                {{-- Desktop View: Table --}}
                <div class="hidden overflow-x-auto sm:block">
                    <table class="min-w-full">
                        <thead class="border-b border-slate-200 bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="whitespace-nowrap px-4 py-3">Produk</th>
                                <th class="whitespace-nowrap px-4 py-3 text-center">Unit</th>
                                <th class="whitespace-nowrap px-4 py-3 text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($topProducts as $product)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#AE7C18]/10">
                                                <x-heroicon-o-cube class="h-4 w-4 text-[#AE7C18]"/>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-slate-900 truncate max-w-[120px] lg:max-w-none">{{ $product['name'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-center text-xs font-semibold text-slate-700">{{ number_format($product['units'],0,',','.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-xs font-semibold text-slate-900">Rp{{ number_format($product['revenue'],0,',','.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-xs text-slate-400">Belum ada data produk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border-t border-slate-200 p-2.5 sm:px-6 sm:py-4">
                <p class="text-[10px] text-slate-500 sm:text-xs">{{ $topProducts->count() }} produk teratas</p>
            </div>
        </div>

        {{-- KATEGORI --}}
        <div class="flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div>
                <div class="flex items-center justify-between border-b border-slate-200 p-3.5 sm:px-6 sm:py-5">
                    <div>
                        <h3 class="text-xs font-semibold text-slate-900 sm:text-xl">Penjualan per Kategori</h3>
                        <p class="hidden text-xs text-slate-500 sm:block">Kinerja penjualan berdasarkan kategori produk.</p>
                    </div>
                    <span class="inline-flex w-fit rounded-full bg-[#AE7C18]/10 px-2 py-0.5 text-[10px] font-semibold text-[#AE7C18] sm:px-3 sm:py-1 sm:text-xs">
                        {{ $salesCategories->count() }} <span class="ml-1 hidden sm:inline">Kategori</span><span class="sm:hidden">&nbsp;Kategori</span>
                    </span>
                </div>

                {{-- Mobile View: Card List --}}
                <div class="divide-y divide-slate-100 sm:hidden">
                    @forelse($salesCategories as $category)
                        <div class="p-2.5 space-y-1.5">
                            <div class="flex items-center justify-between gap-1">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#AE7C18]/10 text-[#AE7C18]">
                                        <x-heroicon-o-squares-2x2 class="h-3 w-3"/>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-bold text-slate-900 truncate" title="{{ $category['name'] }}">{{ $category['name'] }}</p>
                                        <p class="text-[9px] text-slate-400">{{ number_format($category['products'],0,',','.') }} produk</p>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between pt-1 border-t border-slate-50 text-[10px]">
                                <span class="text-slate-400 text-[9px]">Pendapatan:</span>
                                <span class="font-bold text-slate-900 text-[10px]">Rp{{ number_format($category['revenue'],0,',','.') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-[10px] text-slate-400">Belum ada data.</div>
                    @endforelse
                </div>

                {{-- Desktop View: Table --}}
                <div class="hidden overflow-x-auto sm:block">
                    <table class="min-w-full">
                        <thead class="border-b border-slate-200 bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <th class="whitespace-nowrap px-4 py-3">Kategori</th>
                                <th class="whitespace-nowrap px-4 py-3 text-center">Produk</th>
                                <th class="whitespace-nowrap px-4 py-3 text-right">Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($salesCategories as $category)
                                <tr class="transition hover:bg-slate-50">
                                    <td class="whitespace-nowrap px-4 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#AE7C18]/10">
                                                <x-heroicon-o-squares-2x2 class="h-4 w-4 text-[#AE7C18]"/>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-slate-900 truncate max-w-[120px] lg:max-w-none">{{ $category['name'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-center text-xs font-semibold text-slate-700">{{ number_format($category['products'],0,',','.') }}</td>
                                    <td class="whitespace-nowrap px-4 py-4 text-right text-xs font-semibold text-slate-900">Rp{{ number_format($category['revenue'],0,',','.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-xs text-slate-400">Belum ada data kategori.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="border-t border-slate-200 p-2.5 sm:px-6 sm:py-4">
                <p class="text-[10px] text-slate-500 sm:text-xs">{{ $salesCategories->count() }} kategori teratas</p>
            </div>
        </div>
    </div>

    {{-- METODE PEMBAYARAN --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 p-3.5 sm:px-6 sm:py-5">
            <div>
                <h3 class="text-base sm:text-xl font-semibold text-slate-900">Metode Pembayaran</h3>
                <p class="text-[11px] sm:text-sm text-slate-500">Distribusi transaksi berdasarkan metode pembayaran.</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-[#AE7C18]/10 px-2.5 py-0.5 sm:px-3 sm:py-1 text-[11px] sm:text-xs font-semibold text-[#AE7C18]">
                {{ $paymentMethods->count() }} Metode
            </span>
        </div>

        <div class="grid grid-cols-2 gap-3 p-3.5 sm:grid-cols-2 sm:p-6 xl:grid-cols-4">
            @forelse($paymentMethods as $payment)
                <div class="rounded-xl sm:rounded-2xl border border-slate-200 p-3 sm:p-5 transition hover:border-slate-300 hover:shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-900 sm:text-base truncate">{{ $payment['name'] }}</p>
                            <p class="mt-0.5 text-[10px] sm:text-xs text-slate-400">{{ number_format($payment['transactions'],0,',','.') }} tx</p>
                        </div>

                        @if($payment['icon'] === 'banknotes')
                            <div class="flex h-7 w-7 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-emerald-100">
                                <x-heroicon-o-banknotes class="h-4 w-4 sm:h-5 sm:w-5 text-emerald-600"/>
                            </div>
                        @elseif($payment['icon'] === 'qr-code')
                            <div class="flex h-7 w-7 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-violet-100">
                                <x-heroicon-o-qr-code class="h-4 w-4 sm:h-5 sm:w-5 text-violet-600"/>
                            </div>
                        @elseif($payment['icon'] === 'building-library')
                            <div class="flex h-7 w-7 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-blue-100">
                                <x-heroicon-o-building-library class="h-4 w-4 sm:h-5 sm:w-5 text-blue-600"/>
                            </div>
                        @else
                            <div class="flex h-7 w-7 sm:h-9 sm:w-9 shrink-0 items-center justify-center rounded-lg sm:rounded-xl bg-amber-100">
                                <x-heroicon-o-credit-card class="h-4 w-4 sm:h-5 sm:w-5 text-amber-600"/>
                            </div>
                        @endif
                    </div>

                    <div class="mt-3 sm:mt-4">
                        <p class="text-[10px] sm:text-xs font-medium text-slate-400">Pendapatan</p>
                        <p class="mt-0.5 text-xs sm:text-base font-bold text-slate-900 truncate">Rp{{ number_format($payment['revenue'],0,',','.') }}</p>
                    </div>

                    <div class="mt-3 sm:mt-4">
                        <div class="mb-1 sm:mb-2 flex items-center justify-between text-[10px] sm:text-xs">
                            <span class="text-slate-500">Share</span>
                            <span class="font-semibold text-slate-700">{{ $payment['percentage'] }}%</span>
                        </div>
                        <div class="h-1.5 sm:h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-[#AE7C18]" style="width:{{ min(100,$payment['percentage']) }}%"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-6 sm:py-10 text-center text-xs sm:text-sm text-slate-400 col-span-2 xl:col-span-4">Belum ada data pembayaran.</div>
            @endforelse
        </div>
    </div>

    {{-- TRANSAKSI --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 p-3.5 sm:px-6 sm:py-5">
            <div>
                <h3 class="text-base sm:text-xl font-semibold text-slate-900">Laporan Transaksi</h3>
                <p class="text-[11px] sm:text-sm text-slate-500">Catatan transaksi berhasil periode terpilih.</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-[#AE7C18]/10 px-2.5 py-0.5 sm:px-3 sm:py-1 text-[11px] sm:text-xs font-semibold text-[#AE7C18]">
                {{ $transactions->count() }} Transaksi
            </span>
        </div>

        {{-- Mobile View: Card List untuk Transaksi --}}
        <div class="divide-y divide-slate-100 sm:hidden">
            @forelse($transactions as $transaction)
                <div class="p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-900 font-mono">{{ $transaction->invoice_number }}</span>
                        <x-admin.badge-status :status="$transaction->status"/>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div class="bg-slate-50/80 p-2 rounded-lg border border-slate-100">
                            <span class="text-slate-400 block text-[10px]">Tanggal</span>
                            <span class="font-medium text-slate-700 block truncate">{{ $transaction->transaction_date?->format('d M Y, H:i') ?? '-' }}</span>
                        </div>
                        <div class="bg-slate-50/80 p-2 rounded-lg border border-slate-100">
                            <span class="text-slate-400 block text-[10px]">Pelanggan</span>
                            <span class="font-medium text-slate-700 block truncate">{{ $transaction->customer?->name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                        <div>
                            @php
                                $payment = strtoupper($transaction->payment_method ?? '');
                            @endphp
                            @if($payment === 'CASH')
                                <span class="inline-flex rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">CASH</span>
                            @elseif($payment === 'QRIS')
                                <span class="inline-flex rounded-md bg-violet-100 px-2 py-0.5 text-[10px] font-semibold text-violet-700">QRIS</span>
                            @elseif(in_array($payment,['TRANSFER','TRANSFER_BANK','BANK_TRANSFER']))
                                <span class="inline-flex rounded-md bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">Transfer</span>
                            @elseif($payment === 'EDC')
                                <span class="inline-flex rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">EDC</span>
                            @else
                                <span class="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700">{{ $transaction->payment_method ?? '-' }}</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-slate-400">Total: </span>
                            <span class="text-xs font-bold text-slate-900">Rp{{ number_format($transaction->total,0,',','.') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 mb-2">
                            <x-heroicon-o-receipt-percent class="h-5 w-5 text-slate-400"/>
                        </div>
                        <p class="text-xs text-slate-400">Belum ada data transaksi.</p>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Desktop View: Table --}}
        <div class="hidden overflow-x-auto sm:block">
            <table class="min-w-full">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="whitespace-nowrap px-6 py-4">Invoice</th>
                        <th class="whitespace-nowrap px-6 py-4">Tanggal</th>
                        <th class="whitespace-nowrap px-6 py-4">Pelanggan</th>
                        <th class="whitespace-nowrap px-6 py-4 text-center">Pembayaran</th>
                        <th class="whitespace-nowrap px-6 py-4 text-center">Status</th>
                        <th class="whitespace-nowrap px-6 py-4 text-right">Total</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200">
                    @forelse($transactions as $transaction)
                        <tr class="transition hover:bg-slate-50">
                            <td class="whitespace-nowrap px-6 py-5">
                                <span class="text-sm font-semibold text-slate-900">{{ $transaction->invoice_number }}</span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-5">
                                <span class="text-sm text-slate-500">
                                    {{ $transaction->transaction_date?->format('d M Y, H:i') ?? '-' }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-5">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $transaction->customer?->name ?? '-' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">Pelanggan</p>
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-6 py-5 text-center">
                                @php
                                    $payment = strtoupper($transaction->payment_method ?? '');
                                @endphp

                                @if($payment === 'CASH')
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">CASH</span>
                                @elseif($payment === 'QRIS')
                                    <span class="inline-flex rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700">QRIS</span>
                                @elseif(in_array($payment,['TRANSFER','TRANSFER_BANK','BANK_TRANSFER']))
                                    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">Transfer Bank</span>
                                @elseif($payment === 'EDC')
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">EDC</span>
                                @else
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ $transaction->payment_method ?? '-' }}</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-6 py-5 text-center">
                                <x-admin.badge-status :status="$transaction->status"/>
                            </td>

                            <td class="whitespace-nowrap px-6 py-5 text-right">
                                <span class="text-sm font-semibold text-slate-900">Rp{{ number_format($transaction->total,0,',','.') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 mb-2">
                                        <x-heroicon-o-receipt-percent class="h-6 w-6 text-slate-400"/>
                                    </div>
                                    <p class="text-sm text-slate-400">Belum ada data transaksi.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection