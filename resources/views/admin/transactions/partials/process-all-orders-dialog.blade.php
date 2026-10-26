<style>
    [x-cloak] { display: none !important; }
</style>

<div
    x-data="{
        open: false,
        submitting: false,
        completed: false,
        processedTotal: 0,
        failedTotal: 0,
        message: '',

        toggleBodyScroll() {
            document.body.classList.toggle('overflow-hidden', this.open);
        },

        close() {
            if (this.submitting) return;
            if (this.completed) {
                window.location.reload();
                return;
            }
            this.open = false;
            this.toggleBodyScroll();
        },

        async runBatch(event) {
            const form = event.target;
            const failedIds = new Set();

            this.submitting = true;
            this.completed = false;
            this.processedTotal = 0;
            this.failedTotal = 0;
            this.message = 'Memulai pemrosesan...';

            try {
                let hasMore = true;

                while (hasMore) {
                    const body = new FormData(form);

                    failedIds.forEach(id => {
                        body.append('skip_ids[]', id);
                    });

                    this.message =
                        'Memproses pesanan. Berhasil: ' +
                        this.processedTotal +
                        ', gagal: ' + this.failedTotal + '.';

                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(
                            data.message || 'Server menolak permintaan.'
                        );
                    }

                    this.processedTotal += Number(data.processed || 0);
                    this.failedTotal += Number(data.failed || 0);

                    (data.failed_ids || []).forEach(id => {
                        failedIds.add(String(id));
                    });

                    hasMore = Boolean(data.has_more);
                }

                this.message =
                    'Selesai. ' + this.processedTotal +
                    ' pesanan berhasil diproses dan ' +
                    this.failedTotal +
                    ' gagal. Pesanan gagal tetap perlu diperiksa.';

            } catch (error) {
                this.message =
                    'Proses terhenti atau koneksi mengalami timeout. ' +
                    'Sebagian pesanan mungkin sudah berhasil diproses. ' +
                    'Muat ulang halaman untuk memeriksa status terbaru, ' +
                    'kemudian jalankan kembali jika masih ada pesanan PAID.';

            } finally {
                this.submitting = false;
                this.completed = true;
            }
        }
    }"
    x-cloak
    x-on:open-process-all-orders-modal.window="open = true; toggleBodyScroll()"
    @keydown.escape.window="close()"
>
    <div
        x-show="open"
        x-cloak
        @click="close()"
        class="fixed inset-0 z-[200] bg-black/50 backdrop-blur-sm"
    ></div>

    <div
        x-show="open"
        x-cloak
        class="fixed inset-0 z-[201] flex items-center justify-center p-4"
        @click="close()"
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
                    Semua pesanan Website yang masih berstatus
                    <span class="font-bold text-slate-900">PAID</span>
                    akan diubah menjadi
                    <span class="font-bold text-amber-700">ORDER_PROCESSING</span>.
                </p>

                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    Pemrosesan dilakukan maksimal 10 pesanan per request.
                    Sistem mempertahankan riwayat status dan notifikasi email.
                    Proses ini tidak membuat resi Biteship.
                </p>

                <div
                    x-show="submitting || completed"
                    x-cloak
                    class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-700"
                    role="status"
                    aria-live="polite"
                >
                    <p x-text="message"></p>
                    <p x-show="submitting" class="mt-2 font-medium">
                        Berhasil: <span x-text="processedTotal"></span>
                        · Gagal: <span x-text="failedTotal"></span>
                    </p>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('admin.transactions.process-all') }}"
                class="mt-6 flex flex-col-reverse gap-3 sm:flex-row"
                @submit.prevent="runBatch($event)"
            >
                @csrf

                <button
                    type="button"
                    @click="close()"
                    :disabled="submitting"
                    x-show="!completed"
                    class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 disabled:opacity-50"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    :disabled="submitting || completed"
                    x-show="!completed"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#96690F] disabled:cursor-wait disabled:opacity-60"
                >
                    <span x-show="!submitting">Ya, Proses Semua</span>
                    <span x-show="submitting" x-cloak>Sedang Memproses...</span>
                </button>

                <button
                    type="button"
                    x-show="completed"
                    @click="window.location.reload()"
                    class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#AE7C18] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#96690F]"
                >
                    Muat Ulang Halaman
                </button>
            </form>
        </div>
    </div>
</div>