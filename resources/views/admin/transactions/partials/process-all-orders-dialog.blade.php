<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<div
    x-data="{
        open: false,
        submitting: false,

        toggleBodyScroll() {
            document.body.classList.toggle('overflow-hidden', this.open);
        },

        close() {
            if (this.submitting) return;
            this.open = false;
            this.toggleBodyScroll();
        }
    }"
    x-cloak
    x-on:open-process-all-orders-modal.window="
        open = true;
        toggleBodyScroll();
    "
    @keydown.escape.window="close()"
>
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition-opacity duration-200 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-150 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="fixed inset-0 z-[200] bg-black/50 backdrop-blur-sm"
    ></div>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        @click="close()"
        class="fixed inset-0 z-[201] flex items-center justify-center p-4"
    >
        <div
            @click.stop
            class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl sm:p-7"
        >
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                <x-heroicon-o-cog-6-tooth class="h-7 w-7"/>
            </div>

            <div class="mt-5 text-center">
                <h3 class="text-xl font-bold text-slate-900">
                    Proses Semua Pesanan?
                </h3>

                <p class="mt-3 text-sm leading-relaxed text-slate-600">
                    Semua pesanan web yang masih berstatus
                    <span class="font-bold text-slate-900">PAID</span>
                    akan diubah menjadi
                    <span class="font-bold text-amber-700">ORDER_PROCESSING</span>.
                </p>

                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Sistem akan menjalankan mekanisme pemberitahuan email
                    pesanan diproses. Pesanan yang sudah tidak berstatus
                    PAID tidak akan diproses ulang.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('admin.transactions.process-all') }}"
                class="mt-6 flex flex-col-reverse gap-3 sm:flex-row"
                @submit="submitting = true"
            >
                @csrf

                <button
                    type="button"
                    @click="close()"
                    :disabled="submitting"
                    class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    :disabled="submitting"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#96690F] disabled:cursor-wait disabled:opacity-60"
                >
                    <span x-show="!submitting">Ya, Proses Semua</span>
                    <span x-show="submitting" x-cloak>Sedang Memproses...</span>
                </button>
            </form>
        </div>
    </div>
</div>