<div
    x-data="deleteProductModal()"
    x-on:open-delete-product.window="openDelete($event.detail)"
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
        class="fixed inset-0 z-[201] flex items-center justify-center p-5"
    >
        <div
            @click.stop
            class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl"
        >
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-100">
                <x-heroicon-o-trash class="h-10 w-10 text-red-600" />
            </div>

            <h2 class="mt-6 text-center text-2xl font-bold text-slate-900">
                Hapus Produk?
            </h2>

            <p class="mt-3 text-center text-slate-500">
                Apakah Anda yakin ingin menghapus
                <span
                    class="font-semibold text-slate-800"
                    x-text="product.name">
                </span>?
            </p>

            <p class="mt-2 text-center text-sm text-red-500">
                Tindakan ini tidak dapat dibatalkan.
            </p>

            <div class="mt-8 flex gap-3">
                <button
                    type="button"
                    @click="close()"
                    :disabled="loading"
                    class="flex-1 rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Batal
                </button>

                <button
                    type="button"
                    @click="deleteProduct()"
                    :disabled="loading"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3 font-semibold text-white shadow-lg shadow-red-500/20 transition-all duration-300 hover:bg-red-700 hover:shadow-xl hover:shadow-red-500/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
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
document.addEventListener('alpine:init', () => {
    Alpine.data('deleteProductModal', () => ({
        open: false,
        loading: false,

        product: {
            id: null,
            name: ''
        },

        openDelete(data) {
            this.product.id = data?.id ?? null;
            this.product.name = data?.name ?? '';
            this.loading = false;
            this.open = true;
        },

        close() {
            if (this.loading) return;

            this.open = false;
        },

        async deleteProduct() {
            if (!this.product.id || this.loading) return;

            this.loading = true;

            try {
                const response = await fetch(
                    '{{ url('/admin/products') }}/' + this.product.id,
                    {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Produk gagal dihapus.');
                }

                this.open = false;

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        title: 'Produk Dihapus',
                        message: data.message || 'Produk berhasil dihapus.'
                    }
                }));

                this.product = {
                    id: null,
                    name: ''
                };

                setTimeout(() => {
                    window.location.reload();
                }, 600);

            } catch (error) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        title: 'Gagal Menghapus',
                        message: error.message || 'Terjadi kesalahan saat menghapus produk.'
                    }
                }));

                this.loading = false;
            }
        }
    }));
});
</script>