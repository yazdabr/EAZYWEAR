<div
    x-data="categoryDelete()"
    x-on:open-delete-category.window="openDelete($event.detail)"
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
        @click="close()"
        class="fixed inset-0 z-[200] bg-black/50 backdrop-blur-sm">
    </div>

    {{-- Modal --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        class="fixed inset-0 z-[201] flex items-center justify-center p-4 sm:p-5"
    >
        <div
            @click.stop
            class="w-full max-w-sm sm:max-w-md rounded-2xl sm:rounded-3xl bg-white p-6 sm:p-8 shadow-2xl"
        >
            <div class="mx-auto flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-full bg-red-100">
                <x-heroicon-o-trash class="h-8 w-8 sm:h-10 sm:w-10 text-red-600" />
            </div>

            <h2 class="mt-4 sm:mt-6 text-center text-xl sm:text-2xl font-bold text-slate-900">
                Hapus Kategori?
            </h2>

            <p class="mt-2 sm:mt-3 text-center text-sm sm:text-base text-slate-500">
                Apakah Anda yakin ingin menghapus kategori<br class="hidden sm:block" />
                <span
                    class="font-semibold text-slate-800"
                    x-text="product.name">
                </span>?
            </p>

            <p class="mt-1 sm:mt-2 text-center text-xs sm:text-sm text-red-500">
                Tindakan ini tidak dapat dibatalkan.
            </p>

            {{-- Menampilkan pesan error jika ada --}}
            <div
                x-show="error"
                x-cloak
                class="mt-4 rounded-xl bg-red-50 p-3 text-center text-sm font-medium text-red-700"
                x-text="error"
            ></div>

            <div class="mt-6 sm:mt-8 flex gap-2 sm:gap-3">
                <button
                    type="button"
                    @click="close()"
                    :disabled="loading"
                    class="flex-1 rounded-xl border border-slate-300 px-4 py-2 sm:px-5 sm:py-3 text-sm sm:text-base font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Batal
                </button>

                <button
                    type="button"
                    @click="deleteCategory()"
                    :disabled="loading"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2 sm:px-5 sm:py-3 text-sm sm:text-base font-semibold text-white shadow-lg shadow-red-500/20 transition-all duration-300 hover:bg-red-700 hover:shadow-xl hover:shadow-red-500/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <svg
                        x-show="loading"
                        class="h-4 w-4 animate-spin"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4">
                        </circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                        </path>
                    </svg>

                    <span x-text="loading ? 'Menghapus...' : 'Hapus'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function categoryDelete() {
    return {
        open: false,
        loading: false,
        error: '',

        // Variabel tetap menggunakan 'product' mengikuti data bawaan dari script Anda agar tidak ada yang error
        product: {
            id: null,
            name: ''
        },

        openDelete(data) {
            this.product = {
                id: data.id ?? null,
                name: data.name ?? ''
            };

            this.error = '';
            this.loading = false;
            this.open = true;
        },

        close() {
            if (this.loading) {
                return;
            }

            this.open = false;
            this.error = '';
        },

        async deleteCategory() {
            if (!this.product.id || this.loading) {
                return;
            }

            this.loading = true;
            this.error = '';

            const csrfToken = document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content');

            try {
                const response = await fetch(
                    '/admin/categories/' + this.product.id,
                    {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                let data = {};

                try {
                    data = await response.json();
                } catch {
                    data = {};
                }

                if (!response.ok) {
                    throw new Error(
                        data.message ||
                        'Kategori gagal dihapus.'
                    );
                }

                const deletedName = this.product.name;

                this.open = false;

                window.dispatchEvent(
                    new CustomEvent('toast', {
                        detail: {
                            type: 'success',
                            title: 'Kategori Dihapus',
                            message: `"${deletedName}" berhasil dihapus.`
                        }
                    })
                );

                window.dispatchEvent(
                    new CustomEvent('category-deleted', {
                        detail: {
                            id: this.product.id
                        }
                    })
                );

                setTimeout(() => {
                    window.location.reload();
                }, 800);

            } catch (error) {
                this.error = error.message ||
                    'Terjadi kesalahan saat menghapus kategori.';

                window.dispatchEvent(
                    new CustomEvent('toast', {
                        detail: {
                            type: 'error',
                            title: 'Gagal Menghapus',
                            message: this.error
                        }
                    })
                );
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>