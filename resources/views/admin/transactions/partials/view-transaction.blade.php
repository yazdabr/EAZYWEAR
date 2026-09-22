<div x-data="transactionView()" x-effect="document.body.classList.toggle('overflow-hidden', open)" @keydown.escape.window="open=false" x-on:open-view-transaction.window="openDrawer($event.detail)">
    <div x-show="open" x-transition.opacity @click="open=false" class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm" style="display:none;"></div>

    <div x-show="open" x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition transform duration-300 ease-in-out" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed right-0 top-0 z-[100] flex h-screen w-full max-w-[520px] flex-col bg-white shadow-2xl" style="display:none;">

        {{-- Header --}}
        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-4 py-3 sm:px-5 sm:py-4">
            <div class="min-w-0">
                <p class="text-[11px] font-medium text-slate-500 sm:text-xs">Detail Transaksi</p>
                <h2 class="mt-0.5 truncate text-base font-bold text-slate-900 sm:text-lg" x-text="transaction.invoice || 'Detail Transaksi'"></h2>
            </div>

            <button type="button" @click="open=false" class="ml-3 shrink-0 rounded-lg p-1.5 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                <x-heroicon-o-x-mark class="h-5 w-5"/>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 space-y-3 overflow-y-auto bg-slate-50 p-3 sm:space-y-4 sm:p-4">

            {{-- Informasi Pelanggan --}}
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="mb-3 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-[#AE7C18]">
                        <x-heroicon-o-user class="h-4 w-4"/>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Informasi Pelanggan</h3>
                </div>

                <div class="grid grid-cols-2 gap-x-3 gap-y-3">
                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Pelanggan</p>
                        <p class="mt-1 break-words text-sm font-semibold text-slate-900" x-text="transaction.customer || '-'"></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Telepon</p>
                        <p class="mt-1 break-words text-sm font-semibold text-slate-900" x-text="transaction.phone || '-'"></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Tanggal</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900" x-text="transaction.date || '-'"></p>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Email</p>
                        <p class="mt-1 break-all text-sm font-semibold text-slate-900" x-text="transaction.email || '-'"></p>
                    </div>

                    <div class="col-span-2">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Alamat Pengiriman</p>
                        <p class="mt-1 text-sm font-semibold leading-5 text-slate-900" x-text="transaction.address || '-'"></p>
                        <p class="mt-1 text-xs leading-5 text-slate-500" x-text="transaction.location || '-'"></p>
                        <p class="mt-1 text-xs font-medium text-slate-500" x-text="'Pengiriman: ' + (transaction.shippingMethod || '-')"></p>
                    </div>
                </div>
            </div>

            {{-- Produk --}}
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="mb-3 flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-[#AE7C18]">
                        <x-heroicon-o-shopping-bag class="h-4 w-4"/>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">Item Produk</h3>
                </div>

                {{-- Mobile --}}
                <div class="space-y-2.5 sm:hidden">
                    <template x-for="(item,index) in transaction.items" :key="index">
                        <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-3">
                            <div class="flex items-start gap-3">
                                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-slate-200">
                                    <img :src="item.image || '/images/products/placeholder.png'" :alt="item.name" class="h-full w-full object-cover" loading="lazy">
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-xs font-semibold leading-snug text-slate-900" x-text="item.name || '-'"></p>
                                        <span class="shrink-0 text-xs font-bold text-slate-900" x-text="formatCurrency(item.total)"></span>
                                    </div>

                                    <div class="mt-2 flex flex-wrap items-center gap-1.5 border-t border-slate-200/60 pt-2">
                                        <span class="rounded bg-slate-200/60 px-1.5 py-0.5 text-[10px] uppercase text-slate-600" x-text="'Ukuran: ' + (item.size || '-')"></span>

                                        <template x-if="item.custom_name">
                                            <span class="rounded bg-[#AE7C18]/10 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-[#AE7C18]" x-text="'Nama: ' + item.custom_name"></span>
                                        </template>

                                        <template x-if="item.custom_number">
                                            <span class="rounded bg-slate-900/5 px-1.5 py-0.5 text-[10px] font-semibold text-slate-700" x-text="'Nomor: ' + item.custom_number"></span>
                                        </template>

                                        <span class="text-[11px] font-medium text-slate-700" x-text="(item.qty || 0) + 'x'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="!transaction.items.length">
                        <div class="py-5 text-center text-xs text-slate-400">Tidak ada produk pada transaksi ini.</div>
                    </template>
                </div>

                {{-- Desktop --}}
                <div class="hidden overflow-x-auto sm:block">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-[10px] uppercase tracking-wider text-slate-500">
                                <th class="pb-2">Produk</th>
                                <th class="pb-2">Ukuran</th>
                                <th class="pb-2 text-center">Jumlah</th>
                                <th class="pb-2 text-right">Total</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item,index) in transaction.items" :key="index">
                                <tr>
                                    <td class="py-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="h-11 w-11 shrink-0 overflow-hidden rounded-lg bg-slate-100">
                                                <img :src="item.image || '/images/products/placeholder.png'" :alt="item.name" class="h-full w-full object-cover" loading="lazy">
                                            </div>

                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold text-slate-900" x-text="item.name || '-'"></p>

                                                <template x-if="item.custom_name">
                                                    <p class="mt-1 text-[10px] font-semibold uppercase text-[#AE7C18]" x-text="'Nama: ' + item.custom_name"></p>
                                                </template>

                                                <template x-if="item.custom_number">
                                                    <p class="mt-1 text-[10px] font-semibold text-slate-700" x-text="'Nomor: ' + item.custom_number"></p>
                                                </template>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3 text-xs text-slate-600" x-text="item.size || '-'"></td>
                                    <td class="py-3 text-center text-xs text-slate-600" x-text="item.qty || 0"></td>
                                    <td class="py-3 text-right text-xs font-semibold text-slate-900" x-text="formatCurrency(item.total)"></td>
                                </tr>
                            </template>

                            <template x-if="!transaction.items.length">
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-xs text-slate-400">Tidak ada produk pada transaksi ini.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pembayaran dan Ringkasan --}}
            <div class="grid gap-3 sm:gap-4 lg:grid-cols-2">

                {{-- Pembayaran --}}
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="mb-3 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-[#AE7C18]">
                            <x-heroicon-o-credit-card class="h-4 w-4"/>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Pembayaran</h3>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs text-slate-500">Metode Pembayaran</span>
                            <span class="rounded-full bg-violet-100 px-2 py-1 text-[10px] font-semibold text-violet-700" x-text="transaction.payment || '-'"></span>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700">
                                Status Transaksi
                            </label>

                            <div
                                class="flex min-h-[38px] items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold"
                                :class="{
                                    'text-amber-700': transaction.status === 'PENDING',
                                    'text-emerald-700': transaction.status === 'PAID',
                                    'text-red-700': transaction.status === 'CANCELLED',
                                    'text-slate-700': transaction.status === 'COMPLETED'
                                }"
                            >
                                <span x-text="transaction.status || '-'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan --}}
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="mb-3 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-[#AE7C18]">
                            <x-heroicon-o-receipt-percent class="h-4 w-4"/>
                        </div>
                        <h3 class="text-sm font-bold text-slate-900">Ringkasan</h3>
                    </div>

                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-medium text-slate-900" x-text="formatCurrency(transaction.subtotal)"></span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Diskon</span>
                            <span class="font-medium text-slate-900" x-text="formatCurrency(transaction.discount)"></span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Ongkos Kirim</span>
                            <span class="font-medium text-slate-900" x-text="formatCurrency(transaction.shipping)"></span>
                        </div>

                        <div class="border-t border-dashed border-slate-300 pt-2.5">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-sm font-bold text-slate-900">Total</span>
                                <span class="text-base font-bold text-[#AE7C18]" x-text="formatCurrency(transaction.total)"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- **FOOTER** --}}
        <div class="shrink-0 border-t border-slate-200 bg-white p-4 sm:p-5">
            <div
                class="grid gap-2.5 sm:gap-3"
                :class="isDokuPayment() && !['PAID', 'CANCELLED'].includes(transaction.status)
                    ? 'grid-cols-3'
                    : 'grid-cols-2'"
            >

                {{-- Cek Pembayaran DOKU --}}
                <button
                    type="button"
                    x-show="isDokuPayment() && !['PAID', 'CANCELLED'].includes(transaction.status)"
                    @click="checkDokuPayment()"
                    :disabled="loading || dokuLoading || !transaction.id"
                    class="flex h-11 w-full items-center justify-center rounded-xl border border-violet-200 bg-violet-50 px-3 py-2 text-xs font-bold text-violet-700 transition-all duration-200 hover:bg-violet-100 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span
                        x-show="!dokuLoading"
                        class="inline-flex items-center justify-center gap-1.5"
                    >
                        <x-heroicon-o-arrow-path class="h-4 w-4 shrink-0" />
                        <span class="truncate">Cek Pembayaran</span>
                    </span>

                    <span
                        x-show="dokuLoading"
                        x-cloak
                        class="inline-flex items-center justify-center gap-1.5"
                    >
                        <svg
                            class="h-4 w-4 animate-spin shrink-0"
                            viewBox="0 0 24 24"
                            fill="none"
                        >
                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                                stroke="currentColor"
                                stroke-width="3"
                                class="opacity-30"
                            ></circle>
                            <path
                                d="M21 12a9 9 0 0 0-9-9"
                                stroke="currentColor"
                                stroke-width="3"
                                stroke-linecap="round"
                            ></path>
                        </svg>
                        <span class="truncate">Mengecek...</span>
                    </span>
                </button>

                {{-- Cetak Invoice --}}
                <a
                    :href="'{{ route('admin.transactions.print', ['invoice' => '__invoice__']) }}'.replace('__invoice__', transaction.invoice)"
                    target="_blank"
                    class="flex h-11 w-full items-center justify-center rounded-xl border border-slate-200 px-3 py-2 text-center text-xs font-semibold text-slate-700 transition hover:bg-slate-50 active:scale-95"
                >
                    <span class="truncate">Cetak Invoice</span>
                </a>

                {{-- Tutup --}}
                <button
                    type="button"
                    @click="open = false"
                    class="flex h-11 w-full items-center justify-center rounded-xl bg-[#AE7C18] px-3 py-2 text-center text-xs font-bold text-white shadow-md shadow-[#AE7C18]/20 transition-all duration-200 hover:bg-[#96690F] hover:shadow-lg hover:shadow-[#AE7C18]/30 active:scale-95"
                >
                    <span class="truncate">Tutup</span>
                </button>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function transactionView(){
    return {
        open: false,
        loading: false,
        dokuLoading: false,
        transaction: {
            id:null,
            invoice:'',
            date:'',
            customer:'',
            phone:'',
            email:'',
            address:'',
            location:'',
            shippingMethod:'',
            payment:'',
            status:'PENDING',
            subtotal:0,
            discount:0,
            shipping:0,
            total:0,
            items:[]
        },

        formatCurrency(value){
            return 'Rp. ' + Number(value || 0).toLocaleString('id-ID');
        },

        openDrawer(data){
            console.log('VIEW TRANSACTION DATA:',data);

            this.transaction={
                id:data?.id ?? null,
                invoice:data?.invoice ?? '',
                date:data?.date ?? '',
                customer:data?.customer ?? '',
                phone:data?.phone ?? data?.customer_phone ?? '-',
                email:data?.email ?? data?.customer_email ?? '-',
                address:data?.shipping_address ?? '-',
                location:[
                    data?.shipping_district,
                    data?.shipping_city,
                    data?.shipping_province,
                    data?.shipping_postal_code
                ].filter(Boolean).join(', ') || '-',
                shippingMethod:data?.shipping_method ?? '-',
                payment:data?.payment ?? '-',
                status:String(data?.status ?? 'PENDING').toUpperCase(),
                subtotal:data?.subtotal ?? 0,
                discount:data?.discount ?? 0,
                shipping:data?.shipping ?? 0,
                total:data?.total ?? 0,
                items:Array.isArray(data?.items) ? data.items : []
            };

            console.log('TRANSACTION AFTER OPEN:',this.transaction);
            this.open=true;
        },

        isDokuPayment() {
            const payment = String(this.transaction.payment || '').toUpperCase();

            return payment.includes('VA');
        },

        async checkDokuPayment() {
            if (this.dokuLoading || !this.transaction.id) {
                return;
            }

            if (['PAID', 'CANCELLED'].includes(this.transaction.status)) {
                return;
            }

            const confirmed = window.confirm(
                'Periksa status pembayaran DOKU untuk transaksi ' +
                (this.transaction.invoice || '') +
                '?'
            );

            if (!confirmed) {
                return;
            }

            this.dokuLoading = true;

            const transactionId = this.transaction.id;

            const url =
                '/admin/transactions/' +
                transactionId +
                '/check-doku-payment';

            console.log('=== CHECK DOKU PAYMENT ===');
            console.log('Transaction ID:', transactionId);
            console.log('Invoice:', this.transaction.invoice);
            console.log('URL:', url);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            )?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                console.log('DOKU Check Response Status:', response.status);

                const contentType =
                    response.headers.get('content-type') || '';

                const responseText = await response.text();

                console.log('DOKU Check Response:', responseText);

                let data = {};

                if (contentType.includes('application/json')) {
                    try {
                        data = JSON.parse(responseText);
                    } catch (error) {
                        throw new Error('Response JSON tidak valid.');
                    }
                }

                if (!response.ok) {
                    if (response.status === 419) {
                        throw new Error(
                            'Sesi telah berakhir. Silakan refresh halaman.'
                        );
                    }

                    throw new Error(
                        data.message ||
                        'Gagal mengecek pembayaran DOKU.'
                    );
                }

                if (data.success === false) {
                    throw new Error(
                        data.message ||
                        'Pembayaran belum berhasil diverifikasi.'
                    );
                }

                if (data.status === 'PAID') {
                    this.transaction.status = 'PAID';
                }

                window.dispatchEvent(
                    new CustomEvent('toast', {
                        detail: {
                            type: data.success ? 'success' : 'error',
                            title: data.success
                                ? 'Pengecekan DOKU'
                                : 'Pembayaran Belum Berhasil',
                            message:
                                data.message ||
                                'Status pembayaran telah diperiksa.'
                        }
                    })
                );

                if (data.success && data.status === 'PAID') {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } catch (error) {
                console.error('DOKU Payment Check Error:', error);

                window.dispatchEvent(
                    new CustomEvent('toast', {
                        detail: {
                            type: 'error',
                            title: 'Gagal Mengecek Pembayaran',
                            message:
                                error.message ||
                                'Terjadi kesalahan saat mengecek pembayaran DOKU.'
                        }
                    })
                );
            } finally {
                this.dokuLoading = false;
            }
        },
    };
}
</script>
@endpush
