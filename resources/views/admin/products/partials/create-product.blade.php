<div
    x-data="{
        open: false,
        mode: 'create',

        form: {
            name: '',
            category: '',
            sku: '',
            description: '',
            price: '',
            stock: '',
            status: 'Aktif',
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
                status: 'Aktif',
                image: '',
                gallery: []
            };

            this.open = true;
        },

        openEdit(product) {

            this.mode = 'edit';

            this.form = {
                ...product,
                status: product.status || 'Aktif',
                gallery: product.image ? [product.image] : []
            };

            this.open = true;

            this.$nextTick(() => {

                window.dispatchEvent(
                    new CustomEvent('product-gallery-update', {
                        detail: {
                            images: this.form.gallery
                        }
                    })
                );

            });

        }
    }"

    x-effect="toggleBodyScroll()"

    @keydown.escape.window="open=false"

    x-on:open-create-product.window="openCreate()"

    x-on:open-edit-product.window="openEdit($event.detail)"
>


    {{-- ================= OVERLAY ================= --}}
    <div
        x-show="open"

        x-transition:enter="transition-opacity duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"

        x-transition:leave="transition-opacity duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"

        @click="open=false"

        class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm"

        style="display:none;">
    </div>


    {{-- ================= DRAWER ================= --}}
    <div
        x-show="open"

        x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"

        x-transition:leave="transition transform duration-400 ease-in-out"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"

        class="fixed right-0 top-0 z-[100] flex h-screen w-full sm:max-w-[520px] flex-col bg-white shadow-2xl"

        style="display:none;"
    >


        {{-- ================= HEADER ================= --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-5 sm:px-7 sm:py-6">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#AE7C18]/10">

                    <x-heroicon-o-plus
                        class="h-5 w-5 text-[#AE7C18]" />

                </div>

                <div>

                    <div class="flex items-center gap-3">

                        <div>

                            <h2
                                class="text-lg font-bold text-slate-900 sm:text-xl"

                                x-text="mode === 'create'
                                    ? 'Tambah Produk Baru'
                                    : 'Edit Produk'">
                            </h2>

                            <p
                                class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm"

                                x-text="mode === 'create'
                                    ? 'Buat produk baru.'
                                    : 'Perbarui informasi produk.'">
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            {{-- Close --}}
            <button
                @click="open=false"

                class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700">

                <x-heroicon-o-x-mark
                    class="h-6 w-6"/>

            </button>

        </div>


        {{-- ================= BODY ================= --}}
        <div class="flex-1 space-y-4 overflow-y-auto bg-slate-100 p-4 sm:space-y-6 sm:p-6">


            {{-- ================= GENERAL INFORMATION ================= --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">

                <div class="mb-4 flex items-start gap-3 sm:mb-6 sm:gap-4">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-11 sm:w-11">

                        <x-heroicon-o-document-text
                            class="h-5 w-5 text-[#AE7C18]" />

                    </div>

                    <div>

                        <h3 class="text-base font-semibold text-slate-900">

                            Informasi Umum

                        </h3>

                        <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">

                            Informasi dasar tentang produk Anda.

                        </p>

                    </div>

                </div>


                {{-- Product Name --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Nama Produk

                    </label>

                    <x-admin.input
                        x-model="form.name"
                        placeholder="mis. Apex Pro Kit" />

                    <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">

                        Nama ini akan tampil di toko Anda.

                    </p>

                </div>


                {{-- Category + SKU --}}
                <div class="mt-4 grid grid-cols-1 gap-4 sm:mt-6 sm:grid-cols-2">

                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-700">

                            Kategori

                        </label>

                        <x-admin.select
                            x-model="form.category" />

                        <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">

                            Pilih kategori produk.

                        </p>

                    </div>


                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-700">

                            SKU

                        </label>

                        <x-admin.input
                            x-model="form.sku"
                            placeholder="SKU-001" />

                        <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">

                            Harus unik.

                        </p>

                    </div>

                </div>


                {{-- Description --}}
                <div class="mt-4 sm:mt-6">

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Deskripsi

                    </label>

                    <x-admin.textarea
                        x-model="form.description"
                        rows="5"
                        placeholder="Jelaskan bahan, spesifikasi, dan fitur produk..." />

                    <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">

                        Deskripsi ini membantu pelanggan memahami produk Anda.

                    </p>

                </div>

            </div>


            {{-- ================= PRICING ================= --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">

                <div class="mb-4 flex items-start gap-3 sm:mb-6 sm:gap-4">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-100 sm:h-11 sm:w-11">

                        <x-heroicon-o-banknotes
                            class="h-5 w-5 text-emerald-600"/>

                    </div>

                    <div>

                        <h3 class="text-base font-semibold text-slate-900">

                            Harga & Stok

                        </h3>

                        <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">

                            Atur harga jual dan stok yang tersedia.

                        </p>

                    </div>

                </div>


                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">


                    {{-- Harga --}}
                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-700">

                            Harga

                        </label>

                        <x-admin.number-input
                            x-model="form.price"
                            prefix="Rp"
                            placeholder="0" />

                        <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">

                            Harga jual.

                        </p>

                    </div>


                    {{-- Stok --}}
                    <div>

                        <label class="mb-2 block text-sm font-medium text-slate-700">

                            Stok

                        </label>

                        <x-admin.number-input
                            x-model="form.stock"
                            placeholder="0" />

                        <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">

                            Jumlah tersedia.

                        </p>

                    </div>


                    {{-- Status Produk --}}
                    <div class="sm:col-span-2">

                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            Status Produk
                        </label>

                        <div class="grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1">

                            {{-- Aktif --}}
                            <button
                                type="button"
                                @click="form.status = 'Aktif'"
                                :class="form.status === 'Aktif'
                                    ? 'bg-white text-[#AE7C18] shadow-sm'
                                    : 'text-slate-500 hover:text-slate-700'"
                                class="inline-flex h-[46px] items-center justify-center rounded-lg text-sm font-semibold transition-all duration-200">

                                <span
                                    class="mr-2 h-2 w-2 rounded-full"
                                    :class="form.status === 'Aktif'
                                        ? 'bg-emerald-500'
                                        : 'bg-slate-300'">
                                </span>

                                Aktif

                            </button>


                            {{-- Tidak Aktif --}}
                            <button
                                type="button"
                                @click="form.status = 'Tidak Aktif'"
                                :class="form.status === 'Tidak Aktif'
                                    ? 'bg-white text-red-600 shadow-sm'
                                    : 'text-slate-500 hover:text-slate-700'"
                                class="inline-flex h-[46px] items-center justify-center rounded-lg text-sm font-semibold transition-all duration-200">

                                <span
                                    class="mr-2 h-2 w-2 rounded-full"
                                    :class="form.status === 'Tidak Aktif'
                                        ? 'bg-red-500'
                                        : 'bg-slate-300'">
                                </span>

                                Tidak Aktif

                            </button>

                        </div>

                        <p class="mt-1.5 text-xs text-slate-400 sm:mt-2">
                            Tentukan apakah produk dapat ditampilkan dan dijual.
                        </p>

                    </div>

                </div>

            </div>


            {{-- ================= PRODUCT GALLERY ================= --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">

                <div class="mb-4 flex items-start gap-3 sm:mb-6 sm:gap-4">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-100 sm:h-11 sm:w-11">

                        <x-heroicon-o-photo
                            class="h-5 w-5 text-violet-600"/>

                    </div>

                    <div>

                        <h3 class="text-base font-semibold text-slate-900">

                            Galeri Produk

                        </h3>

                        <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">

                            Unggah foto produk.

                        </p>

                    </div>

                </div>

                <x-admin.upload-image />

            </div>

        </div>


        {{-- ================= FOOTER ================= --}}
        <div class="sticky bottom-0 flex flex-col gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">

            <div>

                <p class="text-sm font-medium text-slate-800 sm:text-base">

                    Publikasikan?

                </p>

                <p class="text-xs text-slate-400">

                    Simpan atau lanjutkan mengedit nanti.

                </p>

            </div>


            <div class="flex items-center gap-3">

                {{-- Batal --}}
                <button

                    @click="open=false"

                    class="flex-1 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 sm:flex-none sm:px-5 sm:py-3 sm:text-base">

                    Batal

                </button>


                {{-- Simpan / Perbarui --}}
                <button

                    @click="
                        open = false;

                        setTimeout(() => {

                            $dispatch('toast', {

                                type: mode === 'create'
                                    ? 'success'
                                    : 'info',

                                title: mode === 'create'
                                    ? 'Produk Dibuat'
                                    : 'Produk Diperbarui',

                                message: mode === 'create'
                                    ? 'Produk telah dibuat dengan berhasil.'
                                    : 'Produk telah diperbarui dengan berhasil.'

                            });

                        }, 200);
                    "

                    class="flex-1 rounded-xl bg-[#AE7C18] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] hover:shadow-xl hover:shadow-[#AE7C18]/30 active:scale-95 sm:flex-none sm:px-6 sm:py-3 sm:text-base">

                    <span
                        x-text="mode === 'create'
                            ? 'Simpan Produk'
                            : 'Perbarui Produk'">
                    </span>

                </button>

            </div>

        </div>

    </div>

</div>