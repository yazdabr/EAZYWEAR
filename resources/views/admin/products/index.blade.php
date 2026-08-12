@extends('admin.layouts.app')

@section('title', 'Produk')
@section('page-title', 'Produk')

@section('content')

@if(session('success'))
<script>
    window.addEventListener('load', function () {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: {
                type: 'success',
                title: 'Berhasil',
                message: @js(session('success'))
            }
        }));
    });
</script>
@endif

@if(session('error'))
<script>
    window.addEventListener('load', function () {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: {
                type: 'error',
                title: 'Gagal',
                message: @js(session('error'))
            }
        }));
    });
</script>
@endif

<div class="space-y-5 sm:space-y-6 md:space-y-8">
    {{-- HEADER HALAMAN --}}
    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        {{-- JUDUL --}}
        <div class="shrink-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl md:text-3xl">
                Produk
            </h1>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Kelola semua produk yang tersedia di toko Anda.
            </p>
        </div>

        {{-- TOOLBAR --}}
        <div class="w-full lg:w-auto">
            {{-- DESKTOP --}}
            <div class="hidden lg:flex lg:items-center lg:gap-4">
                {{-- SEARCH --}}
                <div x-data="productSearch()" class="relative w-[320px] shrink-0">
                    <x-admin.search-input
                        name="search"
                        placeholder="Cari produk..."
                        autocomplete="off"
                        x-model="search"
                        @focus="searchProducts()"
                        @input.debounce.300ms="searchProducts()"
                    />

                    {{-- Dropdown Search --}}
                    <div
                        x-show="showResults && results.length"
                        x-cloak
                        @click.outside="showResults=false"
                        class="absolute left-0 right-0 z-[100] mt-2 max-h-60 overflow-y-auto overflow-x-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
                    >
                        <template x-for="product in results" :key="product.id">
                            <button
                                type="button"
                                @click="selectProduct(product)"
                                class="flex w-full items-center justify-between border-b border-slate-100 px-4 py-3 text-left transition hover:bg-slate-50 last:border-none"
                            >
                                <div class="min-w-0 pr-2">
                                    <p class="truncate text-sm font-semibold text-slate-800" x-text="product.name"></p>
                                    <p class="mt-0.5 text-xs text-slate-400" x-text="product.product_code"></p>
                                </div>
                                <x-heroicon-o-chevron-right class="h-4 w-4 shrink-0 text-slate-400"/>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- KATEGORI --}}
                <div class="w-[240px] shrink-0">
                    <form method="GET" action="{{ route('admin.products') }}" class="m-0 w-full p-0">
                        {{-- Pertahankan pencarian --}}
                        <input type="hidden" name="search" value="{{ request('search') }}">
                        <x-admin.filter-select
                            name="category"
                            placeholder="Semua Kategori"
                            :options="$categories->mapWithKeys(fn($category) => [
                                $category->id => $category->name
                            ])->toArray()"
                            :selected="request('category', '')"
                        />
                    </form>
                </div>

                {{-- RESET --}}
                <a
                    href="{{ route('admin.products') }}"
                    title="Atur Ulang Filter"
                    class="inline-flex h-[50px] w-[50px] shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition-all duration-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 active:scale-[0.98]"
                >
                    <x-heroicon-o-arrow-path class="h-4 w-4"/>
                </a>

                {{-- TAMBAH PRODUK --}}
                <button
                    type="button"
                    @click="$dispatch('open-create-product')"
                    class="inline-flex h-[50px] shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#AE7C18] px-6 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18] focus:ring-offset-2 active:scale-[0.98]"
                >
                    <x-heroicon-o-plus class="h-5 w-5"/>
                    <span>Tambah Produk</span>
                </button>
            </div>

            {{-- TABLET / MOBILE --}}
            <div class="flex flex-col gap-3 lg:hidden">
                {{-- SEARCH --}}
                <div x-data="productSearch()" class="relative w-full">
                    <x-admin.search-input
                        name="search"
                        placeholder="Cari produk..."
                        autocomplete="off"
                        x-model="search"
                        @focus="searchProducts()"
                        @input.debounce.300ms="searchProducts()"
                    />

                    {{-- Dropdown --}}
                    <div
                        x-show="showResults && results.length"
                        x-cloak
                        @click.outside="showResults=false"
                        class="absolute left-0 right-0 z-[100] mt-2 max-h-60 overflow-y-auto overflow-x-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
                    >
                        <template x-for="product in results" :key="product.id">
                            <button
                                type="button"
                                @click="selectProduct(product)"
                                class="flex w-full items-center justify-between border-b border-slate-100 px-4 py-3 text-left transition hover:bg-slate-50 last:border-none"
                            >
                                <div class="min-w-0 pr-2">
                                    <p class="truncate text-sm font-semibold text-slate-800" x-text="product.name"></p>
                                    <p class="mt-0.5 text-xs text-slate-400" x-text="product.product_code"></p>
                                </div>
                                <x-heroicon-o-chevron-right class="h-4 w-4 shrink-0 text-slate-400"/>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- KATEGORI + RESET --}}
                <div class="flex items-center gap-3">
                    <div class="min-w-0 flex-1">
                        <form method="GET" action="{{ route('admin.products') }}" class="m-0 w-full p-0">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <x-admin.filter-select
                                name="category"
                                placeholder="Semua Kategori"
                                :options="$categories->mapWithKeys(fn($category) => [
                                    $category->id => $category->name
                                ])->toArray()"
                                :selected="request('category')"
                                onchange="this.form.submit()"
                            />
                        </form>
                    </div>

                    <a
                        href="{{ route('admin.products') }}"
                        title="Atur Ulang Filter"
                        class="inline-flex h-[50px] w-[50px] shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition-all duration-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-800 active:scale-[0.98]"
                    >
                        <x-heroicon-o-arrow-path class="h-4 w-4"/>
                    </a>
                </div>

                {{-- TAMBAH PRODUK --}}
                <button
                    type="button"
                    @click="$dispatch('open-create-product')"
                    class="inline-flex h-[50px] w-full items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-5 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] active:scale-[0.98]"
                >
                    <x-heroicon-o-plus class="h-5 w-5"/>
                    <span>Tambah Produk</span>
                </button>
            </div>
        </div>
    </div>

    {{-- TABEL / KARTU PRODUK --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        <x-admin.product-table :products="$products" />
    </div>
</div>

{{-- MODAL / DRAWER --}}
@include('admin.products.partials.create-product', [
    'categories' => $categories,
    'sizes' => $sizes,
    'nextProductCode' => $nextProductCode,
])

@include('admin.products.partials.delete-product')
@include('admin.products.partials.view-product')

@endsection

{{-- JAVASCRIPT --}}
@push('scripts')
<script>
function productSearch() {
    return {
        search: @js(request('search', '')),
        results: [],
        showResults: false,

        async searchProducts() {
            const keyword = this.search.trim();

            if (!keyword) {
                this.results = [];
                this.showResults = false;
                return;
            }

            try {
                const response = await fetch(
                    '{{ route('admin.products.search') }}?q=' + encodeURIComponent(keyword),
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                if (!response.ok) {
                    throw new Error('Gagal mencari produk.');
                }

                this.results = await response.json();
                this.showResults = true;

            } catch (error) {
                console.error(error);
                this.results = [];
                this.showResults = false;
            }
        },

        selectProduct(product) {
            this.search = product.name;
            this.showResults = false;

            const url = new URL(window.location.href);
            url.searchParams.set('search', product.name);
            url.searchParams.delete('page');

            window.location.href = url.toString();
        }
    };
}
</script>
@endpush