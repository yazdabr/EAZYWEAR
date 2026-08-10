{{-- ================= DELETE API LOG MODAL ================= --}}
<div
    x-data="{
        open: false,
        log: null,

        openModal(event) {
            this.log = event.detail;
            this.open = true;
        },

        closeModal() {
            this.open = false;
            this.log = null;
        },

        deleteLog() {
            this.closeModal();
            $dispatch('toast', {
                type: 'error',
                title: 'Log Dihapus',
                message: 'Log API berhasil dihapus.'
            });
        }
    }"
    @delete-api-log.window="openModal($event)"
    x-show="open"
    class="relative z-[110]"
    style="display:none;">

    {{-- Backdrop --}}
    <div
        x-show="open"
        x-transition.opacity
        @click="closeModal()"
        class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm">
    </div>

    {{-- Modal --}}
    <div
        x-show="open"
        x-transition
        class="fixed inset-0 flex items-center justify-center p-4">
        <div
            @click.stop
            class="w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl">

            {{-- Header --}}
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="flex items-center gap-4">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-100">
                        <x-heroicon-o-trash class="h-5 w-5 text-red-600" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            Hapus Log API
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="px-6 py-5">
                <p class="text-sm leading-6 text-slate-600">
                    Apakah Anda yakin ingin menghapus log API ini?
                </p>

                <template x-if="log">
                    <div class="mt-4 rounded-xl bg-slate-50 p-4">
                        <div class="flex items-center justify-between gap-4">
                            <span class="text-xs font-medium text-slate-500">
                                ID Permintaan
                            </span>
                            <span
                                class="break-all text-right text-xs font-semibold text-slate-800"
                                x-text="log.request_id">
                            </span>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-4">
                            <span class="text-xs font-medium text-slate-500">
                                Endpoint
                            </span>
                            <code
                                class="break-all text-right text-xs text-slate-700"
                                x-text="log.endpoint">
                            </code>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                <button
                    type="button"
                    @click="closeModal()"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>

                <button
                    type="button"
                    @click="deleteLog()"
                    class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:scale-[0.98]">
                    <x-heroicon-o-trash class="h-4 w-4" />
                    Hapus
                </button>
            </div>

        </div>
    </div>
</div>