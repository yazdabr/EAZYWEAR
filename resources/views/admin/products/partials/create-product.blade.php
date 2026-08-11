<div x-data="{open:false,loading:false,errors:{},mode:'create',form:{id:'',name:'',category_id:'',product_code:'{{ $nextProductCode }}',description:'',material:'',price:'',stock:'',status:'Aktif',image:'',gallery:[]},validateForm(){this.errors={};if(!this.form.name?.trim())this.errors.name='Nama produk wajib diisi.';if(!this.form.category_id)this.errors.category_id='Kategori wajib dipilih.';if(!this.form.product_code?.trim())this.errors.product_code='Kode produk wajib diisi.';if(this.form.price===''||this.form.price==null)this.errors.price='Harga wajib diisi.';if(this.form.stock===''||this.form.stock==null)this.errors.stock='Stok wajib diisi.';if(Object.keys(this.errors).length>0){window.dispatchEvent(new CustomEvent('toast',{detail:{type:'error',title:'Data Belum Lengkap',message:'Mohon lengkapi data produk terlebih dahulu.'}}));return false;}return true;},async submitForm(e){e.preventDefault();if(!this.validateForm())return false;this.loading=true;const f=e.target,fd=new FormData(f);try{const r=await fetch(f.action,{method:'POST',headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'},body:fd});const d=await r.json();if(!r.ok){if(r.status===422&&d.errors){this.errors=Object.keys(d.errors).reduce((a,k)=>{a[k]=d.errors[k][0];return a;},{});}throw new Error(d.message||'Gagal menyimpan data produk.');}this.closeDrawer();window.dispatchEvent(new CustomEvent('toast',{detail:{type:'success',title:this.mode==='create'?'Berhasil Ditambahkan':'Berhasil Diperbarui',message:d.message||(this.mode==='create'?'Produk baru berhasil disimpan.':'Data produk berhasil diperbarui.')}}));setTimeout(()=>window.location.reload(),600);window.dispatchEvent(new CustomEvent('product-saved'));}catch(err){window.dispatchEvent(new CustomEvent('toast',{detail:{type:'error',title:'Gagal Menyimpan',message:err.message||'Terjadi kesalahan sistem.'}}));}finally{this.loading=false;}},toggleBodyScroll(){document.body.classList.toggle('overflow-hidden',this.open);},closeDrawer(){this.open=false;},openCreate(){this.mode='create';this.errors={};this.form={id:'',name:'',category_id:'',product_code:'{{ $nextProductCode }}',description:'',material:'',price:'',stock:'',status:'Aktif',image:'',gallery:[]};this.open=true;this.$nextTick(()=>window.dispatchEvent(new CustomEvent('product-gallery-update',{detail:{images:[]}})));},openEdit(p){this.mode='edit';this.errors={};this.form={id:p.id||'',name:p.name||'',category_id:p.category_id||'',product_code:p.product_code||'',description:p.description||'',material:p.material||'',price:p.price!==''&&p.price!==null?parseInt(p.price):'',stock:p.stock!==''&&p.stock!==null?parseInt(p.stock):'',status:(p.status===true||p.status===1||p.status==='1')?'Aktif':'Tidak Aktif',image:p.image||'',gallery:p.image?[p.image]:[]};this.open=true;this.$nextTick(()=>window.dispatchEvent(new CustomEvent('product-gallery-update',{detail:{images:this.form.gallery}})));}}" x-effect="toggleBodyScroll()" @keydown.escape.window="closeDrawer()" x-on:open-create-product.window="openCreate()" x-on:open-edit-product.window="openEdit($event.detail)">

    {{-- OVERLAY --}}
    <div x-show="open" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="open=false" class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm" style="display:none;"></div>

    {{-- DRAWER --}}
    <div x-show="open" x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition transform duration-500 ease-[cubic-bezier(0.4,0,0.2,1)]" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed right-0 top-0 z-[100] flex h-screen w-full sm:max-w-[520px] flex-col bg-white shadow-2xl" style="display:none;">

        {{-- HEADER --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-5 sm:px-7 sm:py-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#AE7C18]/10">
                    <x-heroicon-o-plus class="h-5 w-5 text-[#AE7C18]" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900 sm:text-xl" x-text="mode==='create'?'Tambah Produk Baru':'Edit Produk'"></h2>
                    <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm" x-text="mode==='create'?'Buat produk baru.':'Perbarui informasi produk.'"></p>
                </div>
            </div>
            <button type="button" @click="open=false" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <x-heroicon-o-x-mark class="h-6 w-6"/>
            </button>
        </div>

        <form :action="mode==='create'?'{{ route('admin.products.store') }}':'{{ url('/admin/products') }}/'+form.id" method="POST" enctype="multipart/form-data" @submit="submitForm($event)" class="flex min-h-0 flex-1 flex-col">
            @csrf
            <input type="hidden" name="_method" :value="mode==='edit'?'PUT':'POST'">
            <input type="hidden" name="status" :value="form.status==='Aktif'?1:0">

            {{-- BODY --}}
            <div class="flex-1 space-y-4 overflow-y-auto bg-slate-100 p-4 sm:space-y-6 sm:p-6">
                {{-- GENERAL INFO --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <div class="mb-4 flex items-start gap-3 sm:mb-6 sm:gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-11 sm:w-11">
                            <x-heroicon-o-document-text class="h-5 w-5 text-[#AE7C18]" />
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Informasi Umum</h3>
                            <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Informasi dasar tentang produk Anda.</p>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Nama Produk</label>
                        <x-admin.input x-model="form.name" name="name" placeholder="mis. Apex Pro Kit" ::class="errors.name?'border-red-400 focus:border-red-500 focus:ring-red-500/10':''" />
                        <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">Nama ini akan tampil di toko Anda.</p>
                        <template x-if="errors.name"><p class="mt-1.5 text-xs text-red-500" x-text="errors.name"></p></template>
                        @error('name')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-4 sm:mt-6 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Kategori</label>
                            <select x-model="form.category_id" name="category_id" :class="errors.category_id?'border-red-400 focus:border-red-500':'border-slate-200'" class="h-[50px] w-full rounded-xl border bg-white px-4 text-sm font-medium text-slate-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">Pilih kategori produk.</p>
                            <template x-if="errors.category_id"><p class="mt-1.5 text-xs text-red-500" x-text="errors.category_id"></p></template>
                            @error('category_id')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Kode Produk</label>
                            <x-admin.input x-model="form.product_code" name="product_code" readonly />
                            <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">Kode produk dibuat otomatis.</p>
                            <template x-if="errors.product_code"><p class="mt-1.5 text-xs text-red-500" x-text="errors.product_code"></p></template>
                            @error('product_code')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-4 sm:mt-6">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Deskripsi</label>
                        <x-admin.textarea x-model="form.description" name="description" rows="5" placeholder="Jelaskan bahan, spesifikasi, dan fitur produk..." />
                        <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">Deskripsi ini membantu pelanggan memahami produk Anda.</p>
                        @error('description')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-4 sm:mt-6">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Bahan</label>
                        <x-admin.input x-model="form.material" name="material" placeholder="mis. Dry-Fit Premium" />
                        <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">Material atau bahan utama produk.</p>
                        @error('material')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- PRICING --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <div class="mb-4 flex items-start gap-3 sm:mb-6 sm:gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 sm:h-11 sm:w-11">
                            <x-heroicon-o-banknotes class="h-5 w-5 text-emerald-600"/>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Harga & Stok</h3>
                            <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Atur harga jual dan stok yang tersedia.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Harga</label>
                            <x-admin.number-input x-model="form.price" name="price" prefix="Rp" placeholder="0" />
                            <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">Harga jual.</p>
                            <template x-if="errors.price"><p class="mt-1.5 text-xs text-red-500" x-text="errors.price"></p></template>
                            @error('price')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Stok</label>
                            <x-admin.number-input x-model="form.stock" name="stock" placeholder="0" />
                            <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">Jumlah tersedia.</p>
                            <template x-if="errors.stock"><p class="mt-1.5 text-xs text-red-500" x-text="errors.stock"></p></template>
                            @error('stock')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="mb-2 block text-sm font-medium text-slate-700">Status Produk</label>
                            <div class="grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1">
                                <button type="button" @click="form.status='Aktif'" :class="form.status==='Aktif'?'bg-white text-[#AE7C18] shadow-sm':'text-slate-500 hover:text-slate-700'" class="inline-flex h-[46px] items-center justify-center rounded-lg text-sm font-semibold transition-all duration-200">
                                    <span class="mr-2 h-2 w-2 rounded-full" :class="form.status==='Aktif'?'bg-emerald-500':'bg-slate-300'"></span>Aktif
                                </button>
                                <button type="button" @click="form.status='Tidak Aktif'" :class="form.status==='Tidak Aktif'?'bg-white text-red-600 shadow-sm':'text-slate-500 hover:text-slate-700'" class="inline-flex h-[46px] items-center justify-center rounded-lg text-sm font-semibold transition-all duration-200">
                                    <span class="mr-2 h-2 w-2 rounded-full" :class="form.status==='Tidak Aktif'?'bg-red-500':'bg-slate-300'"></span>Tidak Aktif
                                </button>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">Tentukan apakah produk dapat ditampilkan dan dijual.</p>
                        </div>
                    </div>
                </div>

                {{-- PRODUCT GALLERY --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                    <div class="mb-4 flex items-start gap-3 sm:mb-6 sm:gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 sm:h-11 sm:w-11">
                            <x-heroicon-o-photo class="h-5 w-5 text-violet-600"/>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">Galeri Produk</h3>
                            <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Unggah foto produk.</p>
                        </div>
                    </div>
                    <x-admin.upload-image name="image" />
                </div>
            </div>

            {{-- FOOTER --}}
            <div class="sticky bottom-0 flex flex-col gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
                <div>
                    <p class="text-sm font-medium text-slate-800 sm:text-base">Publikasikan?</p>
                    <p class="text-xs text-slate-400">Simpan atau lanjutkan mengedit nanti.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" @click="closeDrawer()" :disabled="loading" class="flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 disabled:opacity-50 sm:flex-none sm:px-5 sm:py-3 sm:text-base">
                        Batal
                    </button>

                    <button type="submit" :disabled="loading" class="relative flex-1 min-w-[160px] h-[46px] sm:h-[50px] items-center justify-center rounded-xl bg-[#AE7C18] px-5 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] hover:shadow-xl hover:shadow-[#AE7C18]/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:flex-none sm:px-6 sm:text-base">
                        <span x-show="!loading" x-text="mode==='create'?'Simpan Produk':'Perbarui Produk'"></span>
                        <span x-show="loading" class="inline-flex items-center gap-2" style="display:none;">
                            <svg class="h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Proses...</span>
                        </span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>