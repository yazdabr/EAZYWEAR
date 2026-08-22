@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-5 sm:space-y-8">
    {{-- Stat Cards Grid: 2 Kolom di Mobile, 5 Kolom di XL --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-5">
        
        {{-- Produk --}}
        <x-admin.stat-card
            title="Produk"
            value="{{ number_format($totalProducts, 0, ',', '.') }}"
            growth="{{ $growthProducts['value'] }}"
            :positive="$growthProducts['positive']"
            :neutral="$growthProducts['neutral']"
            iconBg="bg-amber-50"
            iconColor="text-[#AE7C18]"
        >
            <x-slot:icon>
                <x-heroicon-o-cube class="h-5 w-5 sm:h-6 sm:w-6"/>
            </x-slot:icon>
        </x-admin.stat-card>

        {{-- Kategori --}}
        <x-admin.stat-card
            title="Kategori"
            value="{{ number_format($totalCategories, 0, ',', '.') }}"
            growth="{{ $growthCategories['value'] }}"
            :positive="$growthCategories['positive']"
            :neutral="$growthCategories['neutral']"
            iconBg="bg-blue-50"
            iconColor="text-blue-600"
        >
            <x-slot:icon>
                <x-heroicon-o-tag class="h-5 w-5 sm:h-6 sm:w-6"/>
            </x-slot:icon>
        </x-admin.stat-card>

        {{-- Pesanan --}}
        <x-admin.stat-card
            title="Pesanan"
            value="{{ number_format($totalOrders, 0, ',', '.') }}"
            growth="{{ $growthOrders['value'] }}"
            :positive="$growthOrders['positive']"
            :neutral="$growthOrders['neutral']"
            iconBg="bg-emerald-50"
            iconColor="text-emerald-600"
        >
            <x-slot:icon>
                <x-heroicon-o-shopping-bag class="h-5 w-5 sm:h-6 sm:w-6"/>
            </x-slot:icon>
        </x-admin.stat-card>

        {{-- Pelanggan --}}
        <x-admin.stat-card
            title="Pelanggan"
            value="{{ number_format($totalCustomers, 0, ',', '.') }}"
            growth="{{ $growthCustomers['value'] }}"
            :positive="$growthCustomers['positive']"
            :neutral="$growthCustomers['neutral']"
            iconBg="bg-rose-50"
            iconColor="text-rose-600"
        >
            <x-slot:icon>
                <x-heroicon-o-users class="h-5 w-5 sm:h-6 sm:w-6"/>
            </x-slot:icon>
        </x-admin.stat-card>

        {{-- Pendapatan (Full Width 2 Kolom di Mobile) --}}
        <div class="col-span-2 sm:col-span-1">
            <x-admin.stat-card
                title="Pendapatan"
                value="Rp {{ number_format($totalRevenue, 0, ',', '.') }}"
                growth="{{ $growthRevenue['value'] }}"
                :positive="$growthRevenue['positive']"
                :neutral="$growthRevenue['neutral']"
                iconBg="bg-violet-50"
                iconColor="text-violet-600"
            >
                <x-slot:icon>
                    <x-heroicon-o-banknotes class="h-5 w-5 sm:h-6 sm:w-6"/>
                </x-slot:icon>
            </x-admin.stat-card>
        </div>

    </div>

    {{-- Chart & Top Products Grid --}}
    <div class="grid grid-cols-1 gap-5 sm:gap-8 xl:grid-cols-3">
        <div class="xl:col-span-2">
            <x-admin.chart-card
                title="Ringkasan Penjualan"
                subtitle="Performa penjualan bulanan"
                chartId="salesChart"
                height="320"
                :data="$salesChart"
            />
        </div>

        <div>
            <x-admin.top-products
                :products="$topProducts"
                :top-products-max="$topProductsMax"
            />
        </div>
    </div>
</div>
@endsection