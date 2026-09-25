<div x-data="transactionView()"
     x-effect="document.body.classList.toggle('overflow-hidden', open)"
     @keydown.escape.window="open = false"
     x-on:open-view-transaction.window="openDrawer($event.detail)"
     class="relative z-[100]">

    {{-- Backdrop / Overlay --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 z-[90] bg-slate-900/50 backdrop-blur-sm"
         style="display:none;"
         x-cloak></div>

    {{-- Modal / Drawer Container --}}
    <div x-show="open"
         x-transition:enter="transition transform duration-300 ease-out"
         x-transition:enter-start="translate-y-full sm:translate-y-0 sm:translate-x-full"
         x-transition:enter-end="translate-y-0 sm:translate-x-0"
         x-transition:leave="transition transform duration-200 ease-in"
         x-transition:leave-start="translate-y-0 sm:translate-x-0"
         x-transition:leave-end="translate-y-full sm:translate-y-0 sm:translate-x-full"
         class="fixed inset-x-0 bottom-0 top-12 z-[100] flex max-h-[92vh] w-full flex-col rounded-t-2xl bg-white shadow-2xl sm:inset-y-0 sm:right-0 sm:left-auto sm:top-0 sm:h-screen sm:max-h-full sm:w-full sm:max-w-lg sm:rounded-none"
         style="display:none;"
         x-cloak>

        {{-- Mobile Pull Indicator (Sangat berguna untuk UX Mobile) --}}
        <div class="flex justify-center pt-2.5 pb-1 sm:hidden">
            <div class="h-1.5 w-12 rounded-full bg-slate-300"></div>
        </div>

        {{-- Header --}}
        <div class="flex shrink-0 items-center justify-between border-b border-slate-200/80 px-4 py-3 sm:px-6 sm:py-4">
            <div class="min-w-0 flex-1 pr-2">
                <span class="inline-block text-[11px] font-semibold uppercase tracking-wider text-slate-400 sm:text-xs">
                    Detail Transaksi
                </span>
                <h2 class="truncate text-base font-bold text-slate-900 sm:text-lg" x-text="transaction.invoice || 'Detail Transaksi'"></h2>
            </div>

            <button type="button"
                    @click="open = false"
                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 focus:outline-none">
                <x-heroicon-o-x-mark class="h-5 w-5"/>
            </button>
        </div>

        {{-- Body Content --}}
        <div class="flex-1 space-y-4 overflow-y-auto bg-slate-50/70 p-4 sm:p-6">

            {{-- Informasi Pelanggan --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <div class="mb-3.5 flex items-center gap-2.5 border-b border-slate-100 pb-2.5">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-[#AE7C18]">
                        <x-heroicon-o-user class="h-4 w-4"/>
                    </div>
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-800">Informasi Pelanggan</h3>
                </div>

                <div class="grid grid-cols-1 gap-x-4 gap-y-3 sm:grid-cols-2">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Pelanggan</p>
                        <p class="mt-0.5 break-words text-xs font-semibold text-slate-900 sm:text-sm" x-text="transaction.customer || '-'"></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Telepon</p>
                        <p class="mt-0.5 break-words text-xs font-semibold text-slate-900 sm:text-sm" x-text="transaction.phone || '-'"></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Tanggal</p>
                        <p class="mt-0.5 text-xs font-semibold text-slate-900 sm:text-sm" x-text="transaction.date || '-'"></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Email</p>
                        <p class="mt-0.5 break-all text-xs font-semibold text-slate-900 sm:text-sm" x-text="transaction.email || '-'"></p>
                    </div>

                    <div class="sm:col-span-2 border-t border-slate-100 pt-2.5">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Alamat Pengiriman</p>
                        <p class="mt-0.5 text-xs font-semibold leading-relaxed text-slate-900 sm:text-sm" x-text="transaction.address || '-'"></p>
                        <p class="mt-0.5 text-xs leading-relaxed text-slate-500" x-text="transaction.location || '-'"></p>
                        <div class="mt-1.5 inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">
                            <span x-text="'Kurir: ' + (transaction.shippingMethod || '-')"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Produk Items --}}
            <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
                <div class="mb-3.5 flex items-center gap-2.5 border-b border-slate-100 pb-2.5">
                    <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-[#AE7C18]">
                        <x-heroicon-o-shopping-bag class="h-4 w-4"/>
                    </div>
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-800">Item Produk</h3>
                </div>

                {{-- Tampilan Mobile --}}
                <div class="space-y-3 sm:hidden">
                    <template x-for="(item, index) in transaction.items" :key="index">
                        <div class="rounded-lg border border-slate-100 bg-slate-50/80 p-3">
                            <div class="flex gap-3">
                                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-white">
                                    <img :src="item.image || '/images/products/placeholder.png'" :alt="item.name" class="h-full w-full object-cover" loading="lazy">
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-1">
                                        <p class="text-xs font-bold text-slate-900 line-clamp-2" x-text="item.name || '-'"></p>
                                        <span class="shrink-0 text-xs font-bold text-slate-900" x-text="formatCurrency(item.total)"></span>
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-1 border-t border-slate-200/60 pt-2">
                                        <span class="rounded bg-slate-200/80 px-1.5 py-0.5 text-[10px] font-medium uppercase text-slate-600" x-text="'Ukuran: ' + (item.size || '-')"></span>

                                        <template x-if="item.custom_name">
                                            <span class="rounded bg-[#AE7C18]/10 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-[#AE7C18]" x-text="'Nama: ' + item.custom_name"></span>
                                        </template>

                                        <template x-if="item.custom_number">
                                            <span class="rounded bg-slate-900/10 px-1.5 py-0.5 text-[10px] font-semibold text-slate-700" x-text="'No: ' + item.custom_number"></span>
                                        </template>

                                        <span class="ml-auto text-[11px] font-semibold text-slate-500" x-text="(item.qty || 0) + ' pcs'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="!transaction.items || !transaction.items.length">
                        <div class="py-6 text-center text-xs text-slate-400">Tidak ada produk pada transaksi ini.</div>
                    </template>
                </div>

                {{-- Tampilan Desktop --}}
                <div class="hidden overflow-hidden sm:block">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="border-b border-slate-200/80 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                <th class="pb-2.5">Produk</th>
                                <th class="pb-2.5 text-center">Ukuran</th>
                                <th class="pb-2.5 text-center">Qty</th>
                                <th class="pb-2.5 text-right">Total</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in transaction.items" :key="index">
                                <tr class="text-xs">
                                    <td class="py-3 pr-2">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-white">
                                                <img :src="item.image || '/images/products/placeholder.png'" :alt="item.name" class="h-full w-full object-cover" loading="lazy">
                                            </div>

                                            <div class="min-w-0">
                                                <p class="font-semibold text-slate-900 truncate" x-text="item.name || '-'"></p>

                                                <div class="mt-0.5 flex flex-wrap gap-1">
                                                    <template x-if="item.custom_name">
                                                        <span class="inline-block text-[10px] font-semibold uppercase text-[#AE7C18]" x-text="'Nama: ' + item.custom_name"></span>
                                                    </template>

                                                    <template x-if="item.custom_number">
                                                        <span class="inline-block text-[10px] font-semibold text-slate-500" x-text="'| No: ' + item.custom_number"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3 text-center text-slate-600 font-medium" x-text="item.size || '-'"></td>
                                    <td class="py-3 text-center text-slate-600 font-medium" x-text="item.qty || 0"></td>
                                    <td class="py-3 text-right font-bold text-slate-900" x-text="formatCurrency(item.total)"></td>
                                </tr>
                            </template>

                            <template x-if="!transaction.items || !transaction.items.length">
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-xs text-slate-400">Tidak ada produk pada transaksi ini.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pembayaran & Ringkasan --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

                {{-- Status Pembayaran --}}
                <div class="flex flex-col justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
                    <div>
                        <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-2.5">
                            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-[#AE7C18]">
                                <x-heroicon-o-credit-card class="h-4 w-4"/>
                            </div>
                            <h3 class="text-xs font-bold uppercase tracking-wide text-slate-800">Pembayaran</h3>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block mb-1">Metode</span>
                                <span class="inline-flex items-center rounded-md bg-violet-50 px-2 py-1 text-xs font-semibold text-violet-700 border border-violet-200/60" x-text="transaction.payment || '-'"></span>
                            </div>

                            <div>
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 block mb-1">Status Transaksi</span>
                                <div class="flex items-center rounded-lg border px-3 py-1.5 text-xs font-bold shadow-xs"
                                     :class="{
                                         'bg-amber-50 border-amber-200 text-amber-700': transaction.status === 'PENDING',
                                         'bg-emerald-50 border-emerald-200 text-emerald-700': transaction.status === 'PAID',
                                         'bg-red-50 border-red-200 text-red-700': transaction.status === 'CANCELLED',
                                         'bg-orange-50 border-orange-200 text-orange-700': transaction.status === 'EXPIRED',
                                         'bg-slate-50 border-slate-200 text-slate-700': transaction.status === 'COMPLETED'
                                     }">
                                    <span x-text="transaction.status || '-'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan Biaya --}}
                <div class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-center gap-2.5 border-b border-slate-100 pb-2.5">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-[#AE7C18]">
                            <x-heroicon-o-receipt-percent class="h-4 w-4"/>
                        </div>
                        <h3 class="text-xs font-bold uppercase tracking-wide text-slate-800">Ringkasan</h3>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between text-slate-500">
                            <span>Subtotal</span>
                            <span class="font-semibold text-slate-800" x-text="formatCurrency(transaction.subtotal)"></span>
                        </div>

                        <div class="flex items-center justify-between text-slate-500">
                            <span>Diskon</span>
                            <span class="font-semibold text-slate-800" x-text="formatCurrency(transaction.discount)"></span>
                        </div>

                        <div class="flex items-center justify-between text-slate-500">
                            <span>Ongkos Kirim</span>
                            <span class="font-semibold text-slate-800" x-text="formatCurrency(transaction.shipping)"></span>
                        </div>

                        <div class="border-t border-dashed border-slate-200 pt-2.5 mt-2.5">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-900">Total</span>
                                <span class="text-base font-bold text-[#AE7C18]" x-text="formatCurrency(transaction.total)"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer Action Buttons --}}
        <div class="shrink-0 border-t border-slate-200 bg-white p-4 sm:px-6">
            <div class="flex flex-col-reverse gap-2.5 sm:flex-row sm:items-center sm:justify-end">

                {{-- Tombol Tutup --}}
                <button type="button"
                        @click="open = false"
                        class="inline-flex h-10 w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 active:scale-[0.98] sm:w-auto">
                    Tutup
                </button>

                {{-- Tombol Cetak Invoice --}}
                <a :href="'{{ route('admin.transactions.print', ['invoice' => '__invoice__']) }}'.replace('__invoice__', transaction.invoice)"
                   target="_blank"
                   class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 text-xs font-semibold text-slate-700 transition hover:bg-slate-100 active:scale-[0.98] sm:w-auto">
                    <x-heroicon-o-printer class="h-4 w-4 text-slate-500"/>
                    <span>Cetak Invoice</span>
                </a>

                {{-- Tombol Cek Pembayaran DOKU --}}
                <button type="button"
                        x-show="isDokuPayment() && transaction.status === 'PENDING'"
                        @click="checkDokuPayment()"
                        :disabled="loading || dokuLoading || !transaction.id"
                        class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-violet-600 px-4 text-xs font-bold text-white shadow-sm transition hover:bg-violet-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto">

                    <template x-if="!dokuLoading">
                        <div class="inline-flex items-center gap-1.5">
                            <x-heroicon-o-arrow-path class="h-4 w-4"/>
                            <span>Cek Pembayaran</span>
                        </div>
                    </template>

                    <template x-if="dokuLoading">
                        <div class="inline-flex items-center gap-1.5">
                            <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-30"></circle>
                                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                            </svg>
                            <span>Mengecek...</span>
                        </div>
                    </template>
                </button>

            </div>
        </div>

    </div>
</div>
