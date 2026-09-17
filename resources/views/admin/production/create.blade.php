
@extends('admin.layouts.app')

@section('title', 'Tambah Produksi')

@section('page-title', 'Tambah Produksi')

@section('content')

<div
    x-data="productionCreate()"
    class="space-y-6 pb-32 md:space-y-8 md:pb-10"
>

    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                Tambah Produksi
            </h2>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Buat rencana produksi baru untuk produk Eazywear.
            </p>
        </div>

        <a
            href="{{ route('admin.productions') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 sm:px-5 sm:py-3"
        >
            <x-heroicon-o-arrow-left class="h-5 w-5" />
            Kembali
        </a>

    </div>


    {{-- Pesan Error Umum --}}
    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
            <div class="flex items-start gap-3">
                <x-heroicon-o-exclamation-triangle
                    class="h-5 w-5 shrink-0 text-red-600"
                />

                <div>
                    <p class="text-sm font-bold text-red-700">
                        Terdapat kesalahan pada formulir
                    </p>

                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif


    {{-- Form Utama --}}
    <form
        action="{{ route('admin.productions.store') }}"
        method="POST"
        @submit="prepareSubmit"
        class="space-y-6 md:space-y-8"
    >

        @csrf


        {{-- Informasi Produksi --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">

            <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-5">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6" />
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900 sm:text-lg">
                        Informasi Produksi
                    </h3>

                    <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                        Lengkapi informasi dasar produksi.
                    </p>
                </div>

            </div>


            <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:gap-5 sm:p-6" x-data="{ productMode: '{{ old('product_mode', 'existing') }}' }">

                {{-- 1. PILIHAN JENIS PRODUK (Full Width) --}}
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">
                        Jenis Produk <span class="text-red-500">*</span>
                    </label>
                    
                    {{-- Radio Option Ringkas --}}
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        {{-- Produk yang sudah ada --}}
                        <label 
                            class="flex cursor-pointer items-center gap-3 rounded-xl border p-3.5 transition hover:border-amber-400"
                            :class="productMode === 'existing' ? 'border-[#AE7C18] bg-amber-50/50 ring-1 ring-[#AE7C18]' : 'border-slate-200 bg-white'"
                        >
                            <input 
                                type="radio" 
                                name="product_mode" 
                                value="existing" 
                                x-model="productMode"
                                class="h-4 w-4 text-[#AE7C18] focus:ring-[#AE7C18]"
                            >
                            <div class="text-xs sm:text-sm">
                                <span class="block font-semibold text-slate-800">Produk yang Sudah Ada</span>
                                <span class="text-slate-500 text-[11px] sm:text-xs">Tambah stok ke produk terdaftar</span>
                            </div>
                        </label>

                        {{-- Produk baru --}}
                        <label 
                            class="flex cursor-pointer items-center gap-3 rounded-xl border p-3.5 transition hover:border-amber-400"
                            :class="productMode === 'new' ? 'border-[#AE7C18] bg-amber-50/50 ring-1 ring-[#AE7C18]' : 'border-slate-200 bg-white'"
                        >
                            <input 
                                type="radio" 
                                name="product_mode" 
                                value="new" 
                                x-model="productMode"
                                class="h-4 w-4 text-[#AE7C18] focus:ring-[#AE7C18]"
                            >
                            <div class="text-xs sm:text-sm">
                                <span class="block font-semibold text-slate-800">Produk Baru</span>
                                <span class="text-slate-500 text-[11px] sm:text-xs">Buat item baru (status non-aktif)</span>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- 2. FORM DINAMIS (Berdasarkan Mode Produk) --}}
                
                {{-- Dynamic Mode: Existing --}}
                <div x-show="productMode === 'existing'" x-cloak class="sm:col-span-2">
                    <label for="product_id" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">
                        Pilih Produk <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="product_id" 
                        id="product_id" 
                        :required="productMode === 'existing'"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs sm:text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                    >
                        <option value="">-- Pilih Produk --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                                {{ $product->name }} @if($product->category) ({{ $product->category->name }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Dynamic Mode: New (Split 2 Kolom) --}}
                <div x-show="productMode === 'new'" x-cloak class="grid grid-cols-1 gap-4 sm:col-span-2 sm:grid-cols-2">
                    <div>
                        <label for="product_name" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">
                            Nama Produk Baru <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="product_name" 
                            id="product_name" 
                            value="{{ old('product_name') }}"
                            :required="productMode === 'new'"
                            placeholder="Contoh: Jersey Training 2026"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs sm:text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                        >
                    </div>

                    <div>
                        <label for="category_id_new" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">
                            Kategori Produk Baru <span class="text-red-500">*</span>
                        </label>
                        <select 
                            name="category_id" 
                            id="category_id_new" 
                            :required="productMode === 'new'"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs sm:text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                        >
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- 3. PERIODE (Ringkas 2 Kolom Sejajar) --}}
                <div class="sm:col-span-2">
                    <label for="period_type" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">
                        Jenis Periode <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="period_type" 
                        name="period_type" 
                        x-model="periodType" 
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs sm:text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                    >
                        <option value="">Pilih Periode</option>
                        <option value="daily">Harian</option>
                        <option value="weekly">Mingguan</option>
                        <option value="monthly">Bulanan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>

                <div>
                    <label for="period_start" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input 
                        id="period_start" 
                        type="date" 
                        name="period_start" 
                        value="{{ old('period_start') }}"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs sm:text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                    >
                </div>

                <div>
                    <label for="period_end" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">
                        Tanggal Selesai <span class="text-red-500">*</span>
                    </label>
                    <input 
                        id="period_end" 
                        type="date" 
                        name="period_end" 
                        value="{{ old('period_end') }}"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-xs sm:text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                    >
                </div>

            </div>

        </div>


        {{-- Jumlah Produksi --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">

            <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-5">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
                    <x-heroicon-o-cube class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6" />
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900 sm:text-lg">
                        Jumlah Produksi
                    </h3>

                    <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                        Tentukan jumlah produk yang akan diproduksi berdasarkan ukuran.
                    </p>
                </div>

            </div>


            <div class="space-y-5 p-4 sm:p-6">

                {{-- Tabel Ukuran --}}
                <div class="overflow-hidden rounded-2xl border border-slate-200">

                    <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-600 sm:text-sm">
                            Rincian Jumlah per Ukuran
                        </p>
                    </div>


                    <div class="divide-y divide-slate-100">

                        @forelse ($sizes as $size)
                            <div class="flex items-center justify-between gap-4 px-4 py-4">

                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $size->name }}
                                    </p>

                                    <p class="mt-0.5 text-xs text-slate-500">
                                        Jumlah produksi ukuran {{ $size->name }}
                                    </p>
                                </div>


                                <div class="w-28 shrink-0 sm:w-36">

                                    <label
                                        for="size_{{ $size->id }}"
                                        class="sr-only"
                                    >
                                        Jumlah ukuran {{ $size->name }}
                                    </label>

                                    <input
                                        id="size_{{ $size->id }}"
                                        type="number"
                                        name="sizes[{{ $size->id }}]"
                                        value="{{ old('sizes.' . $size->id, 0) }}"
                                        min="0"
                                        step="1"
                                        x-model.number="quantities[{{ $size->id }}]"
                                        @input="calculateTotal()"
                                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-center text-sm font-semibold text-slate-800 transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10"
                                    >

                                </div>

                            </div>
                        @empty

                            <div class="px-4 py-8 text-center text-sm text-slate-400">
                                Belum ada data ukuran yang tersedia.
                            </div>

                        @endforelse

                    </div>

                </div>


                {{-- Total Kuantitas --}}
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-[#AE7C18]/20 bg-[#AE7C18]/5 px-4 py-4 sm:px-5">

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            Total Produksi
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            Jumlah seluruh ukuran
                        </p>
                    </div>

                    <div class="text-right">
                        <p
                            class="text-2xl font-black text-[#AE7C18]"
                            x-text="totalQuantity"
                        >
                            0
                        </p>

                        <p class="text-xs font-medium text-slate-500">
                            pcs
                        </p>
                    </div>

                </div>


                {{-- Hidden Total Quantity --}}
                <input
                    type="hidden"
                    name="total_quantity"
                    :value="totalQuantity"
                >

                <p class="text-xs text-slate-500">
                    Pastikan jumlah produksi sudah sesuai dengan kebutuhan sebelum menyimpan data.
                </p>

            </div>

        </div>


        {{-- Catatan Produksi --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">

            <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-5">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
                    <x-heroicon-o-document-text class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6" />
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-900 sm:text-lg">
                        Catatan Produksi
                    </h3>

                    <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                        Tambahkan catatan jika diperlukan.
                    </p>
                </div>

            </div>


            <div class="p-4 sm:p-6">

                <label
                    for="notes"
                    class="mb-1.5 block text-xs font-semibold text-slate-700 sm:mb-2 sm:text-sm"
                >
                    Catatan
                </label>

                <textarea
                    id="notes"
                    name="notes"
                    rows="4"
                    placeholder="Contoh: Produksi untuk stok event atau permintaan khusus..."
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10"
                >{{ old('notes') }}</textarea>

            </div>

        </div>


        {{-- Tombol Aksi Desktop --}}
        <div class="hidden items-center justify-between border-t border-slate-200 pt-6 md:flex">

            <a
                href="{{ route('admin.productions') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
            >
                <x-heroicon-o-arrow-left class="h-5 w-5" />
                Kembali
            </a>


            <div class="flex items-center gap-3">

                <button
                    type="reset"
                    @click="resetForm"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                >
                    Atur Ulang
                </button>


                <button
                    type="submit"
                    :disabled="totalQuantity <= 0"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <x-heroicon-o-check-circle class="h-5 w-5" />
                    Simpan Produksi
                </button>

            </div>

        </div>


        {{-- Floating Footer Mobile --}}
        <div class="fixed bottom-0 left-0 right-0 z-[500] border-t border-slate-200/80 bg-white/95 px-4 py-4 shadow-[0_-8px_20px_rgba(0,0,0,0.08)] backdrop-blur-md md:hidden">

            <div class="flex items-center justify-between gap-3">

                <div class="min-w-0 flex-1">

                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        Total Produksi
                    </p>

                    <p class="truncate text-lg font-black text-[#AE7C18]">
                        <span x-text="totalQuantity"></span>
                        <span class="text-xs font-semibold text-slate-500">
                            pcs
                        </span>
                    </p>

                </div>


                <div class="flex shrink-0 items-center gap-2">

                    <button
                        type="reset"
                        @click="resetForm"
                        class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition active:scale-95"
                        title="Atur Ulang"
                    >
                        <x-heroicon-o-arrow-path class="h-6 w-6" />
                    </button>


                    <button
                        type="submit"
                        :disabled="totalQuantity <= 0"
                        class="inline-flex h-12 items-center justify-center gap-2 rounded-2xl bg-[#AE7C18] px-5 text-sm font-bold text-white shadow-lg shadow-[#AE7C18]/30 transition active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <x-heroicon-o-check-circle class="h-5 w-5" />
                        <span>Simpan</span>
                    </button>

                </div>

            </div>

        </div>

    </form>

</div>

@endsection


@push('scripts')

<script>
    function productionCreate() {
        return {
            periodType: @js(old('period_type', '')),

            quantities: @js(
                collect($sizes ?? [])
                    ->mapWithKeys(function ($size) {
                        return [
                            $size->id => (int) old('sizes.' . $size->id, 0)
                        ];
                    })
                    ->toArray()
            ),

            totalQuantity: 0,

            init() {
                this.calculateTotal();
            },

            calculateTotal() {
                this.totalQuantity = Object.values(this.quantities)
                    .reduce((total, quantity) => {
                        return total + Math.max(0, Number(quantity) || 0);
                    }, 0);
            },

            prepareSubmit(event) {
                this.calculateTotal();

                if (this.totalQuantity <= 0) {
                    event.preventDefault();

                    alert('Jumlah produksi harus lebih dari 0.');
                }
            },

            resetForm() {
                this.quantities = Object.fromEntries(
                    Object.keys(this.quantities).map((id) => [id, 0])
                );

                this.totalQuantity = 0;
            }
        };
    }
</script>

@endpush