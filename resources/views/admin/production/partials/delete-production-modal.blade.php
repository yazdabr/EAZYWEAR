
<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<div
    x-data="deleteProductionModal()"
    x-cloak
    x-on:open-delete-production.window="openDelete($event.detail)"
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
        class="fixed inset-0 z-[200] bg-black/50 backdrop-blur-sm"
    ></div>

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
            class="relative w-full max-w-[360px] rounded-2xl bg-white p-5 shadow-2xl transition-all sm:max-w-md sm:rounded-3xl sm:p-8"
        >
            {{-- ICON --}}
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-red-100/80 sm:h-20 sm:w-20 sm:rounded-full">
                <x-heroicon-o-exclamation-triangle class="h-7 w-7 text-red-600 sm:h-10 sm:w-10"/>
            </div>

            {{-- TEXT CONTENT --}}
            <div class="mt-4 text-center sm:mt-6">
                <h2 class="text-lg font-bold text-slate-900 sm:text-2xl">
                    Hapus Produksi?
                </h2>

                <p class="mt-2 text-xs leading-relaxed text-slate-600 sm:text-base">
                    Data produksi

                    <span
                        class="break-words font-bold text-slate-900"
                        x-text="production.production_code
                            ? `'${production.production_code}'`
                            : '-'"
                    ></span>

                    akan dihapus secara permanen.
                </p>

                {{-- DETAIL INFO BOX --}}
                <div class="mt-4 space-y-2 rounded-xl bg-slate-50 p-3 text-left">
                    <div class="flex items-center justify-between gap-3 text-xs sm:text-sm">
                        <span class="shrink-0 text-slate-500">
                            Produk:
                        </span>

                        <span
                            class="max-w-[190px] truncate text-right font-semibold text-slate-900"
                            x-text="production.product_name || '-'"
                        ></span>
                    </div>

                    <div class="flex items-start justify-between gap-3 text-xs sm:text-sm">
                        <span class="shrink-0 text-slate-500">
                            Periode:
                        </span>

                        <span
                            class="max-w-[210px] text-right font-semibold text-slate-900"
                            x-text="production.period || '-'"
                        ></span>
                    </div>

                    <div class="flex items-center justify-between gap-3 text-xs sm:text-sm">
                        <span class="shrink-0 text-slate-500">
                            Total:
                        </span>

                        <span
                            class="font-semibold text-[#AE7C18]"
                            x-text="Number(production.total_quantity || 0).toLocaleString('id-ID') + ' pcs'"
                        ></span>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200/60 pt-2 text-xs sm:text-sm">
                        <span class="text-slate-500">
                            Status:
                        </span>

                        <span
                            class="inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold sm:text-xs"
                            :class="statusClass()"
                            x-text="production.status || '-'"
                        ></span>
                    </div>
                </div>

                <p class="mt-3 text-[11px] font-medium leading-relaxed text-red-500 sm:text-sm">
                    Stok produk akan dikurangi sesuai jumlah produksi yang dihapus.
                    Tindakan ini permanen dan tidak dapat dibatalkan.
                </p>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="mt-6 flex gap-2.5 sm:mt-8 sm:gap-3">
                <button
                    type="button"
                    @click="close()"
                    :disabled="loading"
                    class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:px-5 sm:py-3 sm:text-base"
                >
                    Batal
                </button>

                <button
                    type="button"
                    @click="submit()"
                    :disabled="loading"
                    class="inline-flex h-[42px] flex-1 items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-red-500/20 transition-all duration-200 hover:bg-red-700 hover:shadow-xl hover:shadow-red-500/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60 sm:h-[48px] sm:px-5 sm:py-3 sm:text-base"
                >
                    <svg
                        x-show="loading"
                        class="h-4 w-4 animate-spin text-white"
                        fill="none"
                        viewBox="0 0 24 24"
                        style="display: none;"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>

                    <span x-text="loading ? 'Menghapus...' : 'Ya, Hapus'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('deleteProductionModal', () => ({
            open: false,
            loading: false,

            production: {
                id: null,
                production_code: '',
                product_name: '',
                period: '',
                total_quantity: 0,
                status: ''
            },

            toggleBodyScroll() {
                document.body.classList.toggle(
                    'overflow-hidden',
                    this.open
                );
            },

            openDelete(data) {
                this.production = {
                    id: data?.id ?? null,
                    production_code: data?.production_code ?? '',
                    product_name: data?.product_name ?? '',
                    period: data?.period ?? '',
                    total_quantity: data?.total_quantity ?? 0,
                    status: String(data?.status ?? '').toUpperCase()
                };

                this.loading = false;
                this.open = true;

                this.toggleBodyScroll();
            },

            close() {
                if (this.loading) return;

                this.open = false;

                this.toggleBodyScroll();
            },

            statusClass() {
                if (
                    this.production.status === 'COMPLETED'
                    || this.production.status === 'SELESAI'
                ) {
                    return 'bg-emerald-100 text-emerald-700';
                }

                if (
                    this.production.status === 'IN_PROGRESS'
                    || this.production.status === 'SEDANG DIPROSES'
                ) {
                    return 'bg-sky-100 text-sky-700';
                }

                if (
                    this.production.status === 'CANCELLED'
                    || this.production.status === 'DIBATALKAN'
                ) {
                    return 'bg-red-100 text-red-700';
                }

                if (
                    this.production.status === 'PLANNED'
                    || this.production.status === 'DIRENCANAKAN'
                ) {
                    return 'bg-amber-100 text-amber-700';
                }

                return 'bg-slate-100 text-slate-700';
            },

            async submit() {
                if (!this.production.id || this.loading) {
                    return;
                }

                this.loading = true;

                const id = this.production.id;

                try {
                    const response = await fetch(
                        '{{ url('/admin/productions') }}/' + id,
                        {
                            method: 'DELETE',

                            headers: {
                                'Accept': 'application/json',

                                'X-CSRF-TOKEN': document
                                    .querySelector('meta[name="csrf-token"]')
                                    ?.getAttribute('content') || '',

                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }
                    );

                    const contentType = response.headers.get('content-type') || '';

                    let data = {};

                    if (contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        throw new Error(
                            'Respons server tidak valid. Periksa route dan controller.'
                        );
                    }

                    if (!response.ok) {
                        throw new Error(
                            data.message || 'Gagal menghapus produksi.'
                        );
                    }

                    this.close();

                    window.dispatchEvent(
                        new CustomEvent('toast', {
                            detail: {
                                type: 'success',
                                title: 'Produksi Dihapus',
                                message: data.message ||
                                    'Produksi berhasil dihapus.'
                            }
                        })
                    );

                    setTimeout(() => {
                        window.location.reload();
                    }, 600);

                } catch (error) {
                    console.error(
                        'Production Delete Error:',
                        error
                    );

                    window.dispatchEvent(
                        new CustomEvent('toast', {
                            detail: {
                                type: 'error',
                                title: 'Gagal Menghapus',
                                message: error.message ||
                                    'Terjadi kesalahan saat menghapus produksi.'
                            }
                        })
                    );

                    this.loading = false;
                }
            }
        }));
    });
</script>