<div
    x-data="categoryDrawer()"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    @keydown.escape.window="closeDrawer()"
    x-on:open-create-category.window="openCreate()"
    x-on:open-edit-category.window="openEdit($event.detail)"
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
        @click="closeDrawer()"
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
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#AE7C18]/10 sm:h-10 sm:w-10">
                    <x-heroicon-o-squares-2x2 class="h-5 w-5 text-[#AE7C18]" />
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-900 sm:text-xl">
                        <span x-text="mode === 'create' ? 'Tambah Kategori' : 'Ubah Kategori'"></span>
                    </h2>

                    <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">
                        Atur kategori produk Anda.
                    </p>
                </div>
            </div>

            <button
                type="button"
                @click="closeDrawer()"
                class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 active:bg-slate-200"
            >
                <x-heroicon-o-x-mark class="h-6 w-6"/>
            </button>
        </div>

        {{-- Form Container --}}
        <form @submit.prevent="submitForm()" class="flex min-h-0 flex-1 flex-col">

            {{-- Body --}}
            <div class="flex-1 space-y-4 overflow-y-auto bg-slate-100 p-4 sm:space-y-6 sm:p-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">

                    <div class="mb-4 flex items-start gap-3 sm:mb-6 sm:gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-11 sm:w-11">
                            <x-heroicon-o-folder class="h-5 w-5 text-[#AE7C18]" />
                        </div>

                        <div>
                            <h3 class="text-base font-semibold text-slate-900">
                                Informasi Umum
                            </h3>

                            <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">
                                Informasi dasar tentang kategori ini.
                            </p>
                        </div>
                    </div>

                    {{-- Nama Kategori --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            Nama Kategori
                        </label>

                        <x-admin.input
                            x-model="form.name"
                            @input="generateSlug()"
                            placeholder="mis. Jersey Sepak Bola"
                        />

                        <p
                            x-show="errors.name"
                            x-text="errors.name"
                            class="mt-1.5 text-xs font-medium text-red-600"
                            style="display: none;"
                        ></p>
                    </div>

                    {{-- Slug --}}
                    <div class="mt-4 sm:mt-6">
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            Slug
                        </label>

                        <x-admin.input
                            x-model="form.slug"
                            placeholder="jersey-sepak-bola"
                        />

                        <p
                            x-show="errors.slug"
                            x-text="errors.slug"
                            class="mt-1.5 text-xs font-medium text-red-600"
                            style="display: none;"
                        ></p>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mt-4 sm:mt-6">
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            Deskripsi
                        </label>

                        <x-admin.textarea
                            rows="4"
                            x-model="form.description"
                            placeholder="Deskripsi kategori..."
                        />

                        <p
                            x-show="errors.description"
                            x-text="errors.description"
                            class="mt-1.5 text-xs font-medium text-red-600"
                            style="display: none;"
                        ></p>
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
                                class="inline-flex h-[46px] items-center justify-center rounded-lg text-sm font-semibold transition-all duration-200 sm:text-base"
                            >
                                <span
                                    class="mr-2 h-2 w-2 rounded-full"
                                    :class="form.status === 'Aktif' ? 'bg-emerald-500' : 'bg-slate-300'"
                                ></span>
                                Aktif
                            </button>

                            {{-- Tidak Aktif --}}
                            <button
                                type="button"
                                @click="form.status = 'Tidak Aktif'"
                                :class="form.status === 'Tidak Aktif'
                                    ? 'bg-white text-red-600 shadow-sm'
                                    : 'text-slate-500 hover:text-slate-700'"
                                class="inline-flex h-[46px] items-center justify-center rounded-lg text-sm font-semibold transition-all duration-200 sm:text-base"
                            >
                                <span
                                    class="mr-2 h-2 w-2 rounded-full"
                                    :class="form.status === 'Tidak Aktif' ? 'bg-red-500' : 'bg-slate-300'"
                                ></span>
                                Tidak Aktif
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            {{-- FOOTER / ACTIONS --}}
            <div class="sticky bottom-0 flex flex-col gap-3 border-t border-slate-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-4">
                <div class="hidden sm:block">
                    <p class="text-sm font-semibold text-slate-800">Publikasikan?</p>
                    <p class="text-xs text-slate-400">Simpan atau perbarui data produk Anda.</p>
                </div>
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <button type="button" @click="closeDrawer()" :disabled="loading" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 active:bg-slate-100 disabled:opacity-50 sm:flex-none sm:px-5 sm:py-3 sm:text-sm">
                        Batal
                    </button>
                    <button type="submit" :disabled="loading" class="relative inline-flex h-[44px] flex-1 min-w-[140px] items-center justify-center rounded-xl bg-[#AE7C18] px-4 text-xs font-bold text-white shadow-md shadow-[#AE7C18]/20 transition-all duration-200 hover:bg-[#96690F] active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:h-[48px] sm:flex-none sm:px-6 sm:text-sm">
                        <span x-show="!loading" x-text="mode === 'create' ? 'Simpan Produk' : 'Perbarui Produk'"></span>
                        <span x-show="loading" class="inline-flex items-center gap-2" style="display: none;">
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

<script>
function categoryDrawer() {
    return {
        open: false,
        loading: false,
        errors: {},
        mode: 'create',

        form: {
            id: '',
            name: '',
            slug: '',
            description: '',
            status: 'Aktif'
        },

        resetForm() {
            this.form = {
                id: '',
                name: '',
                slug: '',
                description: '',
                status: 'Aktif'
            };
            this.errors = {};
        },

        openCreate() {
            this.mode = 'create';
            this.resetForm();
            this.open = true;
        },

        openEdit(category) {
            this.mode = 'edit';
            this.errors = {};

            this.form = {
                id: category?.id ?? '',
                name: category?.name ?? '',
                slug: category?.slug ?? '',
                description: category?.description ?? '',
                status: (
                    category?.status === true ||
                    category?.status === 1 ||
                    category?.status === '1' ||
                    category?.status === 'Aktif'
                ) ? 'Aktif' : 'Tidak Aktif'
            };

            this.open = true;
        },

        generateSlug() {
            if (this.mode !== 'create') return;

            this.form.slug = (this.form.name || '')
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        },

        validateForm() {
            this.errors = {};

            if (!this.form.name || !this.form.name.trim()) {
                this.errors.name = 'Nama kategori wajib diisi.';
            }

            if (!this.form.slug || !this.form.slug.trim()) {
                this.errors.slug = 'Slug kategori wajib diisi.';
            }

            if (Object.keys(this.errors).length > 0) {
                window.dispatchEvent(
                    new CustomEvent('toast', {
                        detail: {
                            type: 'error',
                            title: 'Data Belum Lengkap',
                            message: 'Mohon lengkapi data kategori terlebih dahulu.'
                        }
                    })
                );
                return false;
            }

            return true;
        },

        async submitForm() {
            if (this.loading) return;
            if (!this.validateForm()) return;

            this.loading = true;

            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');

            const url = this.mode === 'create'
                ? '/admin/categories'
                : '/admin/categories/' + this.form.id;

            const formData = new FormData();

            if (this.mode === 'edit') {
                formData.append('_method', 'PUT');
            }

            formData.append('name', this.form.name);
            formData.append('slug', this.form.slug);
            formData.append('description', this.form.description || '');
            formData.append('status', this.form.status === 'Aktif' ? '1' : '0');

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                let data = {};
                try {
                    data = await response.json();
                } catch {
                    data = {};
                }

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = Object.keys(data.errors).reduce((errors, key) => {
                            errors[key] = Array.isArray(data.errors[key]) ? data.errors[key][0] : data.errors[key];
                            return errors;
                        }, {});

                        window.dispatchEvent(
                            new CustomEvent('toast', {
                                detail: {
                                    type: 'error',
                                    title: 'Validasi Gagal',
                                    message: 'Periksa kembali data kategori.'
                                }
                            })
                        );
                        return;
                    }

                    throw new Error(data.message || 'Gagal menyimpan kategori.');
                }

                const currentMode = this.mode;
                this.open = false;

                window.dispatchEvent(
                    new CustomEvent('toast', {
                        detail: {
                            type: 'success',
                            title: currentMode === 'create' ? 'Kategori Dibuat' : 'Kategori Diperbarui',
                            message: data.message || (
                                currentMode === 'create'
                                    ? 'Kategori berhasil dibuat.'
                                    : 'Kategori berhasil diperbarui.'
                            )
                        }
                    })
                );

                window.dispatchEvent(new CustomEvent('category-saved'));

                setTimeout(() => {
                    window.location.reload();
                }, 800);

            } catch (error) {
                window.dispatchEvent(
                    new CustomEvent('toast', {
                        detail: {
                            type: 'error',
                            title: 'Gagal Menyimpan',
                            message: error.message || 'Terjadi kesalahan saat menyimpan kategori.'
                        }
                    })
                );
            } finally {
                this.loading = false;
            }
        },

        closeDrawer() {
            if (this.loading) return;
            this.open = false;
        }
    };
}
</script>