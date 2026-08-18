<div
    x-data="transactionDelete()"
    @keydown.escape.window="closeModal()"
    x-on:open-delete-transaction.window="openModal($event.detail)"
>
    <div
        x-show="open"
        x-cloak
        x-transition.opacity
        @click="closeModal()"
        class="fixed inset-0 z-[190] bg-black/40 backdrop-blur-sm"
        style="display:none;"
    ></div>

    <div
        x-show="open"
        x-cloak
        x-transition
        class="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6"
        style="display:none;"
    >
        <div
            @click.stop
            class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl"
        >
            <div class="border-b border-slate-200 px-5 py-6 sm:px-8">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100">
                    <x-heroicon-o-exclamation-triangle class="h-7 w-7 text-red-600"/>
                </div>

                <h2 class="mt-5 text-center text-xl font-bold text-slate-900 sm:text-2xl">
                    <span x-text="isPending() ? 'Batalkan Transaksi' : 'Hapus Transaksi'"></span>
                </h2>

                <p class="mt-3 text-center text-sm leading-6 text-slate-500">
                    Faktur
                    <span
                        class="font-semibold text-slate-700"
                        x-text="transaction.invoice || '-'"
                    ></span>

                    <span x-text="isPending() ? 'akan dibatalkan.' : 'akan dihapus secara permanen.'"></span>
                </p>
            </div>

            <div class="px-5 py-6 sm:px-8">
                <div class="rounded-2xl bg-slate-50 p-4 sm:p-5">
                    <div class="flex items-start justify-between gap-4">
                        <span class="text-sm text-slate-500">
                            Pelanggan
                        </span>

                        <span
                            class="text-right text-sm font-semibold text-slate-900"
                            x-text="transaction.customer || '-'"
                        ></span>
                    </div>

                    <div class="mt-4 flex items-start justify-between gap-4">
                        <span class="text-sm text-slate-500">
                            Total
                        </span>

                        <span
                            class="text-right text-sm font-semibold text-[#AE7C18]"
                            x-text="'Rp ' + Number(transaction.total || 0).toLocaleString('id-ID')"
                        ></span>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-4 border-t border-slate-200 pt-4">
                        <span class="text-sm text-slate-500">
                            Status
                        </span>

                        <span
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                            :class="statusClass()"
                            x-text="transaction.status || '-'"
                        ></span>
                    </div>
                </div>

                <div
                    class="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm leading-5 text-red-700"
                    x-text="isPending()
                        ? 'Transaksi ini akan diubah menjadi status DIBATALKAN.'
                        : 'Transaksi beserta detail produknya akan dihapus secara permanen.'"
                ></div>
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 px-5 py-5 sm:flex-row sm:px-8">
                <button
                    type="button"
                    @click="closeModal()"
                    :disabled="loading"
                    class="flex-1 rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Tutup
                </button>

                <button
                    type="button"
                    @click="submit()"
                    :disabled="loading"
                    class="flex-1 rounded-xl bg-red-600 px-5 py-3 font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span
                        x-show="!loading"
                        x-text="isPending() ? 'Batalkan Transaksi' : 'Hapus Transaksi'"
                    ></span>

                    <span
                        x-show="loading"
                        x-cloak
                        class="inline-flex items-center justify-center gap-2"
                    >
                        <svg
                            class="h-4 w-4 animate-spin"
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

                        Memproses...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function transactionDelete(){
    return {
        open:false,
        loading:false,

        transaction:{
            id:null,
            invoice:'',
            customer:'',
            total:0,
            status:''
        },

        openModal(data){
            this.transaction={
                id:data?.id ?? null,
                invoice:data?.invoice ?? '',
                customer:data?.customer ?? '',
                total:data?.total ?? 0,
                status:String(data?.status ?? '').toUpperCase()
            };

            console.log('DELETE TRANSACTION DATA:',this.transaction);

            this.open=true;
        },

        closeModal(){
            if(this.loading){
                return;
            }

            this.open=false;
        },

        isPending(){
            return this.transaction.status === 'PENDING';
        },

        statusClass(){
            if(this.transaction.status === 'PENDING'){
                return 'bg-amber-100 text-amber-700';
            }

            if(this.transaction.status === 'PAID'){
                return 'bg-emerald-100 text-emerald-700';
            }

            if(this.transaction.status === 'COMPLETED'){
                return 'bg-sky-100 text-sky-700';
            }

            if(this.transaction.status === 'CANCELLED'){
                return 'bg-red-100 text-red-700';
            }

            return 'bg-slate-100 text-slate-700';
        },

        async submit(){
            if(this.loading || !this.transaction.id){
                return;
            }

            this.loading=true;

            const id=this.transaction.id;
            const pending=this.isPending();

            const url=pending
                ? '/admin/transactions/'+id+'/cancel'
                : '/admin/transactions/'+id;

            const method=pending ? 'PATCH' : 'DELETE';

            try{
                const response=await fetch(url,{
                    method:method,
                    headers:{
                        'Accept':'application/json',
                        'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'X-Requested-With':'XMLHttpRequest'
                    }
                });

                const contentType=response.headers.get('content-type') || '';
                const responseText=await response.text();

                let data={};

                if(contentType.includes('application/json')){
                    try{
                        data=JSON.parse(responseText);
                    }catch(error){
                        throw new Error('Response server tidak valid.');
                    }
                }

                if(!response.ok){
                    if(response.status===419){
                        throw new Error('Sesi telah berakhir. Silakan refresh halaman.');
                    }

                    if(response.status===404){
                        throw new Error('Data transaksi tidak ditemukan.');
                    }

                    throw new Error(
                        data.message || 'Gagal memproses transaksi.'
                    );
                }

                if(!contentType.includes('application/json')){
                    throw new Error('Server mengembalikan response yang tidak sesuai.');
                }

                this.open=false;

                window.dispatchEvent(new CustomEvent('toast',{
                    detail:{
                        type:pending ? 'warning' : 'success',
                        title:pending
                            ? 'Transaksi Dibatalkan'
                            : 'Transaksi Dihapus',
                        message:data.message || (
                            pending
                                ? 'Transaksi berhasil dibatalkan.'
                                : 'Transaksi berhasil dihapus.'
                        )
                    }
                }));

                setTimeout(()=>{
                    window.location.reload();
                },700);

            }catch(error){
                console.error('Transaction Delete Error:',error);

                window.dispatchEvent(new CustomEvent('toast',{
                    detail:{
                        type:'error',
                        title:'Gagal Memproses',
                        message:error.message || 'Terjadi kesalahan saat memproses transaksi.'
                    }
                }));
            }finally{
                this.loading=false;
            }
        }
    };
}
</script>
@endpush