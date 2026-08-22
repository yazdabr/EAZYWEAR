@extends('admin.layouts.app')

@section('title','Kategori')
@section('page-title','Kategori')

@section('content')
<div class="space-y-4 sm:space-y-6 md:space-y-8">
    {{-- ================= HEADER KATEGORI ================= --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        {{-- ================= JUDUL ================= --}}
        <div class="shrink-0 flex items-center justify-between sm:block">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl md:text-3xl">
                    Kategori
                </h1>
                <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">
                    Kelola dan atur semua kategori produk untuk toko Anda.
                </p>
            </div>
        </div>

        {{-- ================= TOOLBAR ================= --}}
        <div class="w-full lg:w-auto">

            {{-- ================================================= --}}
            {{-- DESKTOP (lg:flex) --}}
            {{-- ================================================= --}}
            <div class="hidden lg:flex lg:items-center lg:gap-4">

                {{-- SEARCH DESKTOP --}}
                <div x-data="categorySearch()" class="relative w-[320px] shrink-0">
                    <x-admin.search-input
                        name="search"
                        placeholder="Cari kategori..."
                        autocomplete="off"
                        x-model="search"
                        @focus="searchCategories()"
                        @input.debounce.300ms="searchCategories()"
                    />

                    {{-- Dropdown Search Desktop --}}
                    <div
                        x-show="showResults && results.length"
                        x-cloak
                        @click.outside="showResults = false"
                        class="absolute left-0 right-0 z-[100] mt-2 max-h-60 overflow-y-auto overflow-x-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
                    >
                        <template x-for="category in results" :key="category.id">
                            <button
                                type="button"
                                @click="selectCategory(category)"
                                class="flex w-full items-center justify-between border-b border-slate-100 px-4 py-3 text-left transition hover:bg-slate-50 last:border-none"
                            >
                                <div class="min-w-0 pr-2">
                                    <p class="truncate text-sm font-semibold text-slate-800" x-text="category.name"></p>
                                    <p class="mt-0.5 truncate text-xs text-slate-400" x-text="category.slug"></p>
                                </div>
                                <x-heroicon-o-chevron-right class="h-4 w-4 shrink-0 text-slate-400"/>
                            </button>
                        </template>
                    </div>

                    {{-- Tidak Ditemukan --}}
                    <div
                        x-show="showResults && search.trim() && !results.length && !loading"
                        x-cloak
                        @click.outside="showResults = false"
                        class="absolute left-0 right-0 z-[100] mt-2 rounded-xl border border-slate-200 bg-white p-4 text-center shadow-xl"
                    >
                        <p class="text-sm font-medium text-slate-600">Kategori tidak ditemukan</p>
                        <p class="mt-1 text-xs text-slate-400">Coba gunakan nama kategori lain.</p>
                    </div>
                </div>

                {{-- RESET DESKTOP --}}
                <a
                    href="{{ route('admin.categories') }}"
                    title="Atur Ulang Filter"
                    class="inline-flex h-[50px] w-[50px] shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition-all duration-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 active:scale-[0.98]"
                >
                    <x-heroicon-o-arrow-path class="h-4 w-4"/>
                </a>

                {{-- TAMBAH KATEGORI DESKTOP --}}
                <button
                    type="button"
                    @click="$dispatch('open-create-category')"
                    class="inline-flex h-[50px] shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#AE7C18] px-6 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18] focus:ring-offset-2 active:scale-[0.98]"
                >
                    <x-heroicon-o-plus class="h-5 w-5"/>
                    <span>Tambah Kategori</span>
                </button>
            </div>

            {{-- ================================================= --}}
            {{-- MOBILE / TABLET OPTIMIZED (lg:hidden) --}}
            {{-- ================================================= --}}
            <div class="flex flex-col gap-2.5 lg:hidden">
                <div class="flex items-center gap-2">

                    {{-- SEARCH BAR COMPACT --}}
                    <div x-data="categorySearch()" class="relative min-w-0 flex-1">
                        <x-admin.search-input
                            name="search"
                            placeholder="Cari..."
                            autocomplete="off"
                            x-model="search"
                            @focus="searchCategories()"
                            @input.debounce.300ms="searchCategories()"
                        />

                        {{-- Dropdown Search Mobile --}}
                        <div
                            x-show="showResults && results.length"
                            x-cloak
                            @click.outside="showResults = false"
                            class="absolute left-0 right-0 z-[100] mt-2 max-h-56 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-xl"
                        >
                            <template x-for="category in results" :key="category.id">
                                <button
                                    type="button"
                                    @click="selectCategory(category)"
                                    class="flex w-full items-center justify-between border-b border-slate-100 px-3.5 py-2.5 text-left transition hover:bg-slate-50 last:border-none"
                                >
                                    <div class="min-w-0 pr-2">
                                        <p class="truncate text-xs font-semibold text-slate-800" x-text="category.name"></p>
                                        <p class="text-[10px] text-slate-400" x-text="category.slug"></p>
                                    </div>
                                    <x-heroicon-o-chevron-right class="h-3.5 w-3.5 shrink-0 text-slate-400"/>
                                </button>
                            </template>
                        </div>

                        {{-- Tidak ditemukan --}}
                        <div
                            x-show="showResults && search.trim() && !results.length && !loading"
                            x-cloak
                            @click.outside="showResults = false"
                            class="absolute left-0 right-0 z-[100] mt-2 rounded-xl border border-slate-200 bg-white p-4 text-center shadow-xl"
                        >
                            <p class="text-xs font-medium text-slate-600">Kategori tidak ditemukan</p>
                        </div>
                    </div>

                    {{-- RESET FILTER BUTTON --}}
                    <a
                        href="{{ route('admin.categories') }}"
                        title="Atur Ulang Filter"
                        class="inline-flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition active:scale-95"
                    >
                        <x-heroicon-o-arrow-path class="h-4 w-4"/>
                    </a>
                </div>
            </div>

        </div>
    </div>

    {{-- ================= STATS ================= --}}
    <div class="grid grid-cols-1 gap-3.5 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3">
        <x-admin.stat-card
            title="Total Kategori"
            :value="$totalCategories"
            :growth="$categoryGrowth">
            <x-slot:icon>
                <x-heroicon-o-squares-2x2 class="h-5 w-5 sm:h-6 sm:w-6"/>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Produk Aktif"
            :value="$activeProducts"
            color="bg-emerald-500"
            :growth="$activeProductGrowth">
            <x-slot:icon>
                <x-heroicon-o-cube class="h-5 w-5 sm:h-6 sm:w-6"/>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Aktif Sekarang"
            :value="$activeCategories"
            color="bg-amber-500"
            :growth="$activeCategoryGrowth"
            class="sm:col-span-2 lg:col-span-1">
            <x-slot:icon>
                <x-heroicon-o-eye class="h-5 w-5 sm:h-6 sm:w-6"/>
            </x-slot:icon>
        </x-admin.stat-card>
    </div>

    {{-- ================= DATA VIEW (TABEL & KARTU MOBILE) ================= --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl overflow-hidden">
        
        {{-- 1. DESKTOP/TABLET TABLE VIEW --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="whitespace-nowrap px-6 py-4">Kategori</th>
                        <th class="whitespace-nowrap px-6 py-4">Slug</th>
                        <th class="whitespace-nowrap px-6 py-4 text-center">Produk</th>
                        <th class="whitespace-nowrap px-6 py-4 text-center">Status</th>
                        <th class="whitespace-nowrap px-6 py-4 text-center">Dibuat</th>
                        <th class="whitespace-nowrap px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200">
                    @forelse($categories as $category)
                        <x-admin.category-row :category="$category"/>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <x-heroicon-o-squares-2x2 class="h-10 w-10 text-slate-300"/>
                                    <p class="mt-3 font-medium text-slate-600">Belum ada kategori</p>
                                    <p class="mt-1 text-sm text-slate-400">Data kategori akan muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 2. MOBILE CARD VIEW --}}
        <div class="block md:hidden divide-y divide-slate-100">
            @forelse($categories as $category)
                <div class="p-4 space-y-3 bg-white">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">
                                {{ $category->name }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">
                                {{ $category->description ?: 'Tidak ada deskripsi.' }}
                            </p>
                        </div>
                        
                        <x-admin.badge-status status="{{ $category->status ? 'Aktif' : 'Tidak Aktif' }}" />
                    </div>

                    <div class="flex items-center justify-between pt-2 text-xs border-t border-slate-50 text-slate-500">
                        <div class="space-y-1">
                            <div><span class="text-slate-400">Slug:</span> <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-700 font-mono">{{ $category->slug }}</code></div>
                            <div><span class="text-slate-400">Dibuat:</span> {{ $category->created_at?->format('d M Y') ?? '-' }}</div>
                        </div>

                        <div class="flex flex-col items-end gap-2">
                            <span class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                {{ $category->products_count ?? $category->products()->count() }} Produk
                            </span>
                        </div>
                    </div>

                    {{-- Tombol Aksi Mobile --}}
                    <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="
                                    $dispatch('open-view-category',{
                                        id:@js($category->id),
                                        name:@js($category->name),
                                        slug:@js($category->slug),
                                        description:@js($category->description),
                                        products:@js($category->products_count ?? $category->products()->count()),
                                        status:@js($category->status ? 'Aktif' : 'Tidak Aktif'),
                                        status_value:@js((bool)$category->status),
                                        created:@js($category->created_at?->format('d M Y'))
                                    });
                                "
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 active:bg-slate-100 active:scale-95"
                            >
                                <x-heroicon-o-eye class="h-3.5 w-3.5 text-slate-500"/>
                                <span>Lihat</span>
                            </button>

                            <button
                                type="button"
                                @click="
                                    $dispatch('open-edit-category',{
                                        id:@js($category->id),
                                        name:@js($category->name),
                                        slug:@js($category->slug),
                                        description:@js($category->description),
                                        status:@js((bool)$category->status),
                                        image:@js($category->image)
                                    });
                                "
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 active:bg-slate-100 active:scale-95"
                            >
                                <x-heroicon-o-pencil-square class="h-3.5 w-3.5 text-slate-500"/>
                                <span>Ubah</span>
                            </button>
                        </div>

                        <div>
                            <button
                                type="button"
                                @click="
                                    window.dispatchEvent(
                                        new CustomEvent('open-delete-category', {
                                            detail: {
                                                id: @js($category->id),
                                                name: @js($category->name)
                                            }
                                        })
                                    );
                                "
                                class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50/50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100/50 active:bg-red-100 active:scale-95"
                            >
                                <x-heroicon-o-trash class="h-3.5 w-3.5"/>
                                <span>Hapus</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-10 text-center">
                    <x-heroicon-o-squares-2x2 class="mx-auto h-9 w-9 text-slate-300"/>
                    <p class="mt-2 font-medium text-slate-600 text-sm">Belum ada kategori</p>
                    <p class="mt-0.5 text-xs text-slate-400">Data kategori akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

        {{-- ================= FOOTER ================= --}}
        <div class="flex flex-col items-center gap-3 border-t border-slate-200 px-4 py-4 text-center sm:px-6 sm:py-5 md:flex-row md:items-center md:justify-between md:text-left">
            <p class="text-xs font-medium text-slate-500 sm:text-sm">
                Menampilkan
                <span class="font-semibold text-slate-900">{{ $categories->firstItem() ?? 0 }}</span>
                sampai
                <span class="font-semibold text-slate-900">{{ $categories->lastItem() ?? 0 }}</span>
                dari
                <span class="font-semibold text-slate-900">{{ $categories->total() }}</span>
                kategori
            </p>

            <x-admin.pagination :paginator="$categories"/>
        </div>
    </div>
</div>

{{-- FLOATING ACTION BUTTON (FAB) KHUSUS MOBILE --}}
<button
    type="button"
    @click="$dispatch('open-create-category')"
    class="fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#AE7C18] text-white shadow-xl shadow-[#AE7C18]/40 transition hover:bg-[#96690F] active:scale-95 lg:hidden"
    aria-label="Tambah Kategori"
>
    <x-heroicon-o-plus class="h-6 w-6"/>
</button>

@include('admin.categories.partials.create-category')
@include('admin.categories.partials.view-category')
@include('admin.categories.partials.delete-category')
@endsection

@push('scripts')
<script>
function categorySearch() {
    return {
        search: @js(request('search', '')),
        results: [],
        showResults: false,
        loading: false,

        async searchCategories() {
            const keyword = this.search.trim();

            if (!keyword) {
                this.results = [];
                this.showResults = false;
                return;
            }

            this.loading = true;
            this.showResults = true;

            try {
                const response = await fetch(
                    '{{ route('admin.categories.search') }}?search=' +
                    encodeURIComponent(keyword),
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                if (!response.ok) {
                    throw new Error('Gagal mencari kategori.');
                }

                const data = await response.json();

                this.results = data.data || data;

            } catch (error) {
                console.error('Category Search Error:', error);
                this.results = [];
            } finally {
                this.loading = false;
            }
        },

        selectCategory(category) {
            this.search = category.name;
            this.results = [];
            this.showResults = false;

            const url = new URL(
                '{{ route('admin.categories') }}',
                window.location.origin
            );

            url.searchParams.set('search', category.name);

            window.location.href = url.toString();
        }
    };
}
</script>
@endpush