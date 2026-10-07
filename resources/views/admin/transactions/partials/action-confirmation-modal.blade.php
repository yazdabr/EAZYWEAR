<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<div
    x-data="transactionActionModal()"
    x-cloak
    x-on:open-transaction-action.window="openAction($event.detail)"
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
            class="relative w-full max-w-[360px] sm:max-w-md rounded-2xl sm:rounded-3xl bg-white p-5 sm:p-8 shadow-2xl"
        >

            {{-- ICON --}}
            <div
                class="mx-auto flex h-14 w-14 sm:h-20 sm:w-20 items-center justify-center rounded-2xl sm:rounded-full"
                :class="iconContainerClass()"
            >
                <template x-if="action.type === 'ship'">
                    <x-heroicon-o-truck
                        class="h-7 w-7 sm:h-10 sm:w-10"
                        x-bind:class="iconClass()"
                    />
                </template>

                <template x-if="action.type === 'complete'">
                    <x-heroicon-o-check-circle
                        class="h-7 w-7 sm:h-10 sm:w-10"
                        x-bind:class="iconClass()"
                    />
                </template>
            </div>

            {{-- TEXT CONTENT --}}
            <div class="mt-4 sm:mt-6 text-center">

                <h2 class="text-lg sm:text-2xl font-bold text-slate-900">
                    <span x-text="action.title"></span>
                </h2>

                <p class="mt-2 text-xs sm:text-base leading-relaxed text-slate-600">
                    <span x-text="action.message"></span>
                </p>

                {{-- DETAIL INFO --}}
                <div class="mt-4 rounded-xl bg-slate-50 p-3.5 sm:p-4 text-left">

                    <div class="flex items-start gap-3">

                        <div
                            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                            :class="infoIconContainerClass()"
                        >
                            <template x-if="action.type === 'ship'">
                                <x-heroicon-o-truck
                                    class="h-4 w-4"
                                    x-bind:class="iconClass()"
                                />
                            </template>

                            <template x-if="action.type === 'complete'">
                                <x-heroicon-o-check-circle
                                    class="h-4 w-4"
                                    x-bind:class="iconClass()"
                                />
                            </template>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wide text-slate-400">
                                <span x-text="action.type === 'ship' ? 'Proses Pengiriman' : 'Penyelesaian Pesanan'"></span>
                            </p>

                            <p class="mt-1 text-xs sm:text-sm font-semibold text-slate-800">
                                <span x-text="action.detail"></span>
                            </p>
                        </div>

                    </div>

                </div>

                {{-- WARNING --}}
                <p
                    class="mt-3 text-[11px] sm:text-sm font-medium"
                    :class="warningClass()"
                    x-text="action.warning"
                ></p>

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
                    @click="submit()"
                    :disabled="loading"
                    class="inline-flex flex-1 h-[42px] sm:h-[48px] items-center justify-center gap-2 rounded-xl px-4 py-2.5 sm:px-5 sm:py-3 text-xs sm:text-base font-bold text-white shadow-lg transition-all duration-200 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                    :class="confirmButtonClass()"
                >

                    {{-- LOADING --}}
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

                    {{-- ICON --}}
                    <template x-if="!loading && action.type === 'ship'">
                        <x-heroicon-o-truck class="h-4 w-4" />
                    </template>

                    <template x-if="!loading && action.type === 'complete'">
                        <x-heroicon-o-check-circle class="h-4 w-4" />
                    </template>

                    <span
                        x-text="loading ? 'Memproses...' : action.confirmText"
                    ></span>

                </button>

            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('transactionActionModal', () => ({

        open: false,
        loading: false,

        action: {
            type: null,
            id: null,
            title: '',
            message: '',
            detail: '',
            warning: '',
            confirmText: ''
        },

        toggleBodyScroll() {
            document.body.classList.toggle('overflow-hidden', this.open);
        },

        openAction(data) {
            const type = data?.type ?? null;

            if (!['ship', 'complete'].includes(type)) {
                return;
            }

            this.action = {
                type: type,
                id: data?.id ?? null,

                title: type === 'ship'
                    ? 'Konfirmasi Proses Pengiriman'
                    : 'Konfirmasi Pesanan Diambil',

                message: type === 'ship'
                    ? 'Pastikan barang sudah siap untuk dikirim. Setelah dikonfirmasi, sistem akan membuat pengiriman melalui Biteship.'
                    : 'Pastikan pesanan sudah siap dan benar-benar sudah diambil oleh pelanggan. Setelah dikonfirmasi, pesanan akan langsung ditandai sebagai selesai.',

                detail: type === 'ship'
                    ? 'Kurir dan nomor resi akan dibuat otomatis oleh Biteship.'
                    : 'Pesanan akan berubah menjadi status Pesanan Selesai.',

                warning: type === 'ship'
                    ? 'Pastikan pesanan sudah siap sebelum melanjutkan proses pengiriman.'
                    : 'Tindakan ini hanya dilakukan setelah pelanggan benar-benar menerima pesanan.',

                confirmText: type === 'ship'
                    ? 'Ya, Proses Pengiriman'
                    : 'Ya, Pesanan Sudah Diambil'
            };

            this.loading = false;
            this.open = true;

            this.toggleBodyScroll();
        },

        close() {
            if (this.loading) {
                return;
            }

            this.open = false;
            this.toggleBodyScroll();
        },

        iconContainerClass() {
            return this.action.type === 'ship'
                ? 'bg-[#AE7C18]/10'
                : 'bg-emerald-100';
        },

        infoIconContainerClass() {
            return this.action.type === 'ship'
                ? 'bg-[#AE7C18]/10'
                : 'bg-emerald-100';
        },

        iconClass() {
            return this.action.type === 'ship'
                ? 'text-[#AE7C18]'
                : 'text-emerald-600';
        },

        warningClass() {
            return this.action.type === 'ship'
                ? 'text-[#AE7C18]'
                : 'text-emerald-600';
        },

        confirmButtonClass() {
            return this.action.type === 'ship'
                ? 'bg-[#AE7C18] hover:bg-[#96690F] shadow-[#AE7C18]/20 hover:shadow-[#AE7C18]/30'
                : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-500/20 hover:shadow-emerald-500/30';
        },

        async submit() {
            if (!this.action.id || this.loading) {
                return;
            }

            this.loading = true;

            const id = this.action.id;
            const type = this.action.type;

            const url = type === 'ship'
                ? '{{ url('/admin/transactions') }}/' + id + '/ship'
                : '{{ url('/admin/transactions') }}/' + id + '/complete';

            try {

                const response = await fetch(url, {
                    method: 'PATCH',

                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',

                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const contentType = response.headers.get('content-type') || '';

                if (!contentType.includes('application/json')) {
                    const text = await response.text();

                    console.error('Transaction Action Response:', {
                        status: response.status,
                        contentType: contentType,
                        response: text
                    });

                    throw new Error(
                        `Server mengembalikan response tidak valid (HTTP ${response.status}).`
                    );
                }

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(
                        data.message ||
                        'Gagal memproses transaksi.'
                    );
                }

                this.close();

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',

                        title: type === 'ship'
                            ? 'Pengiriman Berhasil Dibuat'
                            : 'Pesanan Berhasil Diselesaikan',

                        message: data.message ||
                            (
                                type === 'ship'
                                    ? 'Pengiriman berhasil dibuat melalui Biteship.'
                                    : 'Pesanan berhasil ditandai sebagai selesai.'
                            )
                    }
                }));

                setTimeout(() => {
                    window.location.reload();
                }, 600);

            } catch (error) {

                console.error('Transaction Action Error:', error);

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        title: 'Gagal Memproses',
                        message: error.message ||
                            'Terjadi kesalahan saat memproses transaksi.'
                    }
                }));

                this.loading = false;
            }
        }

    }));
});
</script>