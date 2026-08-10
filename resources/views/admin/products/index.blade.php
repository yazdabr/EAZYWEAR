@extends('admin.layouts.app')

@section('title', 'Produk')
@section('page-title', 'Produk')

@section('content')

<div class="space-y-8">

    {{-- ================= HEADER HALAMAN ================= --}}
    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        {{-- Judul & Subjudul --}}
        <div class="space-y-1">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Produk</h1>
            <p class="text-sm text-slate-500">Kelola semua produk yang tersedia di toko Anda.</p>
        </div>

        {{-- Kontrol Toolbar --}}
        <div class="flex w-full flex-col gap-4 lg:w-auto lg:flex-row lg:items-center lg:justify-end">

            {{-- Cari --}}
            <div class="w-full lg:w-80">

                <x-admin.search-input
                    placeholder="Cari produk..." />

            </div>

            {{-- Filter Kategori --}}
            <div class="w-full lg:w-56">

                <x-admin.filter-select
                    placeholder="Semua Kategori"
                    :options="[
                        'Semua Kategori',
                        'Sepak Bola',
                        'Bola Basket',
                        'Futsal',
                        'Bola Voli',
                        'Lari',
                        'Bersepeda',
                    ]" />

            </div>

            {{-- Tambah Produk --}}
            <div class="lg:ml-3">

                <button

                    @click="$dispatch('open-create-product')"

                    class="inline-flex h-[50px] w-full items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#AE7C18] px-6 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18] focus:ring-offset-2 active:scale-[0.98] lg:w-auto">

                    <x-heroicon-o-plus
                        class="h-5 w-5"/>

                    <span>Tambah Produk</span>

                </button>

            </div>

        </div>
    </div>

    {{-- ================= TABEL PRODUK ================= --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <x-admin.product-table />
    </div>

</div>

@include('admin.products.partials.create-product')
@include('admin.products.partials.delete-product')
@include('admin.products.partials.view-product')

@endsection