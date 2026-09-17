<div x-data="productionView()" x-effect="document.body.classList.toggle('overflow-hidden', open)" @keydown.escape.window="open = false" @open-view-production.window="openDrawer($event.detail)">
    {{-- Overlay --}}
    <div x-show="open" x-transition.opacity @click="open = false" class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm" style="display:none;"></div>

    {{-- Drawer --}}
    <div x-show="open" x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition transform duration-300 ease-in-out" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="fixed right-0 top-0 z-[100] flex h-screen w-full max-w-[520px] flex-col bg-white shadow-2xl" style="display:none;">

        {{-- Header --}}
        <div class="flex shrink-0 items-center justify-between border-b border-slate-200 px-4 py-3 sm:px-5 sm:py-4">
            <div class="min-w-0">
                <p class="text-xs font-medium text-slate-500">Detail Produksi</p>
                <h2 class="mt-1 truncate text-base font-bold text-slate-900 sm:text-lg" x-text="production.production_code || 'Detail Produksi'"></h2>
            </div>
            <button type="button" @click="open = false" class="ml-3 shrink-0 rounded-lg p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800">
                <x-heroicon-o-x-mark class="h-5 w-5"/>
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 space-y-3 overflow-y-auto bg-slate-50 p-3 sm:space-y-4 sm:p-4">

            {{-- Informasi Utama --}}
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="mb-3 flex items-center gap-2">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#AE7C18]/10">
                        <x-heroicon-o-clipboard-document-list class="h-4 w-4 text-[#AE7C18]"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Informasi Produksi</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Informasi dasar dan periode produksi.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-x-3 gap-y-4">
                    {{-- Kode Produksi --}}
                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Kode Produksi</p>
                        <p class="mt-1 break-all text-sm font-bold text-slate-900" x-text="production.production_code || '-'"></p>
                    </div>

                    {{-- Status --}}
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Status</p>
                        <span class="mt-1 inline-flex rounded-full px-2 py-1 text-[10px] font-bold" :class="statusClass(production.status)" x-text="statusLabel(production.status)"></span>
                    </div>

                    {{-- Nama Produk --}}
                    <div class="col-span-2">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Nama Produk</p>
                        <p class="mt-1 break-words text-sm font-semibold text-slate-900" x-text="production.product_name || production.product?.name || '-'"></p>
                    </div>

                    {{-- Kategori --}}
                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Kategori</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900" x-text="production.category?.name || production.category_name || '-'"></p>
                    </div>

                    {{-- Jenis Periode --}}
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Jenis Periode</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900" x-text="periodLabel(production.period_type)"></p>
                    </div>

                    {{-- Tanggal Mulai --}}
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Tanggal Mulai</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900" x-text="formatDate(production.period_start)"></p>
                    </div>

                    {{-- Tanggal Selesai --}}
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Tanggal Selesai</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900" x-text="formatDate(production.period_end)"></p>
                    </div>

                    {{-- Tanggal Dibuat
                    <div class="col-span-2">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Dibuat Pada</p>
                        <p class="mt-1 text-sm font-semibold text-slate-900" x-text="formatDateTime(production.created_at)"></p>
                    </div> --}}
                </div>
            </div>

            {{-- Ringkasan Produksi --}}
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="mb-3 flex items-center gap-2">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#AE7C18]/10">
                        <x-heroicon-o-cube class="h-4 w-4 text-[#AE7C18]"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Jumlah Produksi</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Rincian hasil produksi berdasarkan ukuran.</p>
                    </div>
                </div>

                {{-- Total --}}
                <div class="mb-3 flex items-center justify-between gap-3 rounded-xl border border-[#AE7C18]/20 bg-[#AE7C18]/5 px-3 py-3">
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Produksi</p>
                        <p class="mt-1 text-xs text-slate-500">Seluruh ukuran</p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-black text-[#AE7C18]" x-text="formatNumber(production.total_quantity)"></p>
                        <p class="text-xs font-medium text-slate-500">pcs</p>
                    </div>
                </div>

                {{-- Mobile Cards --}}
                <div class="space-y-2 sm:hidden">
                    <template x-for="(item, index) in production.items" :key="index">
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-100 bg-slate-50/70 px-3 py-2.5">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-900">Ukuran <span x-text="item.size?.name || '-'"></span></p>
                                <p class="mt-0.5 text-xs text-slate-500">Jumlah produksi</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-base font-bold text-[#AE7C18]" x-text="formatNumber(item.quantity)"></p>
                                <p class="text-[10px] font-medium text-slate-500">pcs</p>
                            </div>
                        </div>
                    </template>

                    <template x-if="!production.items.length">
                        <div class="py-5 text-center text-xs text-slate-400">Tidak ada rincian ukuran produksi.</div>
                    </template>
                </div>

                {{-- Desktop Table --}}
                <div class="hidden overflow-x-auto sm:block">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wider text-slate-500">
                                <th class="pb-2">Ukuran</th>
                                <th class="pb-2 text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in production.items" :key="index">
                                <tr>
                                    <td class="py-3 text-sm font-medium text-slate-900">
                                        <span x-text="item.size?.name || '-'"></span>
                                    </td>
                                    <td class="py-3 text-right text-sm font-bold text-slate-900" x-text="formatNumber(item.quantity) + ' pcs'"></td>
                                </tr>
                            </template>

                            <template x-if="!production.items.length">
                                <tr>
                                    <td colspan="2" class="py-6 text-center text-sm text-slate-400">Tidak ada rincian ukuran produksi.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Catatan Produksi --}}
            <div class="rounded-xl border border-slate-200 bg-white p-4">
                <div class="mb-3 flex items-center gap-2">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#AE7C18]/10">
                        <x-heroicon-o-document-text class="h-4 w-4 text-[#AE7C18]"/>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Catatan Produksi</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Catatan tambahan produksi.</p>
                    </div>
                </div>

                <div class="rounded-lg border border-slate-100 bg-slate-50/70 p-3">
                    <p class="whitespace-pre-line break-words text-sm leading-6 text-slate-700" x-text="production.notes || 'Tidak ada catatan produksi.'"></p>
                </div>
            </div>
        </div>

        {{-- *FOOTER* --}}
        <div class="shrink-0 border-t border-slate-200 bg-white px-5 py-4 sm:px-6 sm:py-5">

            <div class="flex">

                <button
                    type="button"
                    @click="open = false"
                    class="h-[42px] w-full rounded-xl bg-[#AE7C18] px-4 py-2.5 text-xs font-bold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-200 hover:bg-[#96690F] hover:shadow-xl hover:shadow-[#AE7C18]/30 active:scale-95 sm:h-[48px] sm:px-5 sm:py-3 sm:text-base"
                >
                    Tutup
                </button>

            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    function productionView() {
        return {
            open: false,
            production: {
                id: null,
                production_code: '',
                product_id: null,
                product_name: '',
                product: null,
                category: null,
                category_name: '',
                period_type: '',
                period_start: '',
                period_end: '',
                total_quantity: 0,
                status: 'planned',
                notes: '',
                created_at: '',
                items: []
            },
            openDrawer(data) {
                console.log('VIEW PRODUCTION DATA:', data);
                this.production = {
                    id: data?.id ?? null,
                    production_code: data?.production_code ?? '',
                    product_id: data?.product_id ?? null,
                    product_name: data?.product_name ?? '',
                    product: data?.product ?? null,
                    category: data?.category ?? null,
                    category_name: data?.category_name ?? '',
                    period_type: data?.period_type ?? '',
                    period_start: data?.period_start ?? '',
                    period_end: data?.period_end ?? '',
                    total_quantity: data?.total_quantity ?? 0,
                    status: data?.status ?? 'planned',
                    notes: data?.notes ?? '',
                    created_at: data?.created_at ?? '',
                    items: Array.isArray(data?.items) ? data.items : []
                };
                this.open = true;
            },
            formatNumber(value) {
                return Number(value || 0).toLocaleString('id-ID');
            },
            formatDate(value) {
                if (!value) return '-';
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return value;
                return date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                });
            },
            formatDateTime(value) {
                if (!value) return '-';
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) return value;
                return date.toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            },
            periodLabel(value) {
                const labels = {
                    daily: 'Harian',
                    weekly: 'Mingguan',
                    monthly: 'Bulanan',
                    yearly: 'Tahunan'
                };
                return labels[value] || value || '-';
            },
            statusLabel(value) {
                const labels = {
                    planned: 'Direncanakan',
                    in_progress: 'Sedang Diproses',
                    completed: 'Selesai',
                    cancelled: 'Dibatalkan'
                };
                return labels[value] || value || '-';
            },
            statusClass(value) {
                const classes = {
                    planned: 'bg-amber-100 text-amber-700',
                    in_progress: 'bg-blue-100 text-blue-700',
                    completed: 'bg-emerald-100 text-emerald-700',
                    cancelled: 'bg-red-100 text-red-700'
                };
                return classes[value] || 'bg-slate-100 text-slate-700';
            }
        };
    }
</script>
@endpush