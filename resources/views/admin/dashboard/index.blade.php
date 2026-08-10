@extends('admin.layouts.app')

@section('title', 'Dasboard')

@section('page-title', 'Dasboard')

@section('content')
<div class="space-y-6 sm:space-y-8">

    {{-- ================= STATISTIK ================= --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 xl:grid-cols-5">

        <x-admin.stat-card
            title="Produk"
            value="128"
            growth="+12%">
            <x-slot:icon>
                <x-heroicon-o-cube class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Kategori"
            value="12"
            color="bg-sky-500"
            growth="+4%">
            <x-slot:icon>
                <x-heroicon-o-tag class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Pesanan"
            value="846"
            color="bg-emerald-500"
            growth="+18%">
            <x-slot:icon>
                <x-heroicon-o-shopping-bag class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Pendapatan"
            value="Rp 24,5 Jt"
            color="bg-violet-500"
            growth="+9%">
            <x-slot:icon>
                <x-heroicon-o-banknotes class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Pelanggan"
            value="1.258"
            color="bg-rose-500"
            growth="+15%">
            <x-slot:icon>
                <x-heroicon-o-users class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

    </div>

    {{-- ================= RINGKASAN PENJUALAN & PRODUK TERLARIS ================= --}}
    <div class="grid grid-cols-1 gap-6 sm:gap-8 xl:grid-cols-3">

        {{-- ================= GRAFIK PENJUALAN ================= --}}
        <div class="xl:col-span-2">
            <x-admin.chart-card
                title="Ringkasan Penjualan"
                subtitle="Performa penjualan bulanan"
                chartId="salesChart"
                height="360"/>
        </div>

        {{-- ================= PRODUK TERLARIS ================= --}}
        <div>
            <x-admin.top-products />
        </div>

    </div>

</div>
@endsection