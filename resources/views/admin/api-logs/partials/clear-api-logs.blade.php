{{-- ================= CLEAR API LOGS MODAL ================= --}}
<div
    x-data="{
        open: false,

        openModal() {
            this.open = true;
        },

        closeModal() {
            this.open = false;
        },

        clearLogs() {
            this.closeModal();
            $dispatch('toast', {
                type: 'success',
                title: 'Logs Cleared',
                message: 'All API logs have been cleared successfully.'
            });
        }
    }"
    @open-clear-api-logs.window="openModal()"
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
                            Clear API Logs
                        </h3>
                        <p class="mt-1 text-xs text-slate-500">
                            Permanently remove all API logs.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Content --}}
            <div class="px-6 py-5">
                <div class="rounded-xl border border-red-100 bg-red-50 p-4">
                    <div class="flex gap-3">
                        <x-heroicon-o-exclamation-triangle class="h-5 w-5 shrink-0 text-red-600" />
                        <p class="text-sm leading-6 text-red-700">
                            This will permanently delete all API logs from the system. This action cannot be undone.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50 px-6 py-4">
                <button
                    type="button"
                    @click="closeModal()"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Cancel
                </button>

                <button
                    type="button"
                    @click="clearLogs()"
                    class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 active:scale-[0.98]">
                    <x-heroicon-o-trash class="h-4 w-4" />
                    Clear All Logs
                </button>
            </div>

        </div>
    </div>
</div>