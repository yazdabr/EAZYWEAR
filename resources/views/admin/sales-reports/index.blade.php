@extends('admin.layouts.app')

@section('title','Laporan Penjualan')
@section('page-title','Laporan Penjualan')

@section('content')
<div class="space-y-4 sm:space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Laporan Penjualan</h1>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">Analisis kinerja penjualan dan pendapatan berdasarkan transaksi.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <a
                href="{{ route('admin.sales-reports.print',request()->query()) }}"
                target="_blank"
                class="inline-flex h-[46px] w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:h-[50px] sm:w-auto"
            >
                <x-heroicon-o-printer class="h-5 w-5"/>
                <span>Cetak Laporan</span>
            </a>
            <button type="button" @click="$dispatch('toast',{type:'info',title:'Ekspor Laporan',message:'Fitur ekspor dapat dihubungkan ke Excel/PDF.'})" class="inline-flex h-[46px] w-full items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] sm:h-[50px] sm:w-auto">
                <x-heroicon-o-arrow-down-tray class="h-5 w-5"/>
                <span>Ekspor Laporan</span>
            </button>
        </div>
    </div>

    {{-- INFO --}}
    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-4 shadow-sm sm:px-6 sm:py-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2.5">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-200/60 text-slate-500">
                    <x-heroicon-o-information-circle class="h-4 w-4"/>
                </div>
                <span class="text-xs font-medium text-slate-600 sm:text-sm">Gambaran laporan penjualan</span>
            </div>
            
            <div class="flex items-center justify-between rounded-xl bg-white border border-slate-200/60 px-3 py-2 sm:border-0 sm:bg-transparent sm:p-0">
                <span class="text-[11px] font-medium text-slate-400 sm:hidden">Periode:</span>
                <span class="text-xs font-semibold text-slate-700 sm:text-sm">
                    @if($startDate && $endDate)
                        {{ $startDate->format('d M Y') }} - {{ $endDate->format('d M Y') }}
                    @else
                        Semua data penjualan
                    @endif
                </span>
            </div>
        </div>
    </div>

    {{-- FILTER --}}
    <form method="GET" action="{{ route('admin.sales-reports') }}" class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:rounded-3xl sm:p-6">
        <div class="mb-5 flex flex-col gap-1 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]">
                    <x-heroicon-o-funnel class="h-5 w-5"/>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800 sm:text-lg">Filter Data</h3>
                    <p class="text-xs text-slate-500 sm:hidden">Filter berdasarkan periode laporan</p>
                </div>
            </div>
            <span class="hidden text-xs text-slate-400 sm:inline-block">Filter berdasarkan periode laporan</span>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
            <div class="lg:col-span-3">
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-xs font-medium text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:text-sm">
            </div>

            <div class="lg:col-span-3">
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-xs font-medium text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:text-sm">
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Bulan</label>
                <select
                    name="month"
                    onchange="this.form.submit()"
                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-xs font-medium text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:text-sm"
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

            <div class="lg:col-span-2">
                <label class="mb-1.5 block text-xs font-semibold text-slate-600">Tahun</label>
                <select
                    name="year"
                    onchange="this.form.submit()"
                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-xs font-medium text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:text-sm"
                >
                    <option value="">Semua Tahun</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" @selected((string)request('year') === (string)$year)>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 lg:col-span-2">
                <div class="flex items-center gap-2">
                    <button type="submit" class="inline-flex h-11 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#AE7C18] px-4 text-xs font-semibold text-white shadow-sm shadow-[#AE7C18]/30 transition hover:bg-[#96690F] sm:text-sm">
                        <x-heroicon-o-magnifying-glass class="h-4 w-4"/>
                        <span>Filter</span>
                    </button>
                    <a href="{{ route('admin.sales-reports') }}" title="Atur Ulang Filter" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 transition hover:bg-slate-100">
                        <x-heroicon-o-arrow-path class="h-4 w-4"/>
                    </a>
                </div>
            </div>
        </div>
    </form>

    {{-- STATISTIK --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:rounded-3xl sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 sm:text-sm">Total Pendapatan</p>
                    <h3 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:mt-2 sm:text-2xl">Rp{{ number_format($totalRevenue,0,',','.') }}</h3>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-banknotes class="h-5 w-5 text-emerald-600 sm:h-6 sm:w-6"/>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5"/>
                    {{ $revenueGrowth >= 0 ? '+' : '' }}{{ $revenueGrowth }}%
                </span>
                <span class="text-xs text-slate-400">dibandingkan periode sebelumnya</span>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:rounded-3xl sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 sm:text-sm">Total Transaksi</p>
                    <h3 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:mt-2 sm:text-2xl">{{ number_format($totalTransactions,0,',','.') }}</h3>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-receipt-percent class="h-5 w-5 text-blue-600 sm:h-6 sm:w-6"/>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5"/>
                    {{ $transactionGrowth >= 0 ? '+' : '' }}{{ $transactionGrowth }}%
                </span>
                <span class="text-xs text-slate-400">dibandingkan periode sebelumnya</span>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:rounded-3xl sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 sm:text-sm">Nilai Pesanan Rata-rata</p>
                    <h3 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:mt-2 sm:text-2xl">Rp{{ number_format($averageOrderValue,0,',','.') }}</h3>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-chart-bar class="h-5 w-5 text-violet-600 sm:h-6 sm:w-6"/>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700">
                    <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5"/>
                    {{ $averageGrowth >= 0 ? '+' : '' }}{{ $averageGrowth }}%
                </span>
                <span class="text-xs text-slate-400">dibandingkan periode sebelumnya</span>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:rounded-3xl sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 sm:text-sm">Produk Terjual</p>
                    <h3 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:mt-2 sm:text-2xl">{{ number_format($totalProductsSold,0,',','.') }}</h3>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-cube class="h-5 w-5 text-amber-600 sm:h-6 sm:w-6"/>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                    <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5"/>
                    {{ $productGrowth >= 0 ? '+' : '' }}{{ $productGrowth }}%
                </span>
                <span class="text-xs text-slate-400">dibandingkan periode sebelumnya</span>
            </div>
        </div>
    </div>

    {{-- GRAFIK --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:rounded-3xl sm:p-6">
        <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 sm:text-xl">Ringkasan Pendapatan</h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Pendapatan berdasarkan transaksi yang berhasil.</p>
            </div>
            <span class="rounded-full bg-[#AE7C18]/10 px-3 py-1 text-xs font-semibold text-[#AE7C18]">
                {{ $monthlyRevenue->count() }} Periode
            </span>
        </div>

        @php
            $maxRevenue = max(1,$monthlyRevenue->max('revenue') ?? 1);
        @endphp

        <div class="mt-6 flex h-[280px] items-end gap-2 overflow-x-auto pb-8 sm:h-[360px] sm:gap-4">
            @forelse($monthlyRevenue as $month)
                @php
                    $height = $month['revenue'] > 0 ? max(8,($month['revenue'] / $maxRevenue) * 100) : 2;
                @endphp
                <div class="flex h-full min-w-[55px] flex-1 flex-col justify-end sm:min-w-[70px]">
                    <div class="group relative flex h-full items-end justify-center">
                        <div class="absolute bottom-full mb-2 hidden whitespace-nowrap rounded-lg bg-slate-900 px-2 py-1 text-[10px] text-white group-hover:block">
                            Rp{{ number_format($month['revenue'],0,',','.') }}
                        </div>
                        <div class="w-full max-w-[45px] rounded-t-lg bg-[#AE7C18] transition hover:bg-[#96690F]" style="height:{{ $height }}%"></div>
                    </div>
                    <span class="mt-2 truncate text-center text-[10px] text-slate-500 sm:text-xs">{{ $month['label'] }}</span>
                </div>
            @empty
                <div class="flex w-full items-center justify-center text-sm text-slate-400">
                    Belum ada data pendapatan.
                </div>
            @endforelse
        </div>
    </div>

    {{-- PRODUK --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        <div class="flex flex-col gap-2 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 sm:text-xl">Penjualan per Produk</h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Produk terlaris berdasarkan unit terjual.</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-[#AE7C18]/10 px-3 py-1 text-xs font-semibold text-[#AE7C18]">
                {{ $topProducts->count() }} Produk Teratas
            </span>
        </div>

        {{-- Mobile View: Card List --}}
        <div class="divide-y divide-slate-100 sm:hidden">
            @forelse($topProducts as $product)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10">
                                <x-heroicon-o-cube class="h-4 w-4 text-[#AE7C18]"/>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $product['name'] }}</p>
                                <p class="text-xs text-slate-400">{{ number_format($product['units'],0,',','.') }} unit terjual</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-[#AE7C18] bg-[#AE7C18]/10 px-2.5 py-1 rounded-full">{{ $product['percentage'] }}%</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 text-xs">
                        <span class="text-slate-500">Pendapatan</span>
                        <span class="font-semibold text-slate-900">Rp{{ number_format($product['revenue'],0,',','.') }}</span>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-sm text-slate-400">Belum ada data produk terjual.</div>
            @endforelse
        </div>

        {{-- Desktop View: Table --}}
        <div class="hidden overflow-x-auto sm:block">
            <table class="min-w-full">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="whitespace-nowrap px-6 py-4">Produk</th>
                        <th class="whitespace-nowrap px-6 py-4 text-center">Unit Terjual</th>
                        <th class="whitespace-nowrap px-6 py-4 text-right">Pendapatan</th>
                        <th class="whitespace-nowrap px-6 py-4 text-right">Share</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($topProducts as $product)
                        <tr class="transition hover:bg-slate-50">
                            <td class="whitespace-nowrap px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10">
                                        <x-heroicon-o-cube class="h-4 w-4 text-[#AE7C18]"/>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $product['name'] }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">Penjualan produk</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-5 text-center text-sm font-semibold text-slate-700">{{ number_format($product['units'],0,',','.') }}</td>
                            <td class="whitespace-nowrap px-6 py-5 text-right text-sm font-semibold text-slate-900">Rp{{ number_format($product['revenue'],0,',','.') }}</td>
                            <td class="whitespace-nowrap px-6 py-5">
                                <div class="flex items-center justify-end gap-3">
                                    <div class="w-20 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-2 rounded-full bg-[#AE7C18]" style="width:{{ min(100,$product['percentage']) }}%"></div>
                                    </div>
                                    <span class="w-10 text-right text-sm font-semibold text-slate-700">{{ $product['percentage'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-400">Belum ada data produk terjual.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 p-4 sm:px-6 sm:py-4">
            <p class="text-xs text-slate-500 sm:text-sm">Menampilkan {{ $topProducts->count() }} produk teratas</p>
        </div>
    </div>

    {{-- KATEGORI --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        <div class="flex flex-col gap-2 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 sm:text-xl">Penjualan per Kategori</h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Kinerja penjualan berdasarkan kategori produk.</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-[#AE7C18]/10 px-3 py-1 text-xs font-semibold text-[#AE7C18]">
                {{ $salesCategories->count() }} Kategori
            </span>
        </div>

        {{-- Mobile View: Card List --}}
        <div class="divide-y divide-slate-100 sm:hidden">
            @forelse($salesCategories as $category)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10">
                                <x-heroicon-o-squares-2x2 class="h-4 w-4 text-[#AE7C18]"/>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $category['name'] }}</p>
                                <p class="text-xs text-slate-400">{{ number_format($category['products'],0,',','.') }} produk terjual</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-[#AE7C18] bg-[#AE7C18]/10 px-2.5 py-1 rounded-full">{{ $category['percentage'] }}%</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 text-xs">
                        <span class="text-slate-500">Pendapatan</span>
                        <span class="font-semibold text-slate-900">Rp{{ number_format($category['revenue'],0,',','.') }}</span>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center text-sm text-slate-400">Belum ada data kategori.</div>
            @endforelse
        </div>

        {{-- Desktop View: Table --}}
        <div class="hidden overflow-x-auto sm:block">
            <table class="min-w-full">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="whitespace-nowrap px-6 py-4">Kategori</th>
                        <th class="whitespace-nowrap px-6 py-4 text-center">Produk Terjual</th>
                        <th class="whitespace-nowrap px-6 py-4 text-right">Pendapatan</th>
                        <th class="whitespace-nowrap px-6 py-4 text-right">Share</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($salesCategories as $category)
                        <tr class="transition hover:bg-slate-50">
                            <td class="whitespace-nowrap px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10">
                                        <x-heroicon-o-squares-2x2 class="h-4 w-4 text-[#AE7C18]"/>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $category['name'] }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">Penjualan kategori</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-5 text-center text-sm font-semibold text-slate-700">{{ number_format($category['products'],0,',','.') }}</td>
                            <td class="whitespace-nowrap px-6 py-5 text-right text-sm font-semibold text-slate-900">Rp{{ number_format($category['revenue'],0,',','.') }}</td>
                            <td class="whitespace-nowrap px-6 py-5">
                                <div class="flex items-center justify-end gap-3">
                                    <div class="w-20 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-2 rounded-full bg-[#AE7C18]" style="width:{{ min(100,$category['percentage']) }}%"></div>
                                    </div>
                                    <span class="w-10 text-right text-sm font-semibold text-slate-700">{{ $category['percentage'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-400">Belum ada data kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 p-4 sm:px-6 sm:py-4">
            <p class="text-xs text-slate-500 sm:text-sm">Menampilkan {{ $salesCategories->count() }} kategori</p>
        </div>
    </div>

    {{-- METODE PEMBAYARAN --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        <div class="flex flex-col gap-2 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 sm:text-xl">Metode Pembayaran</h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Distribusi transaksi berdasarkan metode pembayaran.</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-[#AE7C18]/10 px-3 py-1 text-xs font-semibold text-[#AE7C18]">
                {{ $paymentMethods->count() }} Metode
            </span>
        </div>

        <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-6 xl:grid-cols-4">
            @forelse($paymentMethods as $payment)
                <div class="rounded-2xl border border-slate-200 p-4 transition hover:border-slate-300 hover:shadow-sm sm:p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 sm:text-base">{{ $payment['name'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ number_format($payment['transactions'],0,',','.') }} transaksi</p>
                        </div>

                        @if($payment['icon'] === 'banknotes')
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100">
                                <x-heroicon-o-banknotes class="h-5 w-5 text-emerald-600"/>
                            </div>
                        @elseif($payment['icon'] === 'qr-code')
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100">
                                <x-heroicon-o-qr-code class="h-5 w-5 text-violet-600"/>
                            </div>
                        @elseif($payment['icon'] === 'building-library')
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100">
                                <x-heroicon-o-building-library class="h-5 w-5 text-blue-600"/>
                            </div>
                        @else
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100">
                                <x-heroicon-o-credit-card class="h-5 w-5 text-amber-600"/>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <p class="text-xs font-medium text-slate-400">Pendapatan</p>
                        <p class="mt-0.5 text-base font-bold text-slate-900">Rp{{ number_format($payment['revenue'],0,',','.') }}</p>
                    </div>

                    <div class="mt-4">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-xs text-slate-500">Share</span>
                            <span class="text-xs font-semibold text-slate-700">{{ $payment['percentage'] }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-[#AE7C18]" style="width:{{ min(100,$payment['percentage']) }}%"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="py-10 text-center text-sm text-slate-400 sm:col-span-2 xl:col-span-4">Belum ada data pembayaran.</div>
            @endforelse
        </div>
    </div>

    {{-- TRANSAKSI --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        <div class="flex flex-col gap-2 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 sm:text-xl">Laporan Transaksi</h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Catatan transaksi yang berhasil untuk periode terpilih.</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-[#AE7C18]/10 px-3 py-1 text-xs font-semibold text-[#AE7C18]">
                {{ $transactions->count() }} Transaksi
            </span>
        </div>

        {{-- Mobile View: Card List untuk Transaksi --}}
        <div class="divide-y divide-slate-100 sm:hidden">
            @forelse($transactions as $transaction)
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-900">{{ $transaction->invoice_number }}</span>
                        <x-admin.badge-status :status="$transaction->status"/>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <span class="text-slate-400 block">Tanggal</span>
                            <span class="font-medium text-slate-700">{{ $transaction->transaction_date?->format('d M Y, H:i') ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Pelanggan</span>
                            <span class="font-medium text-slate-700">{{ $transaction->customer?->name ?? '-' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <div>
                            @php
                                $payment = strtoupper($transaction->payment_method ?? '');
                            @endphp
                            @if($payment === 'CASH')
                                <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">CASH</span>
                            @elseif($payment === 'QRIS')
                                <span class="inline-flex rounded-full bg-violet-100 px-2 py-0.5 text-[10px] font-semibold text-violet-700">QRIS</span>
                            @elseif(in_array($payment,['TRANSFER','TRANSFER_BANK','BANK_TRANSFER']))
                                <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700">Transfer</span>
                            @elseif($payment === 'EDC')
                                <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">EDC</span>
                            @else
                                <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700">{{ $transaction->payment_method ?? '-' }}</span>
                            @endif
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-400">Total: </span>
                            <span class="text-sm font-bold text-slate-900">Rp{{ number_format($transaction->total,0,',','.') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 mb-2">
                            <x-heroicon-o-receipt-percent class="h-6 w-6 text-slate-400"/>
                        </div>
                        <p class="text-sm text-slate-400">Belum ada data transaksi.</p>
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