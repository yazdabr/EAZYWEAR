<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<div
    x-data="{
        open: false,
        toggleBodyScroll() {
            document.body.classList.toggle('overflow-hidden', this.open);
        }
    }"
    x-cloak
    x-on:open-hide-status-modal.window="open = true; toggleBodyScroll()"
    @keydown.escape.window="if(open) { open = false; toggleBodyScroll(); }"
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
        @click="open = false; toggleBodyScroll()"
        class="fixed inset-0 z-[200] bg-black/50 backdrop-blur-sm"
    ></div>

    {{-- MODAL --}}
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
            class="w-full max-w-md max-h-[90dvh] overflow-y-auto rounded-2xl sm:rounded-3xl bg-white p-5 sm:p-7 shadow-2xl"
        >
            <form method="POST" action="{{ route('admin.transactions.hidden-statuses.update') }}">
                @csrf
                @method('PUT')
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Hide Status Transaksi</h3>
                        <p class="mt-1 text-sm text-slate-500">Pilihan ini tersimpan untuk akun admin kamu, termasuk setelah logout.</p>
                    </div>
                    <button type="button" @click="open = false; toggleBodyScroll()" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-700" aria-label="Tutup">
                        <x-heroicon-o-x-mark class="h-5 w-5"/>
                    </button>
                </div>
                @php
                    $hideableStatuses = [
                        'PENDING' => 'Pending',
                        'PAID' => 'Paid',
                        'EXPIRED' => 'Expired',
                        'ORDER_PROCESSING' => 'Order Processing',
                        'ORDER_SHIPPED' => 'Order Shipped',
                        'ORDER_COMPLETED' => 'Order Completed',
                        'CANCELLED' => 'Cancelled',
                        'ORDER_CANCELLED' => 'Order Cancelled',
                    ];
                    $hiddenStatuses = auth()->user()->hidden_transaction_statuses ?? [];
                @endphp
                <div class="mt-5 space-y-2">
                    @foreach($hideableStatuses as $statusValue => $statusLabel)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                            <input type="checkbox" name="hidden_statuses[]" value="{{ $statusValue }}" @checked(in_array($statusValue, $hiddenStatuses, true)) class="rounded border-slate-300 text-[#AE7C18] focus:ring-[#AE7C18]">
                            <span>{{ $statusLabel }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-5 grid grid-cols-2 gap-2">
                    <button type="button" @click="open = false; toggleBodyScroll()" class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="rounded-xl bg-[#AE7C18] px-4 py-3 text-sm font-semibold text-white hover:bg-[#96690F]">Simpan</button>
                </div>
                <p class="mt-3 text-xs text-slate-400">Jika semua centang dihapus lalu disimpan, seluruh status akan ditampilkan kembali.</p>
            </form>
        </div>
    </div>
</div>