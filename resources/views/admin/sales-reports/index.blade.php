@extends('admin.layouts.app')

@section('title', 'Sales Reports')
@section('page-title', 'Sales Reports')

@section('content')
<div class="space-y-4 sm:space-y-6">
    {{-- PAGE HEADER --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Sales Reports</h1>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">Analyze your sales performance and revenue.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('admin.sales-reports.print') }}" target="_blank" class="inline-flex h-[46px] w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 text-sm font-semibold text-slate-700 transition-all duration-300 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-2 active:scale-[0.98] sm:h-[50px] sm:w-auto">
                <x-heroicon-o-printer class="h-5 w-5" />
                <span>Print Report</span>
            </a>
            <button type="button" @click="$dispatch('toast',{ type:'info', title:'Export Report', message:'Export functionality will be connected when sales data is integrated.' })" class="inline-flex h-[46px] w-full items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18] focus:ring-offset-2 active:scale-[0.98] sm:h-[50px] sm:w-auto">
                <x-heroicon-o-arrow-down-tray class="h-5 w-5" />
                <span>Export Report</span>
            </button>
        </div>
    </div>

    {{-- OVERVIEW --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:px-6 sm:py-4">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                <x-heroicon-o-information-circle class="h-5 w-5 shrink-0 text-slate-400" />
                <span class="text-xs text-slate-600 sm:text-sm">Sales report overview</span>
            </div>
            <span class="text-xs font-medium text-slate-500 sm:text-sm">All sales data</span>
        </div>
    </div>

    {{-- FILTER --}}
<div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all sm:rounded-3xl sm:p-6">
    {{-- Header Filter --}}
    <div class="mb-5 flex flex-col gap-1 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-2.5">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]">
                <x-heroicon-o-funnel class="h-5 w-5" />
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-800 sm:text-lg">Filter Data</h3>
                <p class="text-xs text-slate-500 sm:hidden">Filter berdasarkan periode laporan</p>
            </div>
        </div>
        <span class="hidden text-xs text-slate-400 sm:inline-block">Filter berdasarkan periode laporan</span>
    </div>

    {{-- Form Grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-12 lg:items-end">
        {{-- Date From --}}
        <div class="lg:col-span-3 xl:col-span-3">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Dari Tanggal</label>
            <div class="relative">
                <input type="date" value="2026-08-01" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-xs font-medium text-slate-700 transition duration-200 focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:text-sm">
            </div>
        </div>

        {{-- Date To --}}
        <div class="lg:col-span-3 xl:col-span-3">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Sampai Tanggal</label>
            <div class="relative">
                <input type="date" value="2026-08-08" class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-3 text-xs font-medium text-slate-700 transition duration-200 focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:text-sm">
            </div>
        </div>

        {{-- Month --}}
        <div class="lg:col-span-3 xl:col-span-2">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Bulan</label>
            <x-admin.select class="h-11 w-full border-slate-200 bg-slate-50/50 text-xs font-medium focus:bg-white sm:text-sm">
                <option value="">Semua Bulan</option>
                <option value="1">Januari</option>
                <option value="2">Februari</option>
                <option value="3">Maret</option>
                <option value="4">April</option>
                <option value="5">Mei</option>
                <option value="6">Juni</option>
                <option value="7">Juli</option>
                <option value="8">Agustus</option>
                <option value="9">September</option>
                <option value="10">Oktober</option>
                <option value="11">November</option>
                <option value="12">Desember</option>
            </x-admin.select>
        </div>

        {{-- Year --}}
        <div class="lg:col-span-3 xl:col-span-2">
            <label class="mb-1.5 block text-xs font-semibold text-slate-600">Tahun</label>
            <x-admin.select class="h-11 w-full border-slate-200 bg-slate-50/50 text-xs font-medium focus:bg-white sm:text-sm">
                <option value="">Semua Tahun</option>
                <option value="2026">2026</option>
                <option value="2025">2025</option>
                <option value="2024">2024</option>
            </x-admin.select>
        </div>

        {{-- Action Buttons --}}
        <div class="sm:col-span-2 lg:col-span-12 xl:col-span-2">
            <div class="flex items-center gap-2">
                <button type="button" @click="$dispatch('toast',{ type:'info', title:'Report Filtered', message:'Sales report has been filtered successfully.' })" class="inline-flex h-11 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#AE7C18] px-4 text-xs font-semibold text-white shadow-sm shadow-[#AE7C18]/30 transition-all duration-200 hover:bg-[#96690F] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#AE7C18] focus:ring-offset-2 active:scale-[0.98] sm:text-sm">
                    <x-heroicon-o-magnifying-glass class="h-4 w-4 shrink-0" />
                    <span>Filter</span>
                </button>

                <button type="button" title="Reset Filter" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 transition-all duration-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 active:scale-[0.98]">
                    <x-heroicon-o-arrow-path class="h-4 w-4" />
                </button>
            </div>
        </div>
    </div>
</div>

    {{-- SUMMARY STATS --}}
    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 sm:gap-6 xl:grid-cols-4">
        {{-- Revenue --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:rounded-3xl sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 sm:text-sm">Total Revenue</p>
                    <h3 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:mt-2 sm:text-2xl">Rp24.580.000</h3>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-banknotes class="h-5 w-5 text-emerald-600 sm:h-6 sm:w-6" />
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 sm:mt-5">
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5" /> +12.5%
                </span>
                <span class="text-xs text-slate-400">vs previous period</span>
            </div>
        </div>

        {{-- Transactions --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:rounded-3xl sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 sm:text-sm">Total Transactions</p>
                    <h3 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:mt-2 sm:text-2xl">128</h3>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-receipt-percent class="h-5 w-5 text-blue-600 sm:h-6 sm:w-6" />
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 sm:mt-5">
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5" /> +8.4%
                </span>
                <span class="text-xs text-slate-400">vs previous period</span>
            </div>
        </div>

        {{-- AOV --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:rounded-3xl sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 sm:text-sm">Average Order Value</p>
                    <h3 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:mt-2 sm:text-2xl">Rp192.031</h3>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-chart-bar class="h-5 w-5 text-violet-600 sm:h-6 sm:w-6" />
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 sm:mt-5">
                <span class="inline-flex items-center gap-1 rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-700">
                    <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5" /> +3.7%
                </span>
                <span class="text-xs text-slate-400">vs previous period</span>
            </div>
        </div>

        {{-- Products Sold --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:rounded-3xl sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 sm:text-sm">Products Sold</p>
                    <h3 class="mt-1 text-xl font-bold tracking-tight text-slate-900 sm:mt-2 sm:text-2xl">342</h3>
                </div>
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-cube class="h-5 w-5 text-amber-600 sm:h-6 sm:w-6" />
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2 sm:mt-5">
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">
                    <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5" /> +15.2%
                </span>
                <span class="text-xs text-slate-400">vs previous period</span>
            </div>
        </div>
    </div>

    {{-- CHART --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:rounded-3xl sm:p-6">
        <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between sm:pb-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 sm:text-xl">Revenue Overview</h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Monthly revenue performance.</p>
            </div>
            <div>
                <select class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:w-auto sm:px-4 sm:py-2.5">
                    <option>Monthly</option>
                    <option>Weekly</option>
                    <option>Daily</option>
                </select>
            </div>
        </div>
        <div class="mt-4 h-[280px] sm:mt-6 sm:h-[360px]">
            <div class="flex h-full items-center justify-center rounded-2xl bg-slate-50 p-4">
                <div class="text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-14 sm:w-14">
                        <x-heroicon-o-chart-bar class="h-6 w-6 text-[#AE7C18] sm:h-7 sm:w-7" />
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-700 sm:mt-4 sm:text-base">Revenue Chart</p>
                    <p class="mt-1 text-xs text-slate-400 sm:text-sm">Chart data will be connected to sales transactions.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- SALES BY PRODUCT --}}
    @php
    $topProducts = [
        ['name' => 'Apex Pro Jersey', 'units' => 86, 'revenue' => 'Rp12.814.000', 'percentage' => 32],
        ['name' => 'Elite Training Jersey', 'units' => 64, 'revenue' => 'Rp8.256.000', 'percentage' => 24],
        ['name' => 'Classic Football Jersey', 'units' => 52, 'revenue' => 'Rp6.708.000', 'percentage' => 19],
        ['name' => 'Pro Basketball Jersey', 'units' => 38, 'revenue' => 'Rp5.244.000', 'percentage' => 14],
        ['name' => 'Esports Performance Jersey', 'units' => 27, 'revenue' => 'Rp3.618.000', 'percentage' => 11],
    ];
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        <div class="flex flex-col gap-2 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 sm:text-xl">Sales by Product</h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Top selling products based on units sold.</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-[#AE7C18]/10 px-3 py-1 text-xs font-semibold text-[#AE7C18] sm:py-1.5">Top 5 Products</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="whitespace-nowrap px-4 py-3 sm:px-6 sm:py-4">Product</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center sm:px-6 sm:py-4">Units Sold</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right sm:px-6 sm:py-4">Revenue</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right sm:px-6 sm:py-4">Share</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($topProducts as $product)
                        <tr class="transition hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-4 sm:px-6 sm:py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-10 sm:w-10">
                                        <x-heroicon-o-cube class="h-4 w-4 text-[#AE7C18] sm:h-5 sm:w-5" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $product['name'] }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">Product sales</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-center text-sm font-semibold text-slate-700 sm:px-6 sm:py-5">{{ $product['units'] }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-slate-900 sm:px-6 sm:py-5">{{ $product['revenue'] }}</td>
                            <td class="whitespace-nowrap px-4 py-4 sm:px-6 sm:py-5">
                                <div class="flex items-center justify-end gap-3">
                                    <div class="hidden w-20 overflow-hidden rounded-full bg-slate-100 sm:block">
                                        <div class="h-2 rounded-full bg-[#AE7C18]" style="width: {{ $product['percentage'] }}%;"></div>
                                    </div>
                                    <span class="w-10 text-right text-sm font-semibold text-slate-700">{{ $product['percentage'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between border-t border-slate-200 p-4 sm:px-6 sm:py-4">
            <p class="text-xs text-slate-500 sm:text-sm">Showing top 5 products</p>
            <button type="button" class="text-xs font-semibold text-[#AE7C18] transition hover:underline sm:text-sm">View All Products</button>
        </div>
    </div>

    {{-- SALES BY CATEGORY --}}
    @php
    $salesCategories = [
        ['name' => 'Football', 'products' => 96, 'revenue' => 'Rp14.652.000', 'percentage' => 42],
        ['name' => 'Basketball', 'products' => 64, 'revenue' => 'Rp9.184.000', 'percentage' => 27],
        ['name' => 'Esports', 'products' => 48, 'revenue' => 'Rp6.432.000', 'percentage' => 19],
        ['name' => 'Futsal', 'products' => 31, 'revenue' => 'Rp4.092.000', 'percentage' => 12],
    ];
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        <div class="flex flex-col gap-2 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 sm:text-xl">Sales by Category</h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Sales performance grouped by product category.</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-[#AE7C18]/10 px-3 py-1 text-xs font-semibold text-[#AE7C18] sm:py-1.5">All Categories</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="whitespace-nowrap px-4 py-3 sm:px-6 sm:py-4">Category</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center sm:px-6 sm:py-4">Products Sold</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right sm:px-6 sm:py-4">Revenue</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right sm:px-6 sm:py-4">Share</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($salesCategories as $category)
                        <tr class="transition hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-4 sm:px-6 sm:py-5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-10 sm:w-10">
                                        <x-heroicon-o-squares-2x2 class="h-4 w-4 text-[#AE7C18] sm:h-5 sm:w-5" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">{{ $category['name'] }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">Category sales</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-center text-sm font-semibold text-slate-700 sm:px-6 sm:py-5">{{ $category['products'] }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold text-slate-900 sm:px-6 sm:py-5">{{ $category['revenue'] }}</td>
                            <td class="whitespace-nowrap px-4 py-4 sm:px-6 sm:py-5">
                                <div class="flex items-center justify-end gap-3">
                                    <div class="hidden w-20 overflow-hidden rounded-full bg-slate-100 sm:block">
                                        <div class="h-2 rounded-full bg-[#AE7C18]" style="width: {{ $category['percentage'] }}%;"></div>
                                    </div>
                                    <span class="w-10 text-right text-sm font-semibold text-slate-700">{{ $category['percentage'] }}%</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between border-t border-slate-200 p-4 sm:px-6 sm:py-4">
            <p class="text-xs text-slate-500 sm:text-sm">Showing {{ count($salesCategories) }} categories</p>
            <button type="button" class="text-xs font-semibold text-[#AE7C18] transition hover:underline sm:text-sm">View All Categories</button>
        </div>
    </div>

    {{-- PAYMENT METHODS --}}
    @php
    $paymentMethods = [
        ['name' => 'Cash', 'transactions' => 42, 'revenue' => 'Rp8.240.000', 'percentage' => 33, 'icon' => 'banknotes'],
        ['name' => 'QRIS', 'transactions' => 38, 'revenue' => 'Rp7.180.000', 'percentage' => 30, 'icon' => 'qr-code'],
        ['name' => 'Bank Transfer', 'transactions' => 29, 'revenue' => 'Rp5.920.000', 'percentage' => 23, 'icon' => 'building-library'],
        ['name' => 'EDC', 'transactions' => 19, 'revenue' => 'Rp3.240.000', 'percentage' => 14, 'icon' => 'credit-card'],
    ];
    @endphp

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        <div class="flex flex-col gap-2 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 sm:text-xl">Payment Methods</h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Transaction distribution by payment method.</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-[#AE7C18]/10 px-3 py-1 text-xs font-semibold text-[#AE7C18] sm:py-1.5">4 Payment Methods</span>
        </div>

        <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-6 xl:grid-cols-4">
            @foreach($paymentMethods as $payment)
                <div class="rounded-2xl border border-slate-200 p-4 transition hover:border-slate-300 hover:shadow-sm sm:p-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-slate-900 sm:text-base">{{ $payment['name'] }}</p>
                            <p class="mt-0.5 text-xs text-slate-400 sm:mt-1">{{ $payment['transactions'] }} transactions</p>
                        </div>
                        @if($payment['icon'] === 'banknotes')
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-100 sm:h-10 sm:w-10">
                                <x-heroicon-o-banknotes class="h-5 w-5 text-emerald-600" />
                            </div>
                        @elseif($payment['icon'] === 'qr-code')
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 sm:h-10 sm:w-10">
                                <x-heroicon-o-qr-code class="h-5 w-5 text-violet-600" />
                            </div>
                        @elseif($payment['icon'] === 'building-library')
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-100 sm:h-10 sm:w-10">
                                <x-heroicon-o-building-library class="h-5 w-5 text-blue-600" />
                            </div>
                        @else
                            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-100 sm:h-10 sm:w-10">
                                <x-heroicon-o-credit-card class="h-5 w-5 text-amber-600" />
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 sm:mt-5">
                        <p class="text-xs font-medium text-slate-400">Revenue</p>
                        <p class="mt-0.5 text-base font-bold text-slate-900 sm:mt-1 sm:text-lg">{{ $payment['revenue'] }}</p>
                    </div>
                    <div class="mt-3 sm:mt-4">
                        <div class="mb-1.5 flex items-center justify-between sm:mb-2">
                            <span class="text-xs text-slate-500">Share</span>
                            <span class="text-xs font-semibold text-slate-700">{{ $payment['percentage'] }}%</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full bg-[#AE7C18]" style="width: {{ $payment['percentage'] }}%;"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- TRANSACTION REPORT TABLE --}}
    @php
    $reportTransactions = [
        ['invoice' => 'INV-20260808-001', 'date' => '08 Aug 2026', 'customer' => 'John Doe', 'payment' => 'QRIS', 'status' => 'Paid', 'total' => 'Rp517.000'],
        ['invoice' => 'INV-20260808-002', 'date' => '08 Aug 2026', 'customer' => 'Sarah Wilson', 'payment' => 'Cash', 'status' => 'Completed', 'total' => 'Rp298.000'],
        ['invoice' => 'INV-20260807-003', 'date' => '07 Aug 2026', 'customer' => 'Michael Brown', 'payment' => 'Transfer', 'status' => 'Completed', 'total' => 'Rp745.000'],
        ['invoice' => 'INV-20260807-004', 'date' => '07 Aug 2026', 'customer' => 'Emily Davis', 'payment' => 'EDC', 'status' => 'Paid', 'total' => 'Rp425.000'],
        ['invoice' => 'INV-20260806-005', 'date' => '06 Aug 2026', 'customer' => 'James Anderson', 'payment' => 'QRIS', 'status' => 'Completed', 'total' => 'Rp632.000'],
    ];
    @endphp

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        <div class="flex flex-col gap-2 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 sm:text-xl">Transaction Report</h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Detailed transaction records for the selected period.</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-[#AE7C18]/10 px-3 py-1 text-xs font-semibold text-[#AE7C18] sm:py-1.5">
                {{ count($reportTransactions) }} Transactions
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="whitespace-nowrap px-4 py-3 sm:px-6 sm:py-4">Invoice</th>
                        <th class="whitespace-nowrap px-4 py-3 sm:px-6 sm:py-4">Date</th>
                        <th class="whitespace-nowrap px-4 py-3 sm:px-6 sm:py-4">Customer</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center sm:px-6 sm:py-4">Payment</th>
                        <th class="whitespace-nowrap px-4 py-3 text-center sm:px-6 sm:py-4">Status</th>
                        <th class="whitespace-nowrap px-4 py-3 text-right sm:px-6 sm:py-4">Total</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200">
                    @foreach($reportTransactions as $transaction)
                        <tr class="transition hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-4 sm:px-6 sm:py-5">
                                <span class="text-sm font-semibold text-slate-900">{{ $transaction['invoice'] }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 sm:px-6 sm:py-5">
                                <span class="text-xs text-slate-500 sm:text-sm">{{ $transaction['date'] }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 sm:px-6 sm:py-5">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $transaction['customer'] }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">Customer</p>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-center sm:px-6 sm:py-5">
                                @if($transaction['payment'] === 'Cash')
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 sm:px-3 sm:py-1">Cash</span>
                                @elseif($transaction['payment'] === 'QRIS')
                                    <span class="inline-flex items-center rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-700 sm:px-3 sm:py-1">QRIS</span>
                                @elseif($transaction['payment'] === 'Transfer')
                                    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700 sm:px-3 sm:py-1">Transfer</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-700 sm:px-3 sm:py-1">EDC</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-center sm:px-6 sm:py-5">
                                <x-admin.badge-status :status="$transaction['status']" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 text-right sm:px-6 sm:py-5">
                                <span class="text-sm font-semibold text-slate-900">{{ $transaction['total'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- FOOTER --}}
        <div class="flex items-center justify-between border-t border-slate-200 p-4 sm:px-6 sm:py-4">
            <p class="text-xs text-slate-500 sm:text-sm">Showing {{ count($reportTransactions) }} transactions</p>
            <button type="button" class="text-xs font-semibold text-[#AE7C18] transition hover:underline sm:text-sm">View Transaction List</button>
        </div>
    </div>
</div>
@endsection