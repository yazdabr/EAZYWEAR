<div
    x-data="{
        open: false,
        category: {
            id: '',
            name: '',
            slug: '',
            description: '',
            products: 0,
            status: 'Aktif',
            created: ''
        },
        openView(categoryData) {
            this.category = {
                id: categoryData?.id ?? '',
                name: categoryData?.name ?? '-',
                slug: categoryData?.slug ?? '-',
                description: categoryData?.description || 'Tidak ada deskripsi.',
                products: categoryData?.products ?? categoryData?.products_count ?? 0,
                status: (
                    categoryData?.status === true ||
                    categoryData?.status === 1 ||
                    categoryData?.status === '1' ||
                    categoryData?.status === 'Aktif'
                ) ? 'Aktif' : 'Tidak Aktif',
                created: categoryData?.created ?? categoryData?.created_at ?? '-'
            };
            this.open = true;
        }
    }"
    x-on:open-view-category.window="openView($event.detail)"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    @keydown.escape.window="open = false"
>
    {{-- Overlay --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition-opacity duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-250 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm"
    ></div>

    {{-- Drawer --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition transform duration-300 ease-in-out"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-[100] flex h-full w-full flex-col bg-white shadow-2xl sm:max-w-[520px]"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-7 sm:py-6">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-sky-100 sm:h-10 sm:w-10">
                    <x-heroicon-o-eye class="h-5 w-5 text-sky-600" />
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-900 sm:text-xl">
                        Detail Kategori
                    </h2>

                    <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">
                        Lihat detail informasi kategori.
                    </p>
                </div>
            </div>

            <button
                type="button"
                @click="open = false"
                class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 active:bg-slate-200"
            >
                <x-heroicon-o-x-mark class="h-6 w-6" />
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 space-y-4 overflow-y-auto bg-slate-100 p-4 sm:space-y-6 sm:p-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">

                {{-- Headline Card --}}
                <div class="mb-6 rounded-xl border border-slate-100 bg-slate-50/60 p-4 sm:p-5">
                    <span class="inline-flex rounded-full bg-[#AE7C18]/10 px-3 py-1 text-xs font-semibold text-[#AE7C18] sm:text-sm">
                        Kategori Produk
                    </span>

                    <h3
                        class="mt-3 text-xl font-bold text-slate-900 sm:text-2xl"
                        x-text="category.name"
                    ></h3>

                    <p
                        class="mt-2 text-xs leading-relaxed text-slate-600 sm:text-sm"
                        x-text="category.description"
                    ></p>
                </div>

                {{-- Information Items --}}
                <div class="space-y-3.5 text-xs sm:space-y-4 sm:text-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="font-medium text-slate-500">Slug</span>
                        <span
                            class="font-mono text-slate-800"
                            x-text="category.slug"
                        ></span>
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="font-medium text-slate-500">Jumlah Produk</span>
                        <span
                            class="font-semibold text-slate-900"
                            x-text="category.products + ' Produk'"
                        ></span>
                    </div>

                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="font-medium text-slate-500">Status</span>
                        <span
                            class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold"
                            :class="category.status === 'Aktif'
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-red-100 text-red-700'"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full"
                                :class="category.status === 'Aktif' ? 'bg-emerald-500' : 'bg-red-500'"
                            ></span>
                            <span x-text="category.status"></span>
                        </span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="font-medium text-slate-500">Tanggal Dibuat</span>
                        <span
                            class="font-medium text-slate-800"
                            x-text="category.created"
                        ></span>
                    </div>
                </div>

            </div>
        </div>

        {{-- FOOTER / ACTIONS --}}
        <div class="sticky bottom-0 flex flex-col gap-3 border-t border-slate-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-4">
            <div class="hidden sm:block">
                <p class="text-sm font-semibold text-slate-800">Opsi Kategori</p>
                <p class="text-xs text-slate-400">Pilih tindakan untuk data kategori ini.</p>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-3">
                <button
                    type="button"
                    @click="open = false"
                    class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 active:bg-slate-100 sm:flex-none sm:px-5 sm:py-3 sm:text-sm"
                >
                    Tutup
                </button>

                <button
                    type="button"
                    @click="
                        open = false;
                        setTimeout(() => {
                            $dispatch('open-edit-category', category);
                        }, 250);
                    "
                    class="relative inline-flex h-[44px] flex-1 min-w-[140px] items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 text-xs font-bold text-white shadow-md shadow-[#AE7C18]/20 transition-all duration-200 hover:bg-[#96690F] active:scale-95 sm:h-[48px] sm:flex-none sm:px-6 sm:text-sm"
                >
                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                    <span>Ubah Kategori</span>
                </button>
            </div>
        </div>

    </div>
</div>