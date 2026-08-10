@extends('admin.layouts.app')

@section('title', 'Kategori')
@section('page-title', 'Kategori')

@section('content')
<div class="space-y-6 md:space-y-8">
    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col gap-4 sm:gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">Kategori</h2>
            <p class="mt-1 text-sm text-slate-500 sm:mt-2">Kelola dan atur semua kategori produk untuk toko Anda.</p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            {{-- Search --}}
            <div class="w-full sm:w-72 md:w-80">
                <x-admin.search-input placeholder="Cari kategori..." />
            </div>

            {{-- Tombol Tambah Kategori --}}
            <button @click="$dispatch('open-create-category')" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18] focus:ring-offset-2 active:scale-[0.98] sm:px-6 sm:py-3 sm:text-base">
                <x-heroicon-o-plus class="h-5 w-5" />
                <span>Tambah Kategori</span>
            </button>
        </div>
    </div>

    {{-- ================= STATS ================= --}}
    <div class="grid gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <x-admin.stat-card title="Total Kategori" value="12" growth="+2">
            <x-slot:icon>
                <x-heroicon-o-squares-2x2 class="h-6 w-6 sm:h-7 sm:w-7" />
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card title="Produk Aktif" value="482" color="bg-emerald-500" growth="+18">
            <x-slot:icon>
                <x-heroicon-o-cube class="h-6 w-6 sm:h-7 sm:w-7" />
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card title="Aktif Sekarang" value="8" color="bg-amber-500" growth="+1" class="sm:col-span-2 lg:col-span-1">
            <x-slot:icon>
                <x-heroicon-o-eye class="h-6 w-6 sm:h-7 sm:w-7" />
            </x-slot:icon>
        </x-admin.stat-card>
    </div>

    {{-- ================= TABLE DATA ================= --}}
    @php
    $categories = [
        [
            'name' => 'Football',
            'description' => 'Football Jersey',
            'slug' => 'football',
            'products' => 32,
            'status' => 'Aktif',
            'created' => '10 May 2024',
        ],
        [
            'name' => 'Basketball',
            'description' => 'Basketball Jersey',
            'slug' => 'basketball',
            'products' => 45,
            'status' => 'Aktif',
            'created' => '12 May 2024',
        ],
        [
            'name' => 'Esports',
            'description' => 'Gaming Jersey',
            'slug' => 'esports',
            'products' => 28,
            'status' => 'Aktif',
            'created' => '08 May 2024',
        ],
    ];
    @endphp

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        {{-- Responsive Table Container --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] md:min-w-[900px]">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3.5 whitespace-nowrap sm:px-6 sm:py-4">Kategori</th>
                        <th class="px-4 py-3.5 whitespace-nowrap sm:px-6 sm:py-4">Slug</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap sm:px-6 sm:py-4">Produk</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap sm:px-6 sm:py-4">Status</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap sm:px-6 sm:py-4">Dibuat</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap sm:px-6 sm:py-4">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200">
                    @foreach($categories as $category)
                        <x-admin.category-row :category="$category" />
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Table Footer / Pagination --}}
        <div class="flex flex-col items-center gap-3 border-t border-slate-200 px-4 py-4 text-center sm:px-6 sm:py-5 md:flex-row md:items-center md:justify-between md:text-left">
            <p class="text-xs font-medium text-slate-500 sm:text-sm">
                Menampilkan <span class="font-semibold text-slate-900">3</span> dari <span class="font-semibold text-slate-900">12</span> kategori
            </p>

            <x-admin.pagination />
        </div>
    </div>
</div>

@include('admin.categories.partials.create-category')
@include('admin.categories.partials.view-category')
@include('admin.categories.partials.delete-category')
@endsection