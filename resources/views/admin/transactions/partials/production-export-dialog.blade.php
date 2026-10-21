<div
    x-data="{ open: false, toggleBodyScroll() { document.body.classList.toggle('overflow-hidden', this.open) } }"
    x-cloak
    x-on:open-production-export-modal.window="open = true; toggleBodyScroll()"
    @keydown.escape.window="if (open) { open = false; toggleBodyScroll() }"
>
    <div x-show="open" x-cloak x-transition:enter="transition-opacity duration-200 ease-out"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-150 ease-in" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" @click="open = false; toggleBodyScroll()"
        class="fixed inset-0 z-[200] bg-black/50 backdrop-blur-sm"></div>

    <div x-show="open" x-cloak x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2 sm:scale-90 sm:translate-y-0"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2 sm:scale-90 sm:translate-y-0"
        @click="open = false; toggleBodyScroll()"
        class="fixed inset-0 z-[201] flex items-center justify-center p-4 sm:p-6">
        <div @click.stop class="w-full max-w-2xl max-h-[90dvh] overflow-y-auto rounded-2xl sm:rounded-3xl bg-white p-5 sm:p-7 shadow-2xl">
            <form method="GET" action="{{ route('admin.transactions.production-export') }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="mb-2 inline-flex rounded-xl bg-emerald-50 p-2 text-emerald-700">
                            <x-heroicon-o-document-arrow-down class="h-5 w-5"/>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900 sm:text-xl">Ekspor Data Produksi</h3>
                        <p class="mt-1 text-sm text-slate-500">Pilih produk dan periode pesanan yang ingin disiapkan untuk produksi.</p>
                    </div>
                    <button type="button" @click="open = false; toggleBodyScroll()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup">
                        <x-heroicon-o-x-mark class="h-5 w-5"/>
                    </button>
                </div>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="production-export-product" class="mb-1.5 block text-sm font-semibold text-slate-700">Produk</label>
                        <select id="production-export-product" name="product_id"
                            class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20">
                            <option value="">Semua produk yang dipesan</option>
                            @foreach($exportProducts as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-slate-400">Daftar hanya menampilkan produk yang ada pada transaksi berstatus Paid atau Order Processing.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label for="production-export-month" class="mb-1.5 block text-sm font-semibold text-slate-700">Bulan</label>
                            <select id="production-export-month" name="month" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20">
                                <option value="">Semua bulan</option>
                                @foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $monthValue => $monthLabel)
                                    <option value="{{ $monthValue }}">{{ $monthLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="production-export-year" class="mb-1.5 block text-sm font-semibold text-slate-700">Tahun</label>
                            <select id="production-export-year" name="year" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm text-slate-700 focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20">
                                <option value="">Semua tahun</option>
                                @for($year = now()->year; $year >= now()->year - 5; $year--)
                                    <option value="{{ $year }}" @selected($year === now()->year)>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="rounded-xl border border-slate-200 p-3 sm:p-4">
                        <p class="text-sm font-semibold text-slate-700">Rentang tanggal (opsional)</p>
                        <p class="mt-1 text-xs text-slate-400">Jika diisi, rentang tanggal akan digabung dengan filter bulan dan tahun.</p>
                        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="production-export-start" class="mb-1 block text-xs font-medium text-slate-600">Dari tanggal</label>
                                <input id="production-export-start" type="date" name="start_date" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20">
                            </div>
                            <div>
                                <label for="production-export-end" class="mb-1 block text-xs font-medium text-slate-600">Sampai tanggal</label>
                                <input id="production-export-end" type="date" name="end_date" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-emerald-50 px-3 py-3 text-xs leading-relaxed text-emerald-800">
                        File hanya berisi detail produksi: pelanggan, telepon, pengiriman, produk, warna, ukuran, jumlah, nama/nomor punggung, longsleeve, dan patch. Informasi pembayaran serta harga tidak disertakan.
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2">
                    <button type="button" @click="open = false; toggleBodyScroll()" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 py-3 text-sm font-semibold text-white hover:bg-[#96690F]">
                        <x-heroicon-o-arrow-down-tray class="h-4 w-4"/> Ekspor Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
