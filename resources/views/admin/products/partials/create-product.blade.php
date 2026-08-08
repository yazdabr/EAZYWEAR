<div x-data="{
    open: false,
    mode: 'create',
    form: {
        name: '',
        category: '',
        sku: '',
        description: '',
        price: '',
        stock: '',
        image: '',
        gallery: []
    },
    toggleBodyScroll() {
        document.body.classList.toggle('overflow-hidden', this.open);
    },
    openCreate() {
        this.mode = 'create';
        this.form = {
            name: '',
            category: '',
            sku: '',
            description: '',
            price: '',
            stock: '',
            image: '',
            gallery: []
        };
        this.open = true;
    },
    openEdit(product) {
        this.mode = 'edit';
        this.form = {
            ...product,
            gallery: product.image ? [product.image] : []
        };
        this.open = true;
        this.$nextTick(() => {
            window.dispatchEvent(new CustomEvent('product-gallery-update', {
                detail: { images: this.form.gallery }
            }));
        });
    }
}" x-effect="toggleBodyScroll()" @keydown.escape.window="open=false" x-on:open-create-product.window="openCreate()" x-on:open-edit-product.window="openEdit($event.detail)">

    {{-- ================= OVERLAY ================= --}}
    <div x-show="open" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="open=false" class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm" style="display:none;"></div>

    {{-- ================= DRAWER ================= --}}
    <div x-show="open" x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition transform duration-400 ease-in-out" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed right-0 top-0 z-[100] flex h-screen w-full sm:max-w-[520px] flex-col bg-white shadow-2xl" style="display:none;">

        {{-- HEADER --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-5 sm:px-7 sm:py-6">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#AE7C18]/10">
                    <x-heroicon-o-plus class="h-5 w-5 text-[#AE7C18]" />
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 sm:text-xl" x-text="mode==='create' ? 'Add New Product' : 'Edit Product'"></h2>
                            <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm" x-text="mode==='create' ? 'Create a new product.' : 'Update product information.'"></p>
                        </div>
                    </div>
                </div>
            </div>
            <button @click="open=false" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">
                <x-heroicon-o-x-mark class="h-6 w-6"/>
            </button>
        </div>

        {{-- ================= BODY ================= --}}
        <div class="flex-1 space-y-4 overflow-y-auto bg-slate-100 p-4 sm:space-y-6 sm:p-6">

            {{-- ================= GENERAL INFORMATION ================= --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <div class="mb-4 flex items-start gap-3 sm:mb-6 sm:gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-11 sm:w-11">
                        <x-heroicon-o-document-text class="h-5 w-5 text-[#AE7C18]" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">General Information</h3>
                        <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Basic information about your product.</p>
                    </div>
                </div>

                {{-- Product Name --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Product Name</label>
                    <x-admin.input x-model="form.name" placeholder="e.g. Apex Pro Kit" />
                    <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">This name will appear on your storefront.</p>
                </div>

                {{-- Category + SKU --}}
                <div class="mt-4 grid grid-cols-1 gap-4 sm:mt-6 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Category</label>
                        <x-admin.select x-model="form.category" />
                        <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">Select product category.</p>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">SKU</label>
                        <x-admin.input x-model="form.sku" placeholder="SKU-001" />
                        <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">Must be unique.</p>
                    </div>
                </div>

                {{-- Description --}}
                <div class="mt-4 sm:mt-6">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                    <x-admin.textarea x-model="form.description" rows="5" placeholder="Describe materials, specifications and product features..." />
                    <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">This description will help customers understand your product.</p>
                </div>
            </div>

            {{-- ================= PRICING ================= --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <div class="mb-4 flex items-start gap-3 sm:mb-6 sm:gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 sm:h-11 sm:w-11">
                        <x-heroicon-o-banknotes class="h-5 w-5 text-emerald-600"/>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Pricing & Inventory</h3>
                        <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Configure selling price and available stock.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Price</label>
                        <x-admin.number-input x-model="form.price" prefix="Rp" placeholder="0" />
                        <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">Selling price.</p>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Stock</label>
                        <x-admin.number-input x-model="form.stock" placeholder="0" />
                        <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">Available quantity.</p>
                    </div>
                </div>
            </div>

            {{-- ================= PRODUCT GALLERY ================= --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <div class="mb-4 flex items-start gap-3 sm:mb-6 sm:gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 sm:h-11 sm:w-11">
                        <x-heroicon-o-photo class="h-5 w-5 text-violet-600"/>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Product Gallery</h3>
                        <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Upload product images.</p>
                    </div>
                </div>
                <x-admin.upload-image />
            </div>

        </div>

        {{-- ================= FOOTER ================= --}}
        <div class="sticky bottom-0 flex flex-col gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
            <div>
                <p class="text-sm font-medium text-slate-800 sm:text-base">Ready to publish?</p>
                <p class="text-xs text-slate-400">Save now or continue editing later.</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="open=false" class="flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 sm:flex-none sm:px-5 sm:py-3 sm:text-base">
                    Cancel
                </button>
                <button @click="
                    open = false;
                    setTimeout(() => {
                        $dispatch('toast',{
                            type: mode === 'create' ? 'success' : 'info',
                            title: mode === 'create' ? 'Product Created' : 'Product Updated',
                            message: mode === 'create' ? 'The product has been created successfully.' : 'The product has been updated successfully.'
                        });
                    }, 200);
                " class="flex-1 rounded-xl bg-[#AE7C18] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] hover:shadow-xl hover:shadow-[#AE7C18]/30 active:scale-95 sm:flex-none sm:px-6 sm:py-3 sm:text-base">
                    <span x-text="mode === 'create' ? 'Save Product' : 'Update Product'"></span>
                </button>
            </div>
        </div>

    </div>
</div>