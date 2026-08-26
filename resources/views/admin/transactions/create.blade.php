@extends('admin.layouts.app')
@section('title','Transaksi Baru')
@section('page-title','Transaksi Baru')

@section('content')
{{-- Menambahkan pb-36 di mobile agar konten bawah tidak pernah tertutup sticky footer --}}
<div x-data="transactionCreate()" class="space-y-6 md:space-y-8 pb-36 md:pb-10">
  {{-- Header --}}
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h2 class="text-xl font-bold text-slate-900 sm:text-3xl">Transaksi Baru</h2>
      <p class="mt-1 text-xs text-slate-500 sm:mt-2 sm:text-base">Buat transaksi manual untuk pelanggan.</p>
    </div>
    <a href="{{ route('admin.transactions') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 sm:px-5 sm:py-3">
      <x-heroicon-o-arrow-left class="h-5 w-5"/> Kembali
    </a>
  </div>

  {{-- Informasi Pelanggan --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl overflow-hidden">
    <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-5">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
        <x-heroicon-o-user class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6"/>
      </div>
      <div>
        <h3 class="text-base font-bold text-slate-900 sm:text-lg">Informasi Pelanggan</h3>
        <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Isi informasi pelanggan transaksi.</p>
      </div>
    </div>
    <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:gap-6 sm:p-6">
      <div>
        <label class="mb-1.5 block text-xs font-semibold text-slate-700 sm:mb-2 sm:text-sm">Nama Pelanggan <span class="text-red-500">*</span></label>
        <input x-model="customer.name" type="text" placeholder="Masukkan nama pelanggan..." class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">
      </div>
      <div>
        <label class="mb-1.5 block text-xs font-semibold text-slate-700 sm:mb-2 sm:text-sm">Nomor Telepon</label>
        <input x-model="customer.phone" type="text" placeholder="08xxxxxxxxxx" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">
      </div>
      <div>
        <label class="mb-1.5 block text-xs font-semibold text-slate-700 sm:mb-2 sm:text-sm">Email</label>
        <input x-model="customer.email" type="email" placeholder="customer@email.com" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">
      </div>
      <div>
        <label class="mb-1.5 block text-xs font-semibold text-slate-700 sm:mb-2 sm:text-sm">Tanggal Transaksi</label>
        <input x-model="transactionDate" type="date" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">
      </div>
    </div>
  </div>

  {{-- Pemilihan Produk --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
    <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-5">
      <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
        <x-heroicon-o-cube class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6"/>
      </div>
      <div>
        <h3 class="text-base font-bold text-slate-900 sm:text-lg">Pemilihan Produk</h3>
        <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Cari dan pilih varian produk.</p>
      </div>
    </div>
    <div class="space-y-4 p-4 sm:space-y-5 sm:p-6">
      <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
        {{-- SEARCH PRODUK --}}
        <div class="relative lg:col-span-6">
          <label class="mb-1.5 block text-xs font-semibold text-slate-700 sm:mb-2 sm:text-sm">Cari Produk</label>
          <div class="relative w-full">
            <x-admin.search-input name="product_search" placeholder="Ketik nama produk..." autocomplete="off" x-model="productSearch" @input.debounce.300ms="searchProducts()" />
            
            {{-- Dropdown Hasil Pencarian --}}
            <div x-show="showProductResults && productSearch.trim().length >= 2" x-cloak @click.outside="showProductResults = false" class="absolute left-0 right-0 top-full z-[9999] mt-2 max-h-72 overflow-y-auto overflow-x-hidden rounded-xl border border-slate-200 bg-white shadow-2xl">
              <template x-if="productSearching">
                <div class="flex items-center gap-3 px-4 py-4 text-sm text-slate-400">
                  <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25" />
                    <path d="M21 12a9 9 0 0 1-9 9" stroke="currentColor" stroke-width="3" />
                  </svg>
                  Mencari produk...
                </div>
              </template>
              
              <template x-for="variant in productResults" :key="variant.id">
                <button type="button" @click="selectProduct(variant)" class="flex w-full items-center gap-3 border-b border-slate-100 px-4 py-3 text-left transition hover:bg-slate-50 last:border-none">
                  <div class="h-11 w-11 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-white">
                    <img :src="variant.image" :alt="variant.product?.name ?? '-'" class="h-full w-full object-cover">
                  </div>
                  <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-slate-800" x-text="variant.product?.name ?? '-'"></p>
                    <p class="mt-0.5 truncate text-xs text-slate-400">
                      Ukuran: <span x-text="variant.size?.name ?? '-'"></span>
                      · Stok: <span x-text="variant.stock"></span>
                    </p>
                  </div>
                  <div x-show="isSelected(variant.id)" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-[#AE7C18] text-white">
                    <x-heroicon-o-check class="h-4 w-4"/>
                  </div>
                  <x-heroicon-o-chevron-right x-show="!isSelected(variant.id)" class="h-4 w-4 shrink-0 text-slate-400" />
                </button>
              </template>
              
              <template x-if="!productSearching && productSearch.trim().length >= 2 && productResults.length === 0">
                <div class="px-4 py-5 text-center">
                  <p class="text-sm font-medium text-slate-500">Produk tidak ditemukan.</p>
                  {{-- <p class="mt-1 text-xs text-slate-400">Coba gunakan nama atau SKU lain.</p> --}}
                </div>
              </template>
            </div>
          </div>
        </div>

        {{-- STOK DAN JUMLAH --}}
        <div class="grid grid-cols-2 gap-3 lg:contents">
          <div class="lg:col-span-2">
            <label class="mb-1.5 block text-xs font-semibold text-slate-700 sm:mb-2 sm:text-sm">Stok Tersedia</label>
            <input type="text" readonly :value="selectedProducts.length ? selectedProducts[selectedProducts.length-1].stock : 0" class="h-[46px] sm:h-[50px] w-full rounded-xl border border-slate-200 bg-slate-100 px-4 text-sm font-medium text-slate-700">
          </div>
          <div class="lg:col-span-2">
            <label class="mb-1.5 block text-xs font-semibold text-slate-700 sm:mb-2 sm:text-sm">Jumlah</label>
            <input x-model.number="qty" type="number" min="1" class="h-[46px] sm:h-[50px] w-full rounded-xl border border-slate-300 bg-white px-4 text-sm focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">
          </div>
        </div>

        {{-- TOMBOL TAMBAH --}}
        <div class="flex items-end lg:col-span-2">
          <button type="button" @click="addSelectedProducts()" :disabled="selectedProducts.length === 0" class="inline-flex h-[46px] sm:h-[50px] w-full items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] disabled:cursor-not-allowed disabled:opacity-50">
            <x-heroicon-o-plus class="h-5 w-5" /> <span>Tambah</span>
          </button>
        </div>
      </div>

      {{-- DAFTAR PRODUK YANG DIPILIH SEMENTARA --}}
      <div x-show="selectedProducts.length" x-cloak class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
          <div>
            <p class="text-xs sm:text-sm font-bold text-slate-900">Produk Dipilih</p>
            <p class="mt-0.5 text-[11px] sm:text-xs text-slate-500">Tekan Tambah untuk memasukkan ke keranjang.</p>
          </div>
          <span class="inline-flex shrink-0 whitespace-nowrap rounded-full bg-[#AE7C18]/10 px-3 py-1 text-xs font-semibold text-[#AE7C18]" x-text="selectedProducts.length + ' Produk'"></span>
        </div>
        <div class="divide-y divide-slate-100">
          <template x-for="(item,index) in selectedProducts" :key="item.id">
            <div class="flex items-center gap-3 px-4 py-3">
              <div class="h-10 w-10 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-white sm:h-14 sm:w-14">
                <img :src="item.image" :alt="item.product?.name" class="h-full w-full object-cover">
              </div>
              <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-semibold text-slate-900 sm:text-sm" x-text="item.product?.name"></p>
                <p class="mt-0.5 text-[11px] sm:text-xs text-slate-500">
                  Ukuran: <span class="font-medium text-slate-700" x-text="item.size?.name"></span>
                  · Stok: <span class="font-medium text-slate-700" x-text="item.stock"></span>
                </p>
              </div>
              <div class="text-right">
                <p class="text-xs font-bold text-[#AE7C18] sm:text-sm">Rp <span x-text="formatNumber(item.price)"></span></p>
              </div>
              <button type="button" @click="removeSelectedProduct(index)" class="rounded-lg p-1.5 text-red-500 transition hover:bg-red-50" title="Hapus">
                <x-heroicon-o-trash class="h-5 w-5"/>
              </button>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>

  {{-- Keranjang Belanja --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl overflow-hidden">
    <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3.5 sm:px-6 sm:py-5">
      <div class="flex items-center gap-3 sm:gap-4">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
          <x-heroicon-o-shopping-cart class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6"/>
        </div>
        <div>
          <h3 class="text-base font-bold text-slate-900 sm:text-lg">Keranjang Belanja</h3>
        </div>
      </div>
      <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600" x-text="cart.length + ' Item'"></span>
    </div>

    {{-- Mobile View --}}
    <div class="block md:hidden p-3 space-y-3">
      <template x-if="cart.length === 0">
        <div class="py-8 text-center text-xs text-slate-400">Belum ada produk di keranjang.</div>
      </template>
      <template x-for="(item, index) in cart" :key="item.variant_id">
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm space-y-3">
          <div class="flex items-start justify-between gap-3">
            <div class="flex min-w-0 items-center gap-3">
              <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                <img :src="item.image" :alt="item.product_name" class="h-full w-full object-cover" loading="lazy">
              </div>
              <div class="min-w-0">
                <p class="text-xs font-bold text-slate-900 truncate" x-text="item.product_name"></p>
                {{-- <p class="mt-0.5 text-[11px] text-slate-500">SKU: <span class="font-medium text-slate-700" x-text="item.sku"></span></p> --}}
                <div class="mt-1 flex items-center gap-2 text-[11px] text-slate-600">
                  <span class="rounded bg-slate-100 px-1.5 py-0.5 font-medium">Ukuran: <strong class="text-slate-800" x-text="item.size"></strong></span>
                </div>
              </div>
            </div>
            <button type="button" @click="removeItem(index)" class="rounded-xl p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 transition" title="Hapus produk">
              <x-heroicon-o-trash class="h-5 w-5"/>
            </button>
          </div>

          <div class="flex items-center justify-between border-t border-slate-100 pt-3">
            <div class="flex items-center rounded-xl border border-slate-200 bg-slate-50 p-0.5">
              <button type="button" @click="if(item.qty > 1) { item.qty--; updateQty(index); }" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-slate-600 shadow-sm active:bg-slate-100 disabled:opacity-40" :disabled="item.qty <= 1">
                <x-heroicon-o-minus class="h-3.5 w-3.5" />
              </button>
              <input x-model.number="item.qty" @change="updateQty(index)" type="number" min="1" :max="item.stock" class="w-10 border-0 bg-transparent text-center text-xs font-bold text-slate-800 focus:ring-0 p-0">
              <button type="button" @click="if(item.qty < item.stock) { item.qty++; updateQty(index); }" class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-slate-600 shadow-sm active:bg-slate-100 disabled:opacity-40" :disabled="item.qty >= item.stock">
                <x-heroicon-o-plus class="h-3.5 w-3.5" />
              </button>
            </div>

            <div class="text-right">
              <span class="text-[10px] text-slate-400 block uppercase font-medium">Subtotal</span>
              <span class="text-sm font-bold text-[#AE7C18]">Rp <span x-text="formatNumber(item.price * item.qty)"></span></span>
            </div>
          </div>
        </div>
      </template>
    </div>

    {{-- Desktop View: Table-based --}}
    <div class="hidden md:block w-full overflow-x-auto">
      <table class="w-full min-w-[700px] text-sm">
        <thead class="border-b border-slate-200 bg-slate-50">
          <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
            <th class="px-6 py-4">Produk</th>
            <th class="px-6 py-4 text-center">Ukuran</th>
            <th class="px-6 py-4 text-center">Jumlah</th>
            <th class="px-6 py-4 text-right">Harga</th>
            <th class="px-6 py-4 text-right">Total</th>
            <th class="px-6 py-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <template x-if="cart.length === 0">
            <tr>
              <td colspan="6" class="px-6 py-12 text-center text-sm text-slate-400">Belum ada produk di keranjang.</td>
            </tr>
          </template>
          <template x-for="(item,index) in cart" :key="item.variant_id">
            <tr class="hover:bg-slate-50">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <img :src="item.image" :alt="item.product_name" class="h-full w-full object-cover" loading="lazy">
                  </div>
                  <div class="min-w-0">
                    <p class="font-semibold text-slate-900 truncate" x-text="item.product_name"></p>
                    {{-- <p class="text-xs text-slate-500">SKU: <span x-text="item.sku"></span></p> --}}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-center" x-text="item.size"></td>
              <td class="px-6 py-4 text-center">
                <input x-model.number="item.qty" @change="updateQty(index)" type="number" min="1" :max="item.stock" class="w-20 rounded-lg border border-slate-300 px-2 py-1.5 text-center">
              </td>
              <td class="px-6 py-4 text-right">Rp <span x-text="formatNumber(item.price)"></span></td>
              <td class="px-6 py-4 text-right font-bold text-[#AE7C18]">Rp <span x-text="formatNumber(item.price * item.qty)"></span></td>
              <td class="px-6 py-4 text-center">
                <button type="button" @click="removeItem(index)" class="rounded-lg p-2 text-red-600 transition hover:bg-red-50">
                  <x-heroicon-o-trash class="h-5 w-5"/>
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="flex flex-col gap-2 border-t border-slate-200 bg-slate-50 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
      <span class="text-xs text-slate-500 sm:text-sm">
        Total Produk: <span class="font-semibold text-slate-700" x-text="totalQty"></span>
      </span>
      <span class="text-sm font-bold text-[#AE7C18] sm:text-lg">
        Total: Rp <span x-text="formatNumber(subtotal)"></span>
      </span>
    </div>
  </div>

  {{-- Metode Pembayaran & Ringkasan --}}
  <div class="grid gap-6 lg:grid-cols-2">
    {{-- Metode Pembayaran --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl overflow-hidden">
      <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-5">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
          <x-heroicon-o-credit-card class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6"/>
        </div>
        <div>
          <h3 class="text-base font-bold text-slate-900 sm:text-lg">Metode Pembayaran</h3>
        </div>
      </div>
      <div class="space-y-3 p-4 sm:p-6">
        <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-2 sm:gap-3">
          <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 p-3 transition hover:border-[#AE7C18]">
            <input type="radio" value="CASH" x-model="paymentMethod" class="text-[#AE7C18] focus:ring-[#AE7C18]">
            <div>
              <p class="font-semibold text-slate-900 text-xs sm:text-sm">Tunai</p>
              <p class="text-[10px] sm:text-[11px] text-slate-500">Uang tunai</p>
            </div>
          </label>
          <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 p-3 transition hover:border-[#AE7C18]">
            <input type="radio" value="QRIS" x-model="paymentMethod" class="text-[#AE7C18] focus:ring-[#AE7C18]">
            <div>
              <p class="font-semibold text-slate-900 text-xs sm:text-sm">QRIS</p>
              <p class="text-[10px] sm:text-[11px] text-slate-500">Scan QR</p>
            </div>
          </label>
          <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 p-3 transition hover:border-[#AE7C18]">
            <input type="radio" value="TRANSFER" x-model="paymentMethod" class="text-[#AE7C18] focus:ring-[#AE7C18]">
            <div>
              <p class="font-semibold text-slate-900 text-xs sm:text-sm">Transfer</p>
              <p class="text-[10px] sm:text-[11px] text-slate-500">Bank</p>
            </div>
          </label>
          <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 p-3 transition hover:border-[#AE7C18]">
            <input type="radio" value="EDC" x-model="paymentMethod" class="text-[#AE7C18] focus:ring-[#AE7C18]">
            <div>
              <p class="font-semibold text-slate-900 text-xs sm:text-sm">EDC</p>
              <p class="text-[10px] sm:text-[11px] text-slate-500">Kartu</p>
            </div>
          </label>
        </div>
        
        <div class="mt-4 border-t border-slate-200 pt-4 sm:mt-6 sm:pt-6">
          <label class="mb-1.5 block text-xs font-semibold text-slate-700 sm:mb-2 sm:text-sm">Sumber Transaksi</label>
          <select x-model="source" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-xs sm:text-sm font-medium text-slate-700 transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10">
            <option value="Android POS">Android POS</option>
            <option value="Smart EDC">Smart EDC</option>
          </select>
        </div>
      </div>
    </div>

    {{-- Ringkasan Pesanan --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl overflow-hidden">
      <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-5">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 sm:h-12 sm:w-12">
          <x-heroicon-o-document-text class="h-5 w-5 text-emerald-600 sm:h-6 sm:w-6"/>
        </div>
        <div>
          <h3 class="text-base font-bold text-slate-900 sm:text-lg">Ringkasan Pesanan</h3>
          <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Periksa total transaksi.</p>
        </div>
      </div>
      <div class="space-y-4 p-4 sm:p-6 text-xs sm:text-sm">
        <div class="flex justify-between items-center">
          <span class="text-slate-600">Subtotal</span>
          <span class="font-semibold text-slate-900">Rp <span x-text="formatNumber(subtotal)"></span></span>
        </div>
        <div class="flex items-center justify-between gap-4">
          <span class="text-slate-600">Diskon</span>
          <input x-model.number="discount" type="number" min="0" class="w-28 sm:w-32 rounded-lg border border-slate-300 px-3 py-2 text-right text-xs sm:text-sm">
        </div>
        <div class="flex items-center justify-between gap-4">
          <span class="text-slate-600">Ongkos Kirim</span>
          <input x-model.number="shipping" type="number" min="0" class="w-28 sm:w-32 rounded-lg border border-slate-300 px-3 py-2 text-right text-xs sm:text-sm">
        </div>
        <div class="border-t border-dashed border-slate-300"></div>
        <div class="flex items-center justify-between pt-1">
          <span class="text-sm font-bold text-slate-900 sm:text-lg">Total Keseluruhan</span>
          <span class="text-lg font-bold text-[#AE7C18] sm:text-2xl">
            Rp <span x-text="formatNumber(grandTotal)"></span>
          </span>
        </div>
      </div>
    </div>
  </div>

  {{-- Tombol Aksi Bawah Standard (Desktop) --}}
  <div class="hidden md:flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
    <a href="{{ route('admin.transactions') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-xs sm:text-sm font-medium text-slate-700 transition hover:bg-slate-100">
      <x-heroicon-o-arrow-left class="h-4 w-4 sm:h-5 sm:w-5" /> Kembali
    </a>
    <div class="flex w-full flex-col gap-2.5 sm:w-auto sm:flex-row">
      <button type="button" @click="resetForm()" class="w-full rounded-xl border border-slate-300 bg-white px-5 py-3 text-xs sm:text-sm font-medium text-slate-700 transition hover:bg-slate-100 sm:w-auto">
        Atur Ulang
      </button>
      <button type="button" @click="submitForm()" :disabled="loading || cart.length === 0 || !customer.name" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 py-3 text-xs sm:text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto">
        <x-heroicon-o-check-circle class="h-4 w-4 sm:h-5 sm:w-5" />
        Simpan Transaksi
      </button>
    </div>
  </div>

  {{-- Diperbesar: Floating Footer khusus Mobile --}}
  <div class="fixed bottom-0 left-0 right-0 z-[500] border-t border-slate-200/80 bg-white/95 px-4 py-4 backdrop-blur-md md:hidden shadow-[0_-8px_20px_rgba(0,0,0,0.08)]">
    <div class="flex items-center justify-between gap-3">
      <div class="min-w-0 flex-1">
        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Pembayaran</p>
        <p class="text-lg font-black text-[#AE7C18] truncate">Rp <span x-text="formatNumber(grandTotal)"></span></p>
      </div>
      <div class="flex items-center gap-2 shrink-0">
        <button type="button" @click="resetForm()" class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition active:scale-95 active:bg-slate-100" title="Atur Ulang">
          <x-heroicon-o-arrow-path class="h-6 w-6"/>
        </button>
        <button type="button" @click="submitForm()" :disabled="loading || cart.length === 0 || !customer.name" class="inline-flex h-12 items-center justify-center gap-2 rounded-2xl bg-[#AE7C18] px-5 text-sm font-bold text-white shadow-lg shadow-[#AE7C18]/30 transition active:scale-95 active:bg-[#96690F] disabled:opacity-50">
          <x-heroicon-o-check-circle class="h-5 w-5" />
          <span>Simpan</span>
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function transactionCreate() {
  return {
    variants: @js($variants->map(function ($variant) {
      $thumbnail = $variant->product?->images?->where('is_thumbnail', true)->first();
      $firstImage = $variant->product?->images?->first();
      $imagePath = $thumbnail?->image ?? $firstImage?->image;
      
      if ($imagePath) {
        $image = (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) 
          ? $imagePath 
          : ((str_starts_with($imagePath, 'images/') || str_starts_with($imagePath, 'storage/')) ? asset($imagePath) : asset('storage/' . $imagePath));
      } else {
        $image = asset('images/products/1.png');
      }

      return [
        'id' => $variant->id, 'sku' => $variant->sku, 'price' => (float) $variant->price, 'stock' => (int) ($variant->inventory?->stock ?? 0), 'image' => $image,
        'size' => ['id' => $variant->size?->id, 'name' => $variant->size?->name ?? '-'],
        'color' => ['id' => $variant->color?->id, 'name' => $variant->color?->name ?? '-'],
        'product' => ['id' => $variant->product?->id, 'name' => $variant->product?->name ?? '-'],
      ];
    })->values()),

    productSearch: '', productResults: [], productSearching: false, showProductResults: false,
    selectedVariantId: '', selectedVariant: null, selectedProducts: [], qty: 1, cart: [],
    customer: { name: '', phone: '', email: '' },
    transactionDate: '{{ now()->format('Y-m-d') }}', paymentMethod: 'CASH', source: 'Android POS', discount: 0, shipping: 0, loading: false,

    get subtotal() { return this.cart.reduce((total, item) => total + (Number(item.price) * Number(item.qty)), 0); },
    get totalQty() { return this.cart.reduce((total, item) => total + Number(item.qty), 0); },
    get grandTotal() { return Math.max(0, this.subtotal - Number(this.discount || 0) + Number(this.shipping || 0)); },

    searchProducts() {
      const keyword = this.productSearch.trim().toLowerCase();
      if (keyword.length < 2) { this.productResults = []; this.showProductResults = false; return; }
      this.productSearching = true; this.showProductResults = true;
      this.productResults = this.variants.filter((variant) => ((variant.product?.name ?? '').toLowerCase().includes(keyword) || (variant.sku ?? '').toLowerCase().includes(keyword)));
      this.productSearching = false;
    },

    selectProduct(variant) {
      const index = this.selectedProducts.findIndex((item) => Number(item.id) === Number(variant.id));
      if (index !== -1) { this.selectedProducts.splice(index, 1); return; }
      this.selectedProducts.push({ ...variant });
      this.selectedVariantId = variant.id; this.selectedVariant = variant; this.qty = 1;
      this.productSearch = ''; this.productResults = []; this.showProductResults = false;
    },

    isSelected(id) { return this.selectedProducts.some((item) => Number(item.id) === Number(id)); },
    removeSelectedProduct(index) { this.selectedProducts.splice(index, 1); },

    addSelectedProducts() {
      if (this.selectedProducts.length === 0) { this.toast('error', 'Produk Belum Dipilih', 'Silakan pilih minimal satu produk terlebih dahulu.'); return; }
      const qty = Math.max(1, Number(this.qty || 1));
      for (const variant of this.selectedProducts) {
        const stock = Number(variant.stock || 0);
        if (stock <= 0) { this.toast('error', 'Stok Habis', `${variant.product?.name ?? 'Produk'} sedang tidak memiliki stok.`); return; }
        if (qty > stock) { this.toast('error', 'Stok Tidak Mencukupi', `${variant.product?.name ?? 'Produk'} hanya memiliki stok ${stock}.`); return; }
        const existingIndex = this.cart.findIndex((item) => Number(item.variant_id) === Number(variant.id));
        if (existingIndex !== -1) {
          const existing = this.cart[existingIndex]; const newQty = Number(existing.qty) + qty;
          if (newQty > stock) { this.toast('error', 'Stok Tidak Mencukupi', `${variant.product?.name ?? 'Produk'} maksimal ${stock} pcs.`); return; }
          existing.qty = newQty;
        } else {
          this.cart.push({ variant_id: variant.id, product_id: variant.product?.id ?? null, product_name: variant.product?.name ?? '-', sku: variant.sku, image: variant.image, size: variant.size?.name ?? '-', color: variant.color?.name ?? '-', price: Number(variant.price), stock: stock, qty: qty });
        }
      }
      this.selectedProducts = []; this.selectedVariantId = ''; this.selectedVariant = null; this.qty = 1;
      this.toast('success', 'Produk Ditambahkan', 'Produk berhasil ditambahkan ke keranjang.');
    },

    updateQty(index) {
      const item = this.cart[index]; if (!item) return;
      if (Number(item.qty) < 1) item.qty = 1;
      if (Number(item.qty) > Number(item.stock)) { item.qty = Number(item.stock); this.toast('error', 'Stok Tidak Mencukupi', 'Jumlah disesuaikan dengan stok yang tersedia.'); }
    },

    removeItem(index) { this.cart.splice(index, 1); },

    resetForm() {
      this.productSearch = ''; this.productResults = []; this.productSearching = false; this.showProductResults = false;
      this.selectedProducts = []; this.selectedVariantId = ''; this.selectedVariant = null; this.qty = 1; this.cart = [];
      this.customer = { name: '', phone: '', email: '' };
      this.transactionDate = '{{ now()->format('Y-m-d') }}'; this.paymentMethod = 'CASH'; this.source = 'Android POS'; this.discount = 0; this.shipping = 0;
    },

    formatNumber(value) { return Number(value || 0).toLocaleString('id-ID'); },
    
    toast(type, title, message) { 
      window.dispatchEvent(new CustomEvent('toast', { detail: { type, title, message } })); 
    },

    async submitForm() {
      if (this.loading) return;
      if (!this.customer.name) { this.toast('error', 'Data Belum Lengkap', 'Nama pelanggan wajib diisi.'); return; }
      if (this.cart.length === 0) { this.toast('error', 'Keranjang Kosong', 'Tambahkan minimal satu produk.'); return; }
      this.loading = true;
      try {
        const response = await fetch('{{ route('admin.transactions.store') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json', 'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            customer: this.customer, transaction_date: this.transactionDate, payment_method: this.paymentMethod,
            source: this.source, discount: Number(this.discount || 0), shipping: Number(this.shipping || 0),
            items: this.cart.map((item) => ({ product_variant_id: item.variant_id, qty: Number(item.qty) }))
          })
        });
        const contentType = response.headers.get('content-type') || ''; const text = await response.text(); let data = {};
        if (contentType.includes('application/json')) data = JSON.parse(text);
        if (!response.ok) throw new Error(data.message || 'Gagal menyimpan transaksi.');
        this.toast('success', 'Transaksi Berhasil', data.message || 'Transaksi berhasil dibuat.');
        setTimeout(() => { window.location.href = '{{ route('admin.transactions') }}'; }, 800);
      } catch (error) {
        console.error('Create Transaction Error:', error);
        this.toast('error', 'Gagal Menyimpan', error.message || 'Terjadi kesalahan saat menyimpan transaksi.');
      } finally { this.loading = false; }
    }
  };
}
</script>
@endpush