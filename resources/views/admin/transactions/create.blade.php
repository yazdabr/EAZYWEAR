@extends('admin.layouts.app')

@section('title', 'Transaksi Baru')
@section('page-title', 'Transaksi Baru')

@section('content')
<div class="space-y-6 md:space-y-8">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">Transaksi Baru</h2>
            <p class="mt-1 text-sm text-slate-500 sm:mt-2 sm:text-base">Buat transaksi manual untuk pelanggan walk-in.</p>
        </div>
        <a href="{{ route('admin.transactions') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 sm:py-3 sm:text-base">
            <x-heroicon-o-arrow-left class="h-5 w-5"/>
            Kembali
        </a>
    </div>

    {{-- ================= CUSTOMER INFORMATION ================= --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        {{-- Header --}}
        <div class="flex items-center gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
                <x-heroicon-o-user class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6" />
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 sm:text-lg">Informasi Pelanggan</h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Isi detail pelanggan sebelum membuat transaksi.</p>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-4 sm:p-6">
            <div class="grid gap-4 sm:gap-6 md:grid-cols-2">
                {{-- Customer Name --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">
                        Nama Pelanggan <span class="text-red-500">*</span>
                    </label>
                    <x-admin.input placeholder="Masukkan nama pelanggan..." />
                </div>

                {{-- Phone --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Nomor Telepon</label>
                    <x-admin.input placeholder="08xxxxxxxxxx" />
                </div>

                {{-- Email --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                    <x-admin.input type="email" placeholder="customer@email.com" />
                </div>

                {{-- Transaction Date --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Tanggal Transaksi</label>
                    <input type="date" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">
                </div>
            </div>

            {{-- Notes --}}
            <div class="mt-4 sm:mt-6">
                <label class="mb-2 block text-sm font-medium text-slate-700">Catatan</label>
                <x-admin.textarea rows="4" placeholder="Catatan tambahan untuk transaksi ini..." />
            </div>
        </div>
    </div>

{{-- ================= PRODUCT SELECTION ================= --}}
    <div x-data="{ selectedProduct: true, qty: 1, price: 149000 }" class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        {{-- Header --}}
        <div class="flex items-center gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
                <x-heroicon-o-cube class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6" />
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 sm:text-lg">Pemilihan Produk</h3>
                <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Cari dan tambahkan produk ke transaksi ini.</p>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-4 sm:p-6 space-y-5">
            <div class="grid gap-4 sm:gap-5 md:grid-cols-2 lg:grid-cols-12">
                {{-- Product --}}
                <div class="md:col-span-2 lg:col-span-6">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Produk</label>
                    <x-admin.search-input placeholder="Cari nama produk atau SKU..." />
                </div>

                {{-- Size --}}
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Ukuran</label>
                    <x-admin.select>
                        <option>XS</option>
                        <option>S</option>
                        <option>M</option>
                        <option>L</option>
                        <option selected>XL</option>
                    </x-admin.select>
                </div>

                {{-- Qty --}}
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Jumlah</label>
                    <input type="number" min="1" x-model="qty" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">
                </div>

                {{-- Button --}}
                <div class="flex items-end md:col-span-2 lg:col-span-2">
                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-5 py-3 font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] disabled:opacity-50" :disabled="!selectedProduct">
                        <x-heroicon-o-plus class="h-5 w-5"/>
                        Tambah Item
                    </button>
                </div>
            </div>

            {{-- PREVIEW PRODUK YANG DIPILIH --}}
            <template x-if="selectedProduct">

                <div class="mt-4 rounded-2xl border border-[#AE7C18]/30 bg-[#AE7C18]/5 p-4 transition-all">

                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                        {{-- Info Produk --}}
                        <div class="flex items-center gap-3.5">

                            {{-- Gambar Produk --}}
                            <div class="relative flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white">

                                <img
                                    src="{{ asset('images/products/1.png') }}"
                                    alt="Apex Pro Jersey"
                                    class="h-full w-full object-cover">

                            </div>


                            {{-- Informasi --}}
                            <div>

                                <div class="flex items-center gap-2">

                                    <h4
                                        class="font-bold text-slate-900"
                                        x-text="selectedProduct.name">
                                    </h4>

                                    <span
                                        class="rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">

                                        Stok:
                                        <span x-text="selectedProduct.stock ?? 24"></span>

                                    </span>

                                </div>


                                <p class="mt-0.5 text-xs text-slate-500">

                                    SKU:

                                    <span
                                        class="font-medium text-slate-700"
                                        x-text="selectedProduct.sku">
                                    </span>

                                    <span class="mx-1">
                                        •
                                    </span>

                                    Kategori:

                                    <span
                                        class="font-medium text-slate-700"
                                        x-text="selectedProduct.category">
                                    </span>

                                </p>


                                <p
                                    class="mt-1 text-xs font-semibold text-[#AE7C18]">

                                    Rp
                                    <span
                                        x-text="Number(selectedProduct.price).toLocaleString('id-ID')">
                                    </span>

                                    <span class="font-normal text-slate-400">
                                        / pcs
                                    </span>

                                </p>

                            </div>

                        </div>


                        {{-- Ringkasan Item Terpilih --}}
                        <div
                            class="flex items-center justify-between gap-6 border-t border-[#AE7C18]/20 pt-3 sm:border-t-0 sm:pt-0">

                            <div class="text-right">

                                <p class="text-xs text-slate-500">
                                    Subtotal Item
                                </p>

                                <p
                                    class="text-base font-bold text-slate-900"

                                    x-text="'Rp ' + (
                                        Number(selectedProduct.price) * Number(qty)
                                    ).toLocaleString('id-ID')">

                                </p>

                            </div>


                            {{-- Batal Pilih --}}
                            <button
                                @click="selectedProduct = null"

                                type="button"

                                title="Batal Pilih"

                                class="rounded-xl border border-slate-200 bg-white p-2 text-slate-400 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600">

                                <x-heroicon-o-x-mark
                                    class="h-5 w-5" />

                            </button>

                        </div>

                    </div>

                </div>

            </template>
        </div>
    </div>

{{-- ================= SHOPPING CART ================= --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
            <div class="flex items-center gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
                    <x-heroicon-o-shopping-cart class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 sm:text-lg">Keranjang Belanja</h3>
                    <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Produk yang ditambahkan ke transaksi ini.</p>
                </div>
            </div>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 sm:text-sm">
                2 Item
            </span>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm sm:text-base">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3.5 sm:px-6 sm:py-4">Produk</th>
                        <th class="px-4 py-3.5 text-center sm:px-6 sm:py-4">Ukuran</th>
                        <th class="px-4 py-3.5 text-center sm:px-6 sm:py-4">Jumlah</th>
                        <th class="px-4 py-3.5 text-right sm:px-6 sm:py-4">Harga</th>
                        <th class="px-4 py-3.5 text-right sm:px-6 sm:py-4">Total</th>
                        <th class="px-4 py-3.5 text-center sm:px-6 sm:py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 whitespace-nowrap">
                    {{-- Row 1 --}}
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-4 py-4 sm:px-6 sm:py-5">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('images/products/1.png') }}" alt="Apex Pro Jersey" class="h-12 w-12 rounded-xl object-cover border border-slate-200 shrink-0">
                                <div>
                                    <p class="font-semibold text-slate-900">Apex Pro Jersey</p>
                                    <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">SKU : PRD-001</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center sm:px-6 sm:py-5">XL</td>
                        <td class="px-4 py-4 text-center sm:px-6 sm:py-5">2</td>
                        <td class="px-4 py-4 text-right sm:px-6 sm:py-5">Rp149.000</td>
                        <td class="px-4 py-4 text-right font-bold text-[#AE7C18] sm:px-6 sm:py-5">Rp298.000</td>
                        <td class="px-4 py-4 text-center sm:px-6 sm:py-5">
                            <button class="rounded-lg p-2 text-red-600 transition hover:bg-red-50">
                                <x-heroicon-o-trash class="h-5 w-5"/>
                            </button>
                        </td>
                    </tr>
                    {{-- Row 2 --}}
                    <tr class="transition hover:bg-slate-50">
                        <td class="px-4 py-4 sm:px-6 sm:py-5">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('images/products/1.png') }}" alt="Elite Jersey" class="h-12 w-12 rounded-xl object-cover border border-slate-200 shrink-0">
                                <div>
                                    <p class="font-semibold text-slate-900">Elite Jersey</p>
                                    <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">SKU : PRD-002</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center sm:px-6 sm:py-5">M</td>
                        <td class="px-4 py-4 text-center sm:px-6 sm:py-5">1</td>
                        <td class="px-4 py-4 text-right sm:px-6 sm:py-5">Rp199.000</td>
                        <td class="px-4 py-4 text-right font-bold text-[#AE7C18] sm:px-6 sm:py-5">Rp199.000</td>
                        <td class="px-4 py-4 text-center sm:px-6 sm:py-5">
                            <button class="rounded-lg p-2 text-red-600 transition hover:bg-red-50">
                                <x-heroicon-o-trash class="h-5 w-5"/>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="flex flex-col gap-2 border-t border-slate-200 bg-slate-50 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
            <div class="text-sm text-slate-500">
                Total Produk : <span class="font-semibold text-slate-700">2</span>
            </div>
            <div class="text-base font-bold text-[#AE7C18] sm:text-lg">
                Total Keseluruhan : Rp497.000
            </div>
        </div>
    </div>

    {{-- ================= PAYMENT & SUMMARY ================= --}}
    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Payment Method --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
            <div class="flex items-center gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
                    <x-heroicon-o-credit-card class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 sm:text-lg">Metode Pembayaran</h3>
                    <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Pilih cara pelanggan membayar.</p>
                </div>
            </div>

            <div class="space-y-4 p-4 sm:space-y-5 sm:p-6">
                {{-- Cash --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5 sm:p-4">
                    <input type="radio" name="payment" checked class="text-[#AE7C18] focus:ring-[#AE7C18]">
                    <div>
                        <p class="font-semibold text-slate-900">Tunai</p>
                        <p class="text-xs text-slate-500 sm:text-sm">Bayar menggunakan tunai.</p>
                    </div>
                </label>

                {{-- QRIS --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5 sm:p-4">
                    <input type="radio" name="payment" class="text-[#AE7C18] focus:ring-[#AE7C18]">
                    <div>
                        <p class="font-semibold text-slate-900">QRIS</p>
                        <p class="text-xs text-slate-500 sm:text-sm">Pindai kode QR.</p>
                    </div>
                </label>

                {{-- Transfer --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5 sm:p-4">
                    <input type="radio" name="payment" class="text-[#AE7C18] focus:ring-[#AE7C18]">
                    <div>
                        <p class="font-semibold text-slate-900">Transfer Bank</p>
                        <p class="text-xs text-slate-500 sm:text-sm">Transfer melalui rekening bank.</p>
                    </div>
                </label>

                {{-- EDC --}}
                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 p-3.5 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5 sm:p-4">
                    <input type="radio" name="payment" class="text-[#AE7C18] focus:ring-[#AE7C18]">
                    <div>
                        <p class="font-semibold text-slate-900">EDC</p>
                        <p class="text-xs text-slate-500 sm:text-sm">Kartu debit / kredit.</p>
                    </div>
                </label>

                {{-- Amount Paid --}}
                <div class="pt-2 sm:pt-3">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Jumlah Bayar</label>
                    <input type="number" placeholder="0" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">
                </div>

                {{-- Change --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Kembalian</label>
                    <input type="text" readonly value="Rp 0" class="w-full rounded-xl border border-slate-200 bg-slate-100 px-4 py-3 font-semibold text-slate-700">
                </div>
            </div>
        </div>

        {{-- ================= ORDER SUMMARY ================= --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
            {{-- Header --}}
            <div class="flex items-center gap-4 border-b border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-document-text class="h-5 w-5 text-emerald-600 sm:h-6 sm:w-6" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 sm:text-lg">Ringkasan Pesanan</h3>
                    <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Tinjau transaksi sebelum menyimpan.</p>
                </div>
            </div>

            {{-- Body --}}
            <div class="space-y-4 p-4 sm:space-y-5 sm:p-6">
                {{-- Subtotal --}}
                <div class="flex items-center justify-between text-sm sm:text-base">
                    <span class="text-slate-600">Subtotal</span>
                    <span class="font-semibold text-slate-900">Rp497.000</span>
                </div>

                {{-- Discount --}}
                <div class="flex items-center justify-between text-sm sm:text-base">
                    <span class="text-slate-600">Diskon</span>
                    <span class="font-semibold text-slate-900">Rp0</span>
                </div>

                {{-- Tax --}}
                <div class="flex items-center justify-between text-sm sm:text-base">
                    <span class="text-slate-600">Pajak</span>
                    <span class="font-semibold text-slate-900">Rp0</span>
                </div>

                {{-- Shipping --}}
                <div class="flex items-center justify-between text-sm sm:text-base">
                    <span class="text-slate-600">Ongkos Kirim</span>
                    <span class="font-semibold text-slate-900">Rp20.000</span>
                </div>

                <div class="border-t border-dashed border-slate-300"></div>

                {{-- Grand Total --}}
                <div class="flex items-center justify-between">
                    <span class="text-base font-bold text-slate-900 sm:text-lg">Total Keseluruhan</span>
                    <span class="text-xl font-bold text-[#AE7C18] sm:text-2xl">Rp517.000</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ================= ACTION BUTTON ================= --}}
    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        {{-- Back --}}
        <a href="{{ route('admin.transactions') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-6 py-3 font-medium text-slate-700 transition hover:bg-slate-100">
            <x-heroicon-o-arrow-left class="h-5 w-5"/>
            Kembali ke Transaksi
        </a>

        <div class="flex flex-col gap-3 sm:flex-row">
            {{-- Reset --}}
            <button type="reset" class="w-full rounded-xl border border-slate-300 bg-white px-6 py-3 font-medium text-slate-700 transition hover:bg-slate-100 sm:w-auto">
                Atur Ulang
            </button>

            {{-- Save --}}
            <button 
                @click="
                    $dispatch('toast', {
                        type: 'success',
                        title: 'Transaksi Dibuat',
                        message: 'Transaksi baru berhasil disimpan.'
                    });
                    setTimeout(() => {
                        window.location = '{{ route('admin.transactions') }}';
                    }, 900);
                " 
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-7 py-3 font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] sm:w-auto"
            >
                <x-heroicon-o-check-circle class="h-5 w-5"/>
                Simpan Transaksi
            </button>
        </div>
    </div>
</div>
@endsection