<div
    x-data="deleteProductModal()"
    x-on:open-delete-product.window="openDelete($event.detail)"
    @keydown.escape.window="close()"
>
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
        @click="close()"
        class="fixed inset-0 z-[200] bg-black/50 backdrop-blur-sm">
    </div>

    {{-- MODAL CONTAINER --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2 sm:scale-90 sm:translate-y-0"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2 sm:scale-90 sm:translate-y-0"
        class="fixed inset-0 z-[201] flex items-center justify-center p-4 sm:p-6"
    >
        <div
            @click.stop
            class="relative w-full max-w-[360px] sm:max-w-md rounded-2xl sm:rounded-3xl bg-white p-5 sm:p-8 shadow-2xl transition-all"
        >
            {{-- ICON HAPUS --}}
            <div class="mx-auto flex h-14 w-14 sm:h-20 sm:w-20 items-center justify-center rounded-2xl bg-red-100/80 sm:rounded-full">
                <x-heroicon-o-trash class="h-7 w-7 sm:h-10 sm:w-10 text-red-600" />
            </div>

            {{-- TEXT CONTENT --}}
            <div class="mt-4 sm:mt-6 text-center">
                <h2 class="text-lg sm:text-2xl font-bold text-slate-900">
                    Hapus Produk?
                </h2>

                <p class="mt-2 text-xs sm:text-base text-slate-600 leading-relaxed">
                    Apakah Anda yakin ingin menghapus 
                    <span class="font-bold text-slate-900 break-words" x-text="product.name ? `'` + product.name + `'` : 'produk ini'"></span>?
                </p>

                <p class="mt-1 text-[11px] sm:text-sm font-medium text-red-500">
                    Tindakan ini permanen dan tidak dapat dibatalkan.
                </p>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="mt-6 sm:mt-8 flex gap-2.5 sm:gap-3">
                <button
                    type="button"
                    @click="close()"
                    :disabled="loading"
                    class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 sm:px-5 sm:py-3 text-xs sm:text-base font-semibold text-slate-700 transition hover:bg-slate-50 active:bg-slate-100 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Batal
                </button>

                <button
                    type="button"
                    @click="deleteProduct()"
                    :disabled="loading"
                    class="inline-flex flex-1 h-[42px] sm:h-[48px] items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 sm:px-5 sm:py-3 text-xs sm:text-base font-bold text-white shadow-lg shadow-red-500/20 transition-all duration-200 hover:bg-red-700 hover:shadow-xl hover:shadow-red-500/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <svg
                        x-show="loading"
                        class="h-4 w-4 animate-spin text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        style="display: none;"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>

                    <span x-text="loading ? 'Menghapus...' : 'Ya, Hapus'"></span>
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

        toggleBodyScroll() {
            document.body.classList.toggle('overflow-hidden', this.open);
        },

        openDelete(data) {
            this.product.id = data?.id ?? null;
            this.product.name = data?.name ?? '';
            this.loading = false;
            this.open = true;
            this.toggleBodyScroll();
        },

        close() {
            if (this.loading) return;
            this.open = false;
            this.toggleBodyScroll();
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

                this.close();

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