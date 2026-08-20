<div
    x-data="transactionView()"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    @keydown.escape.window="open=false"
    x-on:open-view-transaction.window="openDrawer($event.detail)"
>
    <div
        x-show="open"
        x-transition.opacity
        @click="open=false"
        class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm"
        style="display:none;"
    ></div>

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
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4 sm:px-8 sm:py-6">
            <div>
                <p class="text-xs font-medium text-slate-500 sm:text-sm">Detail Transaksi</p>
                <h2 class="mt-0.5 text-lg font-bold text-slate-900 sm:mt-1 sm:text-2xl" x-text="transaction.invoice"></h2>
            </div>

            <button type="button" @click="open=false" class="rounded-xl p-2 transition hover:bg-slate-100">
                <x-heroicon-o-x-mark class="h-5 w-5 sm:h-6 sm:w-6"/>
            </button>
        </div>

        <div class="flex-1 space-y-4 overflow-y-auto bg-slate-50 p-4 sm:space-y-8 sm:p-8">
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
                            <template x-for="(item,index) in transaction.items" :key="index">
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

            <div class="grid gap-4 sm:gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-slate-900 sm:mb-5 sm:text-lg">Pembayaran</h3>

                    <div class="space-y-4 sm:space-y-6">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-slate-500 sm:text-sm">Metode Pembayaran</span>
                            <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700" x-text="transaction.payment"></span>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-medium text-slate-700 sm:text-sm">Status Transaksi</label>

                            <select
                                x-model="transaction.status"
                                :disabled="loading"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm transition duration-200 focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 disabled:cursor-not-allowed disabled:bg-slate-100 sm:px-4 sm:py-3 sm:text-base"
                            >
                                <option value="PENDING">Pending</option>
                                <option value="PAID">Paid</option>
                                {{-- <option value="COMPLETED">Completed</option> --}}
                                <option value="CANCELLED">Cancelled</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
                    <h3 class="mb-4 text-base font-semibold text-slate-900 sm:mb-5 sm:text-lg">Ringkasan</h3>

                    <div class="space-y-3 sm:space-y-4">
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

                        <div class="border-t border-dashed border-slate-300 pt-3 sm:pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-base font-bold text-slate-900 sm:text-lg">Total</span>
                                <span class="text-xl font-bold text-[#AE7C18] sm:text-2xl" x-text="'Rp ' + Number(transaction.total || 0).toLocaleString('id-ID')"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-slate-200 bg-white px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-8 sm:py-5">
            <button
                type="button"
                @click="updateStatus()"
                :disabled="loading || !transaction.id"
                class="w-full rounded-xl bg-emerald-600 px-5 py-2.5 text-center text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto sm:px-6 sm:py-3 sm:text-base"
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

            <div class="flex items-center gap-2.5 sm:gap-3">
                <a
                    :href="'{{ route('admin.transactions.print', ['invoice' => '__invoice__']) }}'.replace('__invoice__', transaction.invoice)"
                    target="_blank"
                    class="flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-center text-xs font-medium text-slate-700 transition hover:bg-slate-100 sm:flex-none sm:px-5 sm:py-3 sm:text-sm"
                >
                    Cetak Invoice
                </a>

                <button
                    type="button"
                    @click="open=false"
                    class="flex-1 rounded-xl bg-[#AE7C18] px-5 py-2.5 text-center text-xs font-semibold text-white transition hover:bg-[#96690F] sm:flex-none sm:px-6 sm:py-3 sm:text-sm"
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