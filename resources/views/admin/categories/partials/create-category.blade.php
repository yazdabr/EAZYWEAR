<div x-data="{
    open: false,
    mode: 'create',
    form: {
        name: '',
        slug: '',
        description: '',
        status: 'Aktif'
    },
    resetForm() {
        this.form = {
            name: '',
            slug: '',
            description: '',
            status: 'Aktif'
        };
    },
    openCreate() {
        this.mode = 'create';
        this.resetForm();
        this.open = true;
    },
    openEdit(category) {
        this.mode = 'edit';
        this.form = { ...category };
        this.open = true;
    }
}" x-effect="document.body.classList.toggle('overflow-hidden', open)" @keydown.escape.window="open=false" x-on:open-create-category.window="openCreate()" x-on:open-edit-category.window="openEdit($event.detail)">

    {{-- Overlay --}}
    <div x-show="open" x-transition.opacity @click="open=false" class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm" style="display:none;"></div>

    {{-- Drawer --}}
    <div x-show="open" x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition transform duration-300 ease-in-out" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed right-0 top-0 z-[100] flex h-screen w-full sm:max-w-[520px] flex-col bg-white shadow-2xl" style="display:none;">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-7 sm:py-6">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#AE7C18]/10 sm:h-10 sm:w-10">
                    <x-heroicon-o-squares-2x2 class="h-5 w-5 text-[#AE7C18]" />
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900 sm:text-xl">
                        <span x-text="mode==='create' ? 'Tambah Kategori' : 'Ubah Kategori'"></span>
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Atur kategori produk Anda.</p>
                </div>
            </div>
            <button @click="open=false" class="rounded-lg p-2 transition hover:bg-slate-100">
                <x-heroicon-o-x-mark class="h-6 w-6"/>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 space-y-4 overflow-y-auto bg-slate-100 p-4 sm:space-y-6 sm:p-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
                <div class="mb-4 flex items-start gap-3 sm:mb-6 sm:gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-11 sm:w-11">
                        <x-heroicon-o-folder class="h-5 w-5 text-[#AE7C18]" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Informasi Umum</h3>
                        <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Informasi dasar tentang kategori ini.</p>
                    </div>
                </div>

                {{-- Nama --}}
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Nama Kategori</label>
                    <x-admin.input x-model="form.name" placeholder="mis. Jersey Sepak Bola" />
                </div>

                {{-- Slug --}}
                <div class="mt-4 sm:mt-6">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Slug</label>
                    <x-admin.input x-model="form.slug" placeholder="jersey-sepak-bola" />
                </div>

                {{-- Deskripsi --}}
                <div class="mt-4 sm:mt-6">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Deskripsi</label>
                    <x-admin.textarea rows="4" x-model="form.description" placeholder="Deskripsi kategori..." />
                </div>

                {{-- Status --}}
                <div class="mt-4 sm:mt-6">

                    <label class="mb-2.5 block text-sm font-medium text-slate-700 sm:mb-3">
                        Status
                    </label>

                    <div class="grid grid-cols-2 gap-2 rounded-xl bg-slate-100 p-1">

                        {{-- Aktif --}}
                        <button
                            type="button"
                            @click="form.status = 'Aktif'"

                            :class="form.status === 'Aktif'
                                ? 'bg-white text-[#AE7C18] shadow-sm'
                                : 'text-slate-500 hover:text-slate-700'"

                            class="inline-flex h-[46px] items-center justify-center rounded-lg text-sm font-semibold transition-all duration-200 sm:text-base">

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

                            class="inline-flex h-[46px] items-center justify-center rounded-lg text-sm font-semibold transition-all duration-200 sm:text-base">

                            <span
                                class="mr-2 h-2 w-2 rounded-full"

                                :class="form.status === 'Tidak Aktif'
                                    ? 'bg-red-500'
                                    : 'bg-slate-300'">
                            </span>

                            Tidak Aktif

                        </button>

                    </div>

                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between border-t border-slate-200 bg-white px-5 py-4 sm:px-6 sm:py-5">
            <button @click="open=false" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 sm:px-5 sm:py-3 sm:text-base">
                Batal
            </button>
            <button @click="
                open=false;
                setTimeout(()=>{
                    $dispatch('toast',{
                        type: mode==='create' ? 'success' : 'info',
                        title: mode==='create' ? 'Kategori Dibuat' : 'Kategori Diperbarui',
                        message: mode==='create' ? 'Kategori berhasil dibuat.' : 'Kategori berhasil diperbarui.'
                    });
                },500);
            " class="rounded-xl bg-[#AE7C18] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] hover:shadow-xl sm:px-6 sm:py-3 sm:text-base">
                <span x-text="mode==='create' ? 'Simpan Kategori' : 'Perbarui Kategori'"></span>
            </button>
        </div>

    </div>
</div>