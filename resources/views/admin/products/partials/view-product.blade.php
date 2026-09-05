<div x-data="{
    open: false,
    product:{
        id:null,
        image:'',
        images:[],
        name:'',
        category:'',
        category_id:'',
        description:'',
        product_code:'',
        material:'',
        price:0,
        stock:0,
        status:'Aktif',
        updated:'',
        size_ids:[],
        variants:{}
    },
    
    toggleBodyScroll() {
        document.body.classList.toggle('overflow-hidden', this.open);
    },

    openView(data){
        const sizeIds=Array.isArray(data?.size_ids)?data.size_ids.map(id=>Number(id)):[];
        const variants=data?.variants&&typeof data.variants==='object'?data.variants:{};
        const images=Array.isArray(data?.images)?data.images.slice(0,5):(data?.image?[data.image]:[]);
        this.product={
            id:data?.id??null,
            image:images[0]??data?.image??'',
            images:images,
            name:data?.name??'',
            category:data?.category??'',
            category_id:data?.category_id??'',
            description:data?.description??'',
            product_code:data?.product_code??data?.sku??'',
            material:data?.material??'',
            price:Number(data?.price??0),
            stock:Number(data?.stock??0),
            status:data?.status??'Aktif',
            updated:data?.updated??'',
            size_ids:sizeIds,
            variants:variants
        };
        this.open=true;
        this.toggleBodyScroll();
    },

    closeView() {
        this.open = false;
        this.toggleBodyScroll();
    },

    editProduct(){
        const editData={
            id:this.product.id,
            name:this.product.name,
            category_id:this.product.category_id,
            product_code:this.product.product_code,
            description:this.product.description,
            material:this.product.material,
            price:Number(this.product.price??0),
            stock:Number(this.product.stock??0),
            status:this.product.status==='Aktif',
            image:this.product.image,
            images:Array.isArray(this.product.images)?[...this.product.images]:[],
            size_ids:Array.isArray(this.product.size_ids)?this.product.size_ids.map(id=>Number(id)):[],
            variants:this.product.variants&&typeof this.product.variants==='object'?JSON.parse(JSON.stringify(this.product.variants)):{}
        };
        this.closeView();
        setTimeout(()=>{
            window.dispatchEvent(new CustomEvent('open-edit-product',{detail:editData}));
        },300);
    }
}" 
x-on:open-view-product.window="openView($event.detail)"
@keydown.escape.window="if(open) closeView()">

    {{-- OVERLAY --}}
    <div 
        x-show="open" 
        x-cloak 
        x-transition:enter="transition-opacity duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeView()" 
        class="fixed inset-0 z-[190] bg-black/40 backdrop-blur-sm">
    </div>

    {{-- DRAWER --}}
    <div 
        x-show="open" 
        x-cloak 
        x-transition:enter="transition transform duration-300 ease-out" 
        x-transition:enter-start="translate-x-full" 
        x-transition:enter-end="translate-x-0" 
        x-transition:leave="transition transform duration-200 ease-in" 
        x-transition:leave-start="translate-x-0" 
        x-transition:leave-end="translate-x-full" 
        class="fixed right-0 top-0 z-[200] flex h-full w-full max-w-[500px] flex-col bg-white shadow-2xl"
    >
        {{-- HEADER --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6 sm:py-5">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100">
                    <x-heroicon-o-eye class="h-5 w-5 text-sky-600" />
                </div>

                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-900">Detail Produk</h2>
                    <p class="text-xs text-slate-500">Informasi lengkap spesifikasi produk.</p>
                </div>
            </div>

            <button type="button" @click="closeView()" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <x-heroicon-o-x-mark class="h-5 w-5 sm:h-6 sm:w-6" />
            </button>
        </div>

        {{-- BODY --}}
        <div class="flex-1 overflow-y-auto bg-slate-50 p-4 sm:p-6 space-y-5">
            {{-- MAIN CARD --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm space-y-6">

                {{-- IMAGE WITH FALLBACK --}}
                <div>
                    <div class="relative aspect-square w-full overflow-hidden rounded-xl border border-slate-100 bg-slate-100">
                        <template x-if="product.image">
                            <img
                                :src="product.image.url || product.image"
                                class="h-full w-full object-cover object-center transition duration-300"
                                alt="Foto Produk"
                            >
                        </template>
                        <template x-if="!product.image">
                            <div class="flex h-full w-full flex-col items-center justify-center text-slate-400">
                                <x-heroicon-o-photo class="h-12 w-12 stroke-1" />
                                <span class="mt-2 text-xs font-medium">Tidak ada gambar</span>
                            </div>
                        </template>
                    </div>
                        <div x-show="product.images.length>1" x-cloak class="mt-3 grid grid-cols-4 gap-2.5">
                            <template x-for="(image,index) in product.images.slice(1,5)" :key="'gallery-'+(image.id||index)">
                                <button
                                    type="button"
                                    @click="product.image=image"
                                    class="relative overflow-hidden rounded-lg border-2 transition"
                                    :class="product.image?.id===image?.id?'border-[#AE7C18]':'border-slate-200 hover:border-[#AE7C18]'"
                                >
                                    <img
                                        :src="image.url"
                                        :alt="'Foto Produk '+(index+2)"
                                        width="200"
                                        height="200"
                                        loading="lazy"
                                        decoding="async"
                                        class="aspect-square w-full object-cover"
                                    >
                                </button>
                            </template>
                        </div>
                </div>

                {{-- TITLE & CATEGORY --}}
                <div class="border-b border-slate-100 pb-4">
                    <p class="text-xs font-bold uppercase tracking-wider text-[#AE7C18]" x-text="product.category || 'Tanpa Kategori'"></p>
                    <h2 class="mt-1 text-xl sm:text-2xl font-bold text-slate-900 break-words" x-text="product.name || '-'"></h2>
                    <p class="mt-1 font-mono text-xs text-slate-400" x-text="product.product_code ? 'SKU: ' + product.product_code : 'SKU: -'"></p>
                </div>

                {{-- DESCRIPTION --}}
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Deskripsi</h4>
                    <p class="mt-2 text-xs sm:text-sm leading-relaxed text-slate-600 whitespace-pre-line" x-text="product.description || 'Tidak ada deskripsi.'"></p>
                </div>

                {{-- SPECIFICATIONS --}}
                <div class="rounded-xl bg-slate-50 p-3.5 sm:p-4 space-y-3 border border-slate-100 text-xs sm:text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-slate-500">Harga Utama</span>
                        <span class="font-bold text-[#AE7C18]" x-text="'Rp ' + Number(product.price || 0).toLocaleString('id-ID')"></span>
                    </div>

                    <div class="flex items-center justify-between gap-4 border-t border-slate-200/60 pt-2.5">
                        <span class="text-slate-500">Total Stok</span>
                        <span class="font-semibold text-slate-800" x-text="product.stock ?? 0"></span>
                    </div>

                    <div class="flex items-center justify-between gap-4 border-t border-slate-200/60 pt-2.5">
                        <span class="text-slate-500">Status</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold" :class="product.status === 'Aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'">
                            <span class="h-1.5 w-1.5 rounded-full" :class="product.status === 'Aktif' ? 'bg-emerald-500' : 'bg-red-500'"></span>
                            <span x-text="product.status"></span>
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 border-t border-slate-200/60 pt-2.5">
                        <span class="text-slate-500">Bahan / Material</span>
                        <span class="font-medium text-slate-700" x-text="product.material || '-'"></span>
                    </div>
                </div>

                {{-- SIZE & VARIANTS --}}
                <div x-show="product.size_ids.length > 0 && Object.keys(product.variants).length > 0" x-cloak class="border-t border-slate-100 pt-5">
                    <div class="mb-3">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Variasi Ukuran</h4>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="sizeId in product.size_ids" :key="sizeId">
                            <div x-show="product.variants[String(sizeId)]" class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-3 shadow-2xs">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-xs font-bold text-[#AE7C18]">
                                        <span x-text="product.variants[String(sizeId)]?.name || sizeId"></span>
                                    </div>

                                    <div>
                                        <p class="text-xs font-semibold text-slate-800" x-text="product.variants[String(sizeId)]?.name || ('Ukuran ' + sizeId)"></p>
                                        <p class="text-[11px] text-slate-400">Stok: <span class="font-medium text-slate-700" x-text="product.variants[String(sizeId)]?.stock ?? 0"></span></p>
                                    </div>
                                </div>

                                <p class="text-xs sm:text-sm font-bold text-[#AE7C18]" x-text="'Rp ' + Number(product.variants[String(sizeId)]?.price || 0).toLocaleString('id-ID')"></p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- NO SIZE STATE --}}
                <div x-show="product.size_ids.length === 0 || Object.keys(product.variants).length === 0" x-cloak class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4 text-center">
                    <p class="text-xs font-medium text-slate-500">Produk ini tidak memiliki variasi ukuran khusus.</p>
                </div>

                {{-- UPDATED DATE --}}
                <div class="flex items-center justify-between border-t border-slate-100 pt-4 text-xs text-slate-400">
                    <span>Terakhir Diperbarui</span>
                    <span class="font-medium text-slate-600" x-text="product.updated || '-'"></span>
                </div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="border-t border-slate-200 bg-white px-5 py-4 sm:px-6 sm:py-5">
            <div class="flex gap-2.5 sm:gap-3">
                <button 
                    type="button" 
                    @click="closeView()" 
                    class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 sm:px-5 sm:py-3 text-xs sm:text-base font-semibold text-slate-700 transition hover:bg-slate-50 active:bg-slate-100 active:scale-95"
                >
                    Tutup
                </button>
                
                <button 
                    type="button" 
                    @click="editProduct()" 
                    class="inline-flex flex-1 h-[42px] sm:h-[48px] items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 py-2.5 sm:px-5 sm:py-3 text-xs sm:text-base font-bold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-200 hover:bg-[#96690F] hover:shadow-xl hover:shadow-[#AE7C18]/30 active:scale-95"
                >
                    Edit Produk
                </button>
            </div>
        </div>
    </div>
</div>