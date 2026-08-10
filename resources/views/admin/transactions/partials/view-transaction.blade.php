<div x-data="{
        open: false,
        transaction: {
            invoice: '',
            date: '',
            customer: '',
            phone: '',
            email: '',
            payment: '',
            status: '',
            subtotal: '',
            discount: '',
            shipping: '',
            total: '',
            items: []
        },
        openDrawer(data) {
            this.transaction = data;
            this.open = true;
        }
    }" 
    x-effect="document.body.classList.toggle('overflow-hidden', open)" 
    @keydown.escape.window="open=false" 
    x-on:open-view-transaction.window="openDrawer($event.detail)">

    {{-- Overlay --}}
    <div x-show="open" x-transition.opacity @click="open=false" class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm" style="display:none;"></div>

    {{-- Drawer --}}
    <div x-show="open" 
        x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]" 
        x-transition:enter-start="translate-x-full" 
        x-transition:enter-end="translate-x-0" 
        x-transition:leave="transition transform duration-300 ease-in-out" 
        x-transition:leave-start="translate-x-0" 
        x-transition:leave-end="translate-x-full" 
        class="fixed right-0 top-0 z-[100] flex h-screen w-full max-w-[760px] flex-col bg-white shadow-2xl" 
        style="display:none;">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4 sm:px-8 sm:py-6">
            <div>
                <p class="text-xs font-medium text-slate-500 sm:text-sm">Detail Transaksi</p>
                <h2 class="mt-0.5 text-lg font-bold text-slate-900 sm:mt-1 sm:text-2xl" x-text="transaction.invoice"></h2>
            </div>
            <button @click="open=false" class="rounded-xl p-2 transition hover:bg-slate-100">
                <x-heroicon-o-x-mark class="h-5 w-5 sm:h-6 sm:w-6"/>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 space-y-4 overflow-y-auto bg-slate-50 p-4 sm:space-y-8 sm:p-8">

            {{-- Customer --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
                <h3 class="mb-4 text-base font-semibold text-slate-900 sm:mb-5 sm:text-lg">Informasi Pelanggan</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-400">Pelanggan</p>
                        <p class="mt-1 font-semibold text-slate-900 text-sm sm:text-base" x-text="transaction.customer"></p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-400">Telepon</p>
                        <p class="mt-1 font-semibold text-slate-900 text-sm sm:text-base" x-text="transaction.phone"></p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-400">Email</p>
                        <p class="mt-1 font-semibold text-slate-900 text-sm sm:text-base break-all" x-text="transaction.email"></p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-400">Tanggal</p>
                        <p class="mt-1 font-semibold text-slate-900 text-sm sm:text-base" x-text="transaction.date"></p>
                    </div>
                </div>
            </div>

            {{-- Products --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
                <h3 class="mb-4 text-base font-semibold text-slate-900 sm:mb-5 sm:text-lg">Produk</h3>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[480px]">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wider text-slate-500">
                                <th class="pb-3">Produk</th>
                                <th class="pb-3">Ukuran</th>
                                <th class="pb-3">Warna</th>
                                <th class="pb-3 text-center">Jumlah</th>
                                <th class="pb-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="item in transaction.items">
                                <tr>
                                    <td class="py-3 sm:py-4">
                                        <p class="font-medium text-slate-900 text-sm sm:text-base" x-text="item.name"></p>
                                    </td>
                                    <td class="py-3 text-sm text-slate-600 sm:py-4 sm:text-base" x-text="item.size"></td>
                                    <td class="py-3 text-sm text-slate-600 sm:py-4 sm:text-base" x-text="item.color"></td>
                                    <td class="py-3 text-center text-sm text-slate-600 sm:py-4 sm:text-base" x-text="item.qty"></td>
                                    <td class="py-3 text-right text-sm font-semibold text-slate-900 sm:py-4 sm:text-base" x-text="item.total"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Payment & Summary --}}
            <div class="grid gap-4 sm:gap-6 lg:grid-cols-2">

                {{-- Payment --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-slate-900 sm:mb-5 sm:text-lg">Pembayaran</h3>
                    <div class="space-y-4 sm:space-y-6">
                        {{-- Payment Method --}}
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500 sm:text-sm">Metode Pembayaran</span>
                            <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700" x-text="transaction.payment"></span>
                        </div>

                        {{-- Transaction Status --}}
                        <div>
                            <label class="mb-2 block text-xs font-medium text-slate-700 sm:text-sm">Status Transaksi</label>
                            <select x-model="transaction.status" class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm transition duration-200 focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:px-4 sm:py-3 sm:text-base">
                                <option value="Pending">Menunggu</option>
                                <option value="Paid">Lunas</option>
                                <option value="Processing">Diproses</option>
                                <option value="Completed">Selesai</option>
                                <option value="Cancelled">Dibatalkan</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Summary --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-slate-900 sm:mb-5 sm:text-lg">Ringkasan</h3>
                    <div class="space-y-3 sm:space-y-4">
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-medium text-slate-900" x-text="transaction.subtotal"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-slate-500">Diskon</span>
                            <span class="font-medium text-slate-900" x-text="transaction.discount"></span>
                        </div>
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-slate-500">Ongkos Kirim</span>
                            <span class="font-medium text-slate-900" x-text="transaction.shipping"></span>
                        </div>
                        <div class="border-t border-dashed border-slate-300 pt-3 sm:pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-base font-bold text-slate-900 sm:text-lg">Total</span>
                                <span class="text-xl font-bold text-[#AE7C18] sm:text-2xl" x-text="transaction.total"></span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- Footer --}}
        <div class="flex flex-col gap-3 border-t border-slate-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8 sm:py-5">
            <button @click="
                    $dispatch('toast',{
                        type:'info',
                        title:'Transaksi Diperbarui',
                        message:'Status transaksi berhasil diperbarui.'
                    });
                " class="w-full rounded-xl bg-emerald-600 px-5 py-2.5 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 sm:w-auto sm:px-6 sm:py-3 sm:text-base">
                Perbarui Status
            </button>

            <div class="flex items-center gap-2.5 sm:gap-3">
                <a :href="'{{ route('admin.transactions.print', ['invoice' => '__invoice__']) }}'.replace('__invoice__', transaction.invoice)" target="_blank" class="flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-center text-xs font-medium text-slate-700 transition hover:bg-slate-100 sm:flex-none sm:px-5 sm:py-3 sm:text-sm">
                    Cetak Faktur
                </a>
                <button @click="open=false" class="flex-1 rounded-xl bg-[#AE7C18] px-5 py-2.5 text-center text-xs font-semibold text-white transition hover:bg-[#96690F] sm:flex-none sm:px-6 sm:py-3 sm:text-sm">
                    Tutup
                </button>
            </div>
        </div>

    </div>

</div>