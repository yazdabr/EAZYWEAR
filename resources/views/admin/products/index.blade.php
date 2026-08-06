@extends('admin.layouts.app')

@section('title', 'Products')

@section('page-title', 'Products')

@section('content')

<div class="space-y-8">

    {{-- ================= PAGE HEADER ================= --}}
    <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

        <div>

            <h1 class="text-3xl font-bold text-slate-900">

                Products

            </h1>

            <p class="mt-2 text-slate-500">

                Manage all products available in your store.

            </p>

        </div>

        <div class="flex flex-col gap-3 lg:flex-row">

            <x-admin.search-input />

            <x-admin.filter-select
                placeholder="All Categories"
                :options="[
                    'All Categories',
                    'Football',
                    'Basketball',
                    'Futsal',
                    'Volleyball',
                    'Running',
                    'Cycling',
                ]"/>

            <button

                @click="$dispatch('open-create-product')"

                class="inline-flex h-[50px] items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F]">

                <x-heroicon-o-plus class="h-5 w-5"/>

                <span>Add Product</span>

            </button>

        </div>

    </div>

    {{-- ================= PRODUCT TABLE ================= --}}
    <x-admin.product-table />

</div>

@include('admin.products.partials.create-product')

@include('admin.products.partials.delete-product')

@include('admin.products.partials.view-product')

@endsection