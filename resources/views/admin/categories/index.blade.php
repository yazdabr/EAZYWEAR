@extends('admin.layouts.app')

@section('title', 'Categories')
@section('page-title', 'Categories')

@section('content')

<div class="space-y-8">

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-3xl font-bold text-slate-900">Categories</h2>
            <p class="mt-2 text-sm text-slate-500">Manage and organize all product categories for your store.</p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            {{-- Search --}}
            <div class="w-full sm:w-80">
                <x-admin.search-input placeholder="Search categories..." />
            </div>

            {{-- Add Category Button --}}
            <button
                @click="$dispatch('open-create-category')"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 py-3 font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18] focus:ring-offset-2 active:scale-[0.98]">
                <x-heroicon-o-plus class="h-5 w-5" />
                <span>Add Category</span>
            </button>
        </div>
    </div>

    {{-- ================= STATS ================= --}}
    <div class="grid gap-6 lg:grid-cols-3">
        <x-admin.stat-card
            title="Total Categories"
            value="12"
            growth="+2">
            <x-slot:icon>
                <x-heroicon-o-squares-2x2 class="h-7 w-7" />
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Live Products"
            value="482"
            color="bg-emerald-500"
            growth="+18">
            <x-slot:icon>
                <x-heroicon-o-cube class="h-7 w-7" />
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Active Now"
            value="8"
            color="bg-amber-500"
            growth="+1">
            <x-slot:icon>
                <x-heroicon-o-eye class="h-7 w-7" />
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
            'status' => 'Active',
            'created' => '10 May 2024',
        ],
        [
            'name' => 'Basketball',
            'description' => 'Basketball Jersey',
            'slug' => 'basketball',
            'products' => 45,
            'status' => 'Active',
            'created' => '12 May 2024',
        ],
        [
            'name' => 'Esports',
            'description' => 'Gaming Jersey',
            'slug' => 'esports',
            'products' => 28,
            'status' => 'Active',
            'created' => '08 May 2024',
        ],
    ];
    @endphp

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        {{-- Responsive Table Container --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px]">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{-- Checkbox --}}
                        <th class="w-16 px-6 py-4">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded border-slate-300 text-[#AE7C18] transition focus:ring-[#AE7C18]">
                        </th>

                        <th class="px-6 py-4 whitespace-nowrap">Category</th>
                        <th class="px-6 py-4 whitespace-nowrap">Slug</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Products</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Created</th>
                        <th class="px-6 py-4 text-center whitespace-nowrap">Action</th>
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
        <div class="flex flex-col gap-4 border-t border-slate-200 px-6 py-5 md:flex-row md:items-center md:justify-between">
            <p class="text-sm font-medium text-slate-500">
                Showing <span class="font-semibold text-slate-900">3</span> of <span class="font-semibold text-slate-900">12</span> categories
            </p>

            <x-admin.pagination />
        </div>

    </div>

</div>

@include('admin.categories.partials.create-category')
@include('admin.categories.partials.view-category')
@include('admin.categories.partials.delete-category')

@endsection