<div
    x-data="transactionView()"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    @keydown.escape.window="open=false"
    x-on:open-view-transaction.window="openDrawer($event.detail)"
>
    <!-- Overlay Backdrop -->
    <div
        x-show="open"
        x-transition.opacity
        @click="open=false"
        class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm"
        style="display:none;"
    ></div>

    <!-- Drawer Panel (Tetap dari Samping Kanan) -->
    <div
        x-show="open"
        x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition transform duration-300 ease-in-out"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed right-0 top-0 z-[100] flex h-screen w-full max-w-[760px] flex-col bg-white shadow-2xl"
        style="display:none;"
    >
        <!-- Header Drawer -->
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3.5 sm:px-8 sm:py-6 shrink-0">
            <div>
                <p class="text-xs font-medium text-slate-500 sm:text-sm">Detail Transaksi</p>
                <h2 class="mt-0.5 text-base font-bold text-slate-900 sm:mt-1 sm:text-2xl" x-text="transaction.invoice"></h2>
            </div>

            <button type="button" @click="open=false" class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                <x-heroicon-o-x-mark class="h-5 w-5 sm:h-6 sm:w-6"/>
            </button>
        </div>

        <!-- Scrollable Content Body -->
        <div class="flex-1 space-y-4 overflow-y-auto bg-slate-50 p-4 sm:space-y-6 sm:p-8">
            
            <!-- Informasi Pelanggan -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
                <h3 class="mb-3 text-sm font-semibold text-slate-900 sm:mb-5 sm:text-lg">Informasi Pelanggan</h3>

                <div class="grid grid-cols-2 gap-3 sm:gap-5">
                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 sm:text-xs">Pelanggan</p>
                        <p class="mt-0.5 text-sm font-semibold text-slate-900 sm:mt-1 sm:text-base break-words" x-text="transaction.customer"></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 sm:text-xs">Telepon</p>
                        <p class="mt-0.5 text-sm font-semibold text-slate-900 sm:mt-1 sm:text-base" x-text="transaction.phone"></p>
                    </div>

                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 sm:text-xs">Tanggal</p>
                        <p class="mt-0.5 text-sm font-semibold text-slate-900 sm:mt-1 sm:text-base" x-text="transaction.date"></p>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 sm:text-xs">Email</p>
                        <p class="mt-0.5 text-sm font-semibold text-slate-900 sm:mt-1 sm:text-base break-all" x-text="transaction.email"></p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 sm:text-xs">
                            Alamat Pengiriman
                        </p>

                        <p
                            class="mt-0.5 text-sm font-semibold leading-5 text-slate-900 sm:mt-1 sm:text-base sm:leading-6"
                            x-text="transaction.address"
                        ></p>

                        <p
                            class="mt-0.5 text-xs leading-5 text-slate-500 sm:text-sm sm:leading-6"
                            x-text="transaction.location"
                        ></p>

                        <p
                            class="mt-1 text-xs font-medium text-slate-500 sm:text-sm"
                            x-text="'Pengiriman: ' + transaction.shippingMethod"
                        ></p>
                    </div>
                </div>
            </div>

            <!-- Produk (Optimized for Mobile) -->
            <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
                <h3 class="mb-3 text-sm font-semibold text-slate-900 sm:mb-5 sm:text-lg">Item Produk</h3>

                <!-- Mobile View: Card List (Ditampilkan di layar kecil) -->
                <div class="space-y-3 sm:hidden">
                    <template x-for="(item, index) in transaction.items" :key="index">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3">
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-900 leading-snug" x-text="item.name"></p>
                                <span class="shrink-0 text-sm font-bold text-slate-900" x-text="item.total"></span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200/60 pt-2 text-xs text-slate-500">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="rounded bg-slate-200/60 px-1.5 py-0.5 uppercase text-[11px]"
                                        x-text="'Ukuran: ' + (item.size || '-')"
                                    ></span>

                                    <span
                                        class="rounded bg-slate-200/60 px-1.5 py-0.5 uppercase text-[11px]"
                                        x-text="'Warna: ' + (item.color || '-')"
                                    ></span>

                                    <template x-if="item.custom_name">
                                        <span
                                            class="rounded bg-[#AE7C18]/10 px-1.5 py-0.5 text-[11px] font-semibold uppercase text-[#AE7C18]"
                                            x-text="'Nama Jersey: ' + item.custom_name"
                                        ></span>
                                    </template>
                                </div>

                                <span
                                    class="font-medium text-slate-700"
                                    x-text="item.qty + 'x'"
                                ></span>
                            </div>
                        </div>
                    </template>

                    <template x-if="!transaction.items.length">
                        <div class="py-6 text-center text-xs text-slate-400">
                            Tidak ada produk pada transaksi ini.
                        </div>
                    </template>
                </div>

                <!-- Desktop View: Table (Ditampilkan di layar desktop / sm ke atas) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="w-full">
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
                            <template x-for="(item,index) in transaction.items" :key="index">
                                <tr>
                                    <td class="py-4">
                                        <p
                                            class="font-medium text-slate-900 text-base"
                                            x-text="item.name"
                                        ></p>

                                        <template x-if="item.custom_name">
                                            <p
                                                class="mt-1 text-xs font-semibold uppercase tracking-wide text-[#AE7C18]"
                                                x-text="'Nama Jersey: ' + item.custom_name"
                                            ></p>
                                        </template>
                                    </td>
                                    <td class="py-4 text-base text-slate-600" x-text="item.size"></td>
                                    <td class="py-4 text-base text-slate-600" x-text="item.color"></td>
                                    <td class="py-4 text-center text-base text-slate-600" x-text="item.qty"></td>
                                    <td class="py-4 text-right text-base font-semibold text-slate-900" x-text="item.total"></td>
                                </tr>
                            </template>
                            <template x-if="!transaction.items.length">
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-sm text-slate-400">
                                        Tidak ada produk pada transaksi ini.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pembayaran & Ringkasan -->
            <div class="grid gap-4 sm:gap-6 lg:grid-cols-2">
                <!-- Status Pembayaran -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
                    <h3 class="mb-3 text-sm font-semibold text-slate-900 sm:mb-5 sm:text-lg">Pembayaran</h3>

                    <div class="space-y-3 sm:space-y-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500 sm:text-sm">Metode Pembayaran</span>
                            <span class="rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-semibold text-violet-700 sm:px-3 sm:py-1" x-text="transaction.payment"></span>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-slate-700 sm:mb-2 sm:text-sm">Status Transaksi</label>
                            <select
                                x-model="transaction.status"
                                :disabled="loading"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs transition duration-200 focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 disabled:cursor-not-allowed disabled:bg-slate-100 sm:px-4 sm:py-3 sm:text-base"
                            >
                                <option value="PENDING">Pending</option>
                                <option value="PAID">Paid</option>
                                <option value="CANCELLED">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Harga -->
                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
                    <h3 class="mb-3 text-sm font-semibold text-slate-900 sm:mb-5 sm:text-lg">Ringkasan</h3>

                    <div class="space-y-2.5 sm:space-y-4">
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-slate-500">Subtotal</span>
                            <span class="font-medium text-slate-900" x-text="'Rp ' + Number(transaction.subtotal || 0).toLocaleString('id-ID')"></span>
                        </div>

                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-slate-500">Diskon</span>
                            <span class="font-medium text-slate-900" x-text="'Rp ' + Number(transaction.discount || 0).toLocaleString('id-ID')"></span>
                        </div>

                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-slate-500">Ongkos Kirim</span>
                            <span class="font-medium text-slate-900" x-text="'Rp ' + Number(transaction.shipping || 0).toLocaleString('id-ID')"></span>
                        </div>

                        <div class="border-t border-dashed border-slate-300 pt-2.5 sm:pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-slate-900 sm:text-lg">Total</span>
                                <span class="text-lg font-bold text-[#AE7C18] sm:text-2xl" x-text="'Rp ' + Number(transaction.total || 0).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Action Footer (Optimized Mobile Touch Targets) -->
        <div class="flex flex-col gap-2 border-t border-slate-200 bg-white p-4 shrink-0 sm:flex-row sm:items-center sm:justify-between sm:px-8 sm:py-5">
            <button
                type="button"
                @click="updateStatus()"
                :disabled="loading || !transaction.id"
                class="order-1 w-full rounded-xl bg-emerald-600 px-5 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-60 sm:order-none sm:w-auto sm:px-6 sm:py-3 sm:text-base"
            >
                <span x-show="!loading">Perbarui Status</span>

                <span x-show="loading" x-cloak class="inline-flex items-center justify-center gap-2">
                    <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-30"></circle>
                        <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    Menyimpan...
                </span>
            </button>

            <div class="order-2 flex items-center gap-2 sm:order-none sm:gap-3">
                <a
                    :href="'{{ route('admin.transactions.print', ['invoice' => '__invoice__']) }}'.replace('__invoice__', transaction.invoice)"
                    target="_blank"
                    class="flex-1 rounded-xl border border-slate-300 px-3.5 py-2.5 text-center text-xs font-semibold text-slate-700 transition hover:bg-slate-100 sm:flex-none sm:px-5 sm:py-3 sm:text-sm"
                >
                    Cetak Invoice
                </a>

                <button
                    type="button"
                    @click="open=false"
                    class="flex-1 rounded-xl bg-[#AE7C18] px-4 py-2.5 text-center text-xs font-semibold text-white transition hover:bg-[#96690F] sm:flex-none sm:px-6 sm:py-3 sm:text-sm"
                >
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function transactionView(){
    return {
        open:false,
        loading:false,
        transaction:{
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
            status:'Pending',
            subtotal:0,
            discount:0,
            shipping:0,
            total:0,
            items:[]
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

        async updateStatus(){
            if(this.loading){
                return;
            }

            if(!this.transaction.id){
                console.error('Transaction ID kosong:',this.transaction);

                window.dispatchEvent(new CustomEvent('toast',{
                    detail:{
                        type:'error',
                        title:'Gagal Memperbarui',
                        message:'ID transaksi tidak ditemukan.'
                    }
                }));

                return;
            }

            this.loading=true;

            const transactionId=this.transaction.id;
            const status=String(this.transaction.status || '').toUpperCase();

            const url='/admin/transactions/'+transactionId+'/status';

            console.log('=== UPDATE TRANSACTION STATUS ===');
            console.log('Transaction ID:',transactionId);
            console.log('Status:',status);
            console.log('URL:',url);

            try{
                const response=await fetch(url,{
                    method:'PATCH',
                    headers:{
                        'Content-Type':'application/json',
                        'Accept':'application/json',
                        'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With':'XMLHttpRequest'
                    },
                    body:JSON.stringify({
                        status:status
                    })
                });

                console.log('Response Status:',response.status);
                console.log('Response URL:',response.url);

                const contentType=response.headers.get('content-type') || '';
                const responseText=await response.text();

                console.log('Response:',responseText);

                let data={};

                if(contentType.includes('application/json')){
                    try{
                        data=JSON.parse(responseText);
                    }catch(error){
                        throw new Error('Response JSON tidak valid.');
                    }
                }else{
                    if(!response.ok){
                        throw new Error('Server mengembalikan halaman error. Status HTTP: '+response.status);
                    }

                    throw new Error('Server mengembalikan response yang tidak sesuai.');
                }

                if(!response.ok){
                    if(response.status===419){
                        throw new Error('Sesi telah berakhir. Silakan refresh halaman.');
                    }

                    if(response.status===422){
                        if(data.errors){
                            const firstError=Object.values(data.errors)[0];

                            throw new Error(
                                Array.isArray(firstError)
                                    ? firstError[0]
                                    : firstError
                            );
                        }

                        throw new Error(data.message || 'Status transaksi tidak valid.');
                    }

                    throw new Error(
                        data.message || 'Gagal memperbarui status transaksi.'
                    );
                }

                if(data.success===false){
                    throw new Error(data.message || 'Gagal memperbarui status transaksi.');
                }

                this.transaction.status=status;

                window.dispatchEvent(new CustomEvent('toast',{
                    detail:{
                        type:'success',
                        title:'Status Updated',
                        message:data.message || 'Transaction status updated successfully.'
                    }
                }));

                setTimeout(()=>{
                    window.location.reload();
                },700);

            }catch(error){
                console.error('Transaction Status Error:',error);

                window.dispatchEvent(new CustomEvent('toast',{
                    detail:{
                        type:'error',
                        title:'Update Failed',
                        message:error.message || 'Failed to update transaction status.'
                    }
                }));
            }finally{
                this.loading=false;
            }
        },
    };
}
</script>
@endpush