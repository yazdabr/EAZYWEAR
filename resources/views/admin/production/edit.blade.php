
@extends('admin.layouts.app')

@section('title', 'Ubah Produksi')

@section('page-title', 'Ubah Produksi')

@section('content')
    <div
        x-data="productionEdit()"
        x-init="init()"
        class="w-full max-w-none space-y-6"
    >
        {{-- Header --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a
                        href="{{ route('admin.productions') }}"
                        class="transition hover:text-[#AE7C18]"
                    >
                        Produksi
                    </a>

                    <x-heroicon-o-chevron-right class="h-4 w-4" />

                    <span class="text-slate-700">
                        Ubah Produksi
                    </span>
                </div>

                <h1 class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                    Ubah Data Produksi
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Perbarui informasi dan jumlah produksi.
                </p>
            </div>

            <a
                href="{{ route('admin.productions') }}"
                class="inline-flex w-fit items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
            >
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                Kembali
            </a>
        </div>

        {{-- Error Validation --}}
        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-exclamation-triangle class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />

                    <div>
                        <p class="text-sm font-semibold text-red-800">
                            Terdapat kesalahan pada formulir.
                        </p>

                        <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Form --}}
        <form
            action="{{ route('admin.productions.update', $production) }}"
            method="POST"
            @submit="prepareSubmit"
            class="space-y-6"
        >
            @csrf
            @method('PUT')

            {{-- Informasi Produksi --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-[#AE7C18]">
                            <x-heroicon-o-clipboard-document-list class="h-5 w-5" />
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-slate-900 sm:text-lg">
                                Informasi Produksi
                            </h2>

                            <p class="text-xs text-slate-500 sm:text-sm">
                                Informasi dasar data produksi.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 px-5 py-5 sm:grid-cols-2 sm:px-6">
                    {{-- Kode Produksi --}}
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">
                            Kode Produksi
                        </label>

                        <input
                            type="text"
                            value="{{ $production->production_code }}"
                            readonly
                            class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm font-medium text-slate-500 outline-none"
                        />

                        <p class="mt-1.5 text-xs text-slate-400">
                            Kode produksi tidak dapat diubah.
                        </p>
                    </div>

                    {{-- Jenis Periode --}}
                    <div>
                        <label
                            for="period_type"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Jenis Periode
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="period_type"
                            name="period_type"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#AE7C18] focus:ring-2 focus:ring-amber-100"
                        >
                            <option value="">Pilih Jenis Periode</option>

                            <option
                                value="daily"
                                @selected(old('period_type', $production->period_type) === 'daily')
                            >
                                Harian
                            </option>

                            <option
                                value="weekly"
                                @selected(old('period_type', $production->period_type) === 'weekly')
                            >
                                Mingguan
                            </option>

                            <option
                                value="monthly"
                                @selected(old('period_type', $production->period_type) === 'monthly')
                            >
                                Bulanan
                            </option>

                            <option
                                value="yearly"
                                @selected(old('period_type', $production->period_type) === 'yearly')
                            >
                                Tahunan
                            </option>
                        </select>
                    </div>

                    {{-- Tanggal Mulai --}}
                    <div>
                        <label
                            for="period_start"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Tanggal Mulai
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="period_start"
                            type="date"
                            name="period_start"
                            value="{{ old('period_start', optional($production->period_start)->format('Y-m-d') ?? $production->period_start) }}"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#AE7C18] focus:ring-2 focus:ring-amber-100"
                        />
                    </div>

                    {{-- Tanggal Selesai --}}
                    <div>
                        <label
                            for="period_end"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Tanggal Selesai
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="period_end"
                            type="date"
                            name="period_end"
                            value="{{ old('period_end', optional($production->period_end)->format('Y-m-d') ?? $production->period_end) }}"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#AE7C18] focus:ring-2 focus:ring-amber-100"
                        />
                    </div>
                </div>
            </div>

            {{-- Informasi Produk --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-[#AE7C18]">
                            <x-heroicon-o-cube class="h-5 w-5" />
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-slate-900 sm:text-lg">
                                Informasi Produk
                            </h2>

                            <p class="text-xs text-slate-500 sm:text-sm">
                                Perbarui nama dan kategori produk.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid gap-5 px-5 py-5 sm:grid-cols-2 sm:px-6">
                    {{-- Nama Produk --}}
                    <div>
                        <label
                            for="product_name"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Nama Produk
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="product_name"
                            type="text"
                            name="product_name"
                            value="{{ old('product_name', $production->product_name) }}"
                            maxlength="255"
                            required
                            placeholder="Masukkan nama produk"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#AE7C18] focus:ring-2 focus:ring-amber-100"
                        />
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <label
                            for="category_id"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Kategori Produk
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="category_id"
                            name="category_id"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#AE7C18] focus:ring-2 focus:ring-amber-100"
                        >
                            <option value="">Pilih Kategori</option>

                            @foreach ($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    @selected((string) old('category_id', $production->category_id) === (string) $category->id)
                                >
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Jumlah Produksi --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-[#AE7C18]">
                                <x-heroicon-o-archive-box class="h-5 w-5" />
                            </div>

                            <div>
                                <h2 class="text-base font-bold text-slate-900 sm:text-lg">
                                    Jumlah Produksi
                                </h2>

                                <p class="text-xs text-slate-500 sm:text-sm">
                                    Atur jumlah produksi berdasarkan ukuran.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-xl bg-slate-50 px-4 py-2.5">
                            <p class="text-xs font-medium text-slate-500">
                                Total Produksi
                            </p>

                            <p class="text-lg font-bold text-[#AE7C18]" x-text="totalQuantity">
                                0
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-5 sm:px-6">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($sizes as $size)
                            <div class="rounded-xl border border-slate-200 p-4 transition focus-within:border-amber-300 focus-within:ring-2 focus-within:ring-amber-50">
                                <div class="flex items-center justify-between gap-3">
                                    <label
                                        for="size_{{ $size->id }}"
                                        class="text-sm font-bold text-slate-700"
                                    >
                                        {{ $size->name }}
                                    </label>

                                    <span class="text-xs text-slate-400">
                                        pcs
                                    </span>
                                </div>

                                <input
                                    id="size_{{ $size->id }}"
                                    type="number"
                                    name="sizes[{{ $size->id }}]"
                                    min="0"
                                    step="1"
                                    value="{{ old('sizes.' . $size->id, $existingSizes[$size->id] ?? 0) }}"
                                    x-model.number="quantities[{{ $size->id }}]"
                                    @input="calculateTotal()"
                                    class="mt-3 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none transition focus:border-[#AE7C18] focus:ring-2 focus:ring-amber-100"
                                />
                            </div>
                        @endforeach
                    </div>

                    @error('sizes')
                        <p class="mt-3 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            {{-- Catatan --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-[#AE7C18]">
                            <x-heroicon-o-document-text class="h-5 w-5" />
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-slate-900 sm:text-lg">
                                Catatan
                            </h2>

                            <p class="text-xs text-slate-500 sm:text-sm">
                                Tambahkan catatan jika diperlukan.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-5 py-5 sm:px-6">
                    <label
                        for="notes"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Catatan Produksi
                    </label>

                    <textarea
                        id="notes"
                        name="notes"
                        rows="4"
                        placeholder="Tulis catatan produksi..."
                        class="w-full resize-y rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#AE7C18] focus:ring-2 focus:ring-amber-100"
                    >{{ old('notes', $production->notes) }}</textarea>
                </div>
            </div>

            {{-- Footer Action --}}
            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a
                    href="{{ route('admin.productions') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#956a14] focus:outline-none focus:ring-2 focus:ring-amber-200 focus:ring-offset-2"
                >
                    <x-heroicon-o-check class="h-5 w-5" />
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    <script>
        function productionEdit() {
            return {
                quantities: @js(
                    collect($sizes ?? [])
                        ->mapWithKeys(function ($size) use ($existingSizes) {
                            return [
                                $size->id => (int) old(
                                    'sizes.' . $size->id,
                                    $existingSizes[$size->id] ?? 0
                                ),
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
            };
        }
    </script>
@endsection