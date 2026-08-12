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
        class="fixed inset-0 z-[190] bg-black/40 backdrop-blur-sm">
    </div>

    {{-- Modal --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="fixed inset-0 z-[200] flex items-center justify-center p-4"
    >
        <div
            @click.stop
            class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl"
        >
            {{-- Header --}}
            <div class="flex items-center gap-4 border-b border-slate-200 px-6 py-5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100">
                    <x-heroicon-o-trash class="h-5 w-5 text-red-600"/>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Hapus Kategori
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Konfirmasi penghapusan kategori.
                    </p>
                </div>

                <button
                    type="button"
                    @click="close()"
                    :disabled="loading"
                    class="ml-auto rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <x-heroicon-o-x-mark class="h-5 w-5"/>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-6">
                <p class="text-sm leading-6 text-slate-600">
                    Apakah Anda yakin ingin menghapus kategori
                    <span
                        class="font-semibold text-slate-900"
                        x-text="'“' + product.name + '”'"
                    ></span>?
                </p>

                <div class="mt-4 rounded-xl bg-red-50 p-4">
                    <div class="flex gap-3">
                        <x-heroicon-o-exclamation-triangle class="mt-0.5 h-5 w-5 shrink-0 text-red-600"/>

                        <p class="text-sm leading-5 text-red-700">
                            Data kategori yang sudah dihapus tidak dapat dikembalikan.
                        </p>
                    </div>
                </div>

                {{-- Error --}}
                <div
                    x-show="error"
                    x-cloak
                    class="mt-4 rounded-xl bg-red-50 p-4"
                >
                    <p
                        class="text-sm font-medium text-red-700"
                        x-text="error">
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">

                <button
                    type="button"
                    @click="close()"
                    :disabled="loading"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Batal
                </button>

                <button
                    type="button"
                    @click="deleteCategory()"
                    :disabled="loading"
                    class="inline-flex min-w-[120px] items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <svg
                        x-show="loading"
                        class="h-4 w-4 animate-spin"
                        xmlns="http://www.w3.org/2000/svg"
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

                    <span
                        x-text="loading ? 'Menghapus...' : 'Hapus'">
                    </span>
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