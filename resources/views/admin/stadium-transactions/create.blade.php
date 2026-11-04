@extends('admin.layouts.app')

@section('title', 'Tambah Transaksi Stadion')
@section('page-title', 'Tambah Transaksi Stadion')

@section('content')
@if ($errors->any())
    <script>
        window.addEventListener('load', function () {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'error',
                    title: 'Validasi Gagal',
                    message: @js($errors->first())
                }
            }));
        });
    </script>
@endif

<div x-data="stadiumTransactionCreate()" class="space-y-5 pb-10 sm:space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900 sm:text-3xl">
                Tambah Transaksi Stadion
            </h2>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Input transaksi stadion secara manual tanpa mengurangi stok.
            </p>
        </div>

        <a
            href="{{ route('admin.stadium-transactions.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
        >
            <x-heroicon-o-arrow-left class="h-4 w-4" />
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p class="font-semibold">Transaksi belum dapat disimpan.</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        x-ref="stadiumTransactionForm"
        method="POST"
        action="{{ route('admin.stadium-transactions.store') }}"
        @submit.prevent="submitForm($event)"
        novalidate
    >
        @csrf

        <div class="space-y-5 sm:space-y-6">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
                <div class="flex items-center gap-3 border-b border-slate-200 px-4 py-3.5 sm:gap-4 sm:px-6 sm:py-5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
                        <x-heroicon-o-calendar-days class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 sm:text-lg">Informasi Transaksi</h3>
                        <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                            Tentukan tanggal transaksi dan nama pelanggan bila diperlukan.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 sm:gap-6 sm:p-6">
                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700 sm:mb-2 sm:text-sm">
                            Tanggal Transaksi <span class="text-red-500">*</span>
                        </label>
                        <input
                            x-model="transactionDate"
                            type="datetime-local"
                            name="transaction_date"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10"
                        >
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-semibold text-slate-700 sm:mb-2 sm:text-sm">
                            Nama Pelanggan <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input
                            x-model="customerName"
                            type="text"
                            name="customer_name"
                            maxlength="255"
                            placeholder="Contoh: Transaksi Stadion 30 Oktober 2026"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10"
                        >
                    </div>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:px-6 sm:py-5">
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 sm:h-12 sm:w-12">
                            <x-heroicon-o-shopping-bag class="h-5 w-5 text-[#AE7C18] sm:h-6 sm:w-6" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 sm:text-lg">Item Transaksi</h3>
                            <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                                Tambahkan satu atau beberapa item jersey stadion.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        @click="addItem()"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] active:scale-95"
                    >
                        <x-heroicon-o-plus class="h-4 w-4" />
                        Tambah Item
                    </button>
                </div>

                <div class="space-y-4 p-4 sm:space-y-5 sm:p-6">
                    <template x-if="items.length === 0">
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center">
                            <p class="text-sm font-semibold text-slate-700">Belum ada item transaksi.</p>
                            <p class="mt-1 text-xs text-slate-500">Klik tombol Tambah Item untuk mulai mengisi transaksi.</p>
                        </div>
                    </template>

                    <template x-for="(item, index) in items" :key="item.key">
                        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/60">
                            <div class="flex items-center justify-between gap-3 border-b border-slate-200 bg-white px-4 py-3">
                                <p class="text-sm font-bold text-slate-800">
                                    Item <span x-text="index + 1"></span>
                                </p>
                                <button
                                    type="button"
                                    @click="removeItem(index)"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                                >
                                    <x-heroicon-o-trash class="h-3.5 w-3.5" />
                                    Hapus
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-12 sm:p-5">
                                <div class="lg:col-span-2">
                                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Versi <span class="text-red-500">*</span></label>
                                    <select
                                        x-model="item.version"
                                        :name="`items[${index}][version]`"
                                        @change="onVersionChange(item)"
                                        required
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10"
                                    >
                                        <template x-for="version in versions()" :key="version">
                                            <option :value="version" x-text="version"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="lg:col-span-2">
                                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Kit <span class="text-red-500">*</span></label>
                                    <select
                                        x-model="item.kit"
                                        :name="`items[${index}][kit]`"
                                        @change="onKitChange(item)"
                                        required
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10"
                                    >
                                        <template x-for="kit in availableKits(item.version)" :key="kit">
                                            <option :value="kit" x-text="kit"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="lg:col-span-2">
                                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Ukuran <span class="text-red-500">*</span></label>
                                    <select
                                        x-model.number="item.size_id"
                                        :name="`items[${index}][size_id]`"
                                        required
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10"
                                    >
                                        <option value="">Pilih ukuran</option>
                                        <template x-for="size in availableSizes(item)" :key="size.size_id">
                                            <option :value="size.size_id" x-text="size.size_name"></option>
                                        </template>
                                    </select>
                                </div>

                                <div class="lg:col-span-2">
                                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Quantity <span class="text-red-500">*</span></label>
                                    <input
                                        x-model.number="item.qty"
                                        :name="`items[${index}][qty]`"
                                        type="number"
                                        min="1"
                                        required
                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10"
                                    >
                                </div>

                                <div class="lg:col-span-4">
                                    <label class="mb-1.5 block text-xs font-semibold text-slate-700">Harga per pcs</label>
                                    <div class="flex h-[42px] items-center rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-[#AE7C18]">
                                        <span x-text="formatNumber(rowPrice(item))"></span>
                                    </div>
                                </div>

                                <div class="lg:col-span-12">
                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3">
                                            <input
                                                x-model="item.nns"
                                                :name="`items[${index}][nns]`"
                                                type="checkbox"
                                                value="1"
                                                :disabled="!selectedVariant(item)?.customization_enabled"
                                                class="h-4 w-4 rounded border-slate-300 text-[#AE7C18] focus:ring-[#AE7C18] disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                            <span class="text-sm font-semibold text-slate-700">NNS</span>
                                            <span x-show="selectedVariant(item) && !selectedVariant(item)?.customization_enabled" class="ml-auto text-[11px] text-slate-400">Tidak tersedia</span>
                                        </label>

                                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3">
                                            <input
                                                x-model="item.patch"
                                                :name="`items[${index}][patch]`"
                                                type="checkbox"
                                                value="1"
                                                :disabled="!selectedVariant(item)?.patch_enabled"
                                                class="h-4 w-4 rounded border-slate-300 text-[#AE7C18] focus:ring-[#AE7C18] disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                            <span class="text-sm font-semibold text-slate-700">Patch</span>
                                            <span x-show="selectedVariant(item) && !selectedVariant(item)?.patch_enabled" class="ml-auto text-[11px] text-slate-400">Tidak tersedia</span>
                                        </label>

                                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-3 py-3">
                                            <input
                                                x-model="item.longsleeve"
                                                :name="`items[${index}][longsleeve]`"
                                                type="checkbox"
                                                value="1"
                                                :disabled="!selectedVariant(item)?.longsleeve_enabled"
                                                class="h-4 w-4 rounded border-slate-300 text-[#AE7C18] focus:ring-[#AE7C18] disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                            <span class="text-sm font-semibold text-slate-700">Longsleeve</span>
                                            <span x-show="selectedVariant(item) && !selectedVariant(item)?.longsleeve_enabled" class="ml-auto text-[11px] text-slate-400">Tidak tersedia</span>
                                        </label>
                                    </div>
                                </div>

                                <div x-show="item.nns" x-cloak class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:col-span-12">
                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Nama Jersey</label>
                                        <input
                                            x-model="item.custom_name"
                                            :name="`items[${index}][custom_name]`"
                                            type="text"
                                            maxlength="12"
                                            :disabled="!item.nns"
                                            placeholder="Maksimal 12 huruf"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm uppercase transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 disabled:bg-slate-100"
                                        >
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-semibold text-slate-700">Nomor Punggung</label>
                                        <input
                                            x-model="item.custom_number"
                                            :name="`items[${index}][custom_number]`"
                                            type="text"
                                            inputmode="numeric"
                                            maxlength="2"
                                            :disabled="!item.nns"
                                            placeholder="1-2 digit"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 disabled:bg-slate-100"
                                        >
                                    </div>
                                </div>

                                <input
                                    type="hidden"
                                    :name="`items[${index}][product_variant_id]`"
                                    :value="selectedVariant(item)?.variant_id ?? ''"
                                >

                                <div class="lg:col-span-12">
                                    <div class="flex items-center justify-between gap-3 border-t border-slate-200 pt-3">
                                        <span class="text-xs text-slate-500">
                                            Subtotal item
                                        </span>
                                        <span class="text-sm font-bold text-slate-900" x-text="formatNumber(rowSubtotal(item))"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:rounded-3xl sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Transaksi</p>
                        <p class="mt-1 text-2xl font-extrabold text-[#AE7C18]" x-text="formatNumber(subtotal)"></p>
                        <p class="mt-1 text-xs text-slate-500">
                            <span x-text="totalQty"></span> pcs · <span x-text="items.length"></span> item
                        </p>
                    </div>

                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center">
                        <a
                            href="{{ route('admin.stadium-transactions.index') }}"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                        >
                            Batal
                        </a>
                        <button
                            type="submit"
                            :disabled="submitting || items.length === 0"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <x-heroicon-o-check-circle class="h-4 w-4" />
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan Transaksi'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function stadiumTransactionCreate() {
    return {
        catalog: @js($catalog),
        transactionDate: @js(old('transaction_date', now('Asia/Makassar')->format('Y-m-d\TH:i'))),
        customerName: @js(old('customer_name', '')),
        items: [],
        submitting: false,

        init() {
            const oldItems = @js(old('items', []));

            if (Array.isArray(oldItems) && oldItems.length > 0) {
                this.items = oldItems.map((item) => ({
                    key: this.newKey(),
                    version: item.version || 'Stadium',
                    kit: item.kit || 'Home',
                    size_id: item.size_id ? Number(item.size_id) : '',
                    qty: Number(item.qty || 1),
                    nns: this.toBoolean(item.nns),
                    patch: this.toBoolean(item.patch),
                    longsleeve: this.toBoolean(item.longsleeve),
                    custom_name: item.custom_name || '',
                    custom_number: item.custom_number || '',
                }));
            } else {
                this.addItem();
            }
        },

        newKey() {
            return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
        },

        toBoolean(value) {
            return value === true || value === 1 || value === '1' || value === 'on';
        },

        async submitForm(event) {
            if (this.submitting) {
                return;
            }

            const form = event?.target ?? this.$refs.stadiumTransactionForm;

            if (!form) {
                return;
            }

            this.submitting = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                    },
                    body: new FormData(form),
                });

                const contentType = response.headers.get('content-type') || '';
                const responseText = await response.text();

                let data = {};

                if (contentType.includes('application/json')) {
                    try {
                        data = JSON.parse(responseText);
                    } catch (error) {
                        throw new Error('Response server tidak dapat diproses.');
                    }
                }

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        const firstError = Object.values(data.errors).flat()[0];

                        throw new Error(firstError || data.message || 'Data transaksi tidak valid.');
                    }

                    if (response.status === 419) {
                        throw new Error('Sesi halaman telah berakhir. Silakan refresh halaman.');
                    }

                    if (response.status === 404) {
                        throw new Error('Endpoint transaksi stadion tidak ditemukan.');
                    }

                    throw new Error(data.message || 'Gagal menyimpan transaksi stadion.');
                }

                if (!contentType.includes('application/json')) {
                    throw new Error('Server mengembalikan response yang tidak sesuai.');
                }

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        title: 'Transaksi Ditambahkan',
                        message: data.message || 'Transaksi stadion berhasil ditambahkan.',
                    },
                }));

                const redirectUrl = data.redirect
                    || '{{ route('admin.stadium-transactions.index') }}';

                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 1200);
            } catch (error) {
                console.error('Stadium Transaction Save Error:', error);

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        title: 'Gagal Menyimpan',
                        message: error.message || 'Terjadi kesalahan saat menyimpan transaksi stadion.',
                    },
                }));

                this.submitting = false;
            }
        },

        addItem() {
            this.items.push({
                key: this.newKey(),
                version: 'Stadium',
                kit: 'Home',
                size_id: '',
                qty: 1,
                nns: false,
                patch: false,
                longsleeve: false,
                custom_name: '',
                custom_number: '',
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        versions() {
            const available = [...new Set(this.catalog.map((variant) => variant.version))];
            const preferred = ['Stadium', 'Supporter'];

            return preferred
                .filter((version) => available.includes(version))
                .concat(available.filter((version) => !preferred.includes(version)));
        },

        availableKits(version) {
            const available = [...new Set(
                this.catalog
                    .filter((variant) => variant.version === version)
                    .map((variant) => variant.kit)
            )];
            const preferred = ['Home', 'Away', 'Third'];

            return preferred
                .filter((kit) => available.includes(kit))
                .concat(available.filter((kit) => !preferred.includes(kit)));
        },

        availableSizes(item) {
            const sizes = [];

            this.catalog
                .filter((variant) => (
                    variant.version === item.version
                    && variant.kit === item.kit
                ))
                .forEach((variant) => {
                    if (!sizes.some((size) => Number(size.size_id) === Number(variant.size_id))) {
                        sizes.push({
                            size_id: Number(variant.size_id),
                            size_name: variant.size_name,
                        });
                    }
                });

            return sizes;
        },

        selectedVariant(item) {
            if (!item.size_id) {
                return null;
            }

            return this.catalog.find((variant) => (
                variant.version === item.version
                && variant.kit === item.kit
                && Number(variant.size_id) === Number(item.size_id)
            )) || null;
        },

        onVersionChange(item) {
            const kits = this.availableKits(item.version);

            if (!kits.includes(item.kit)) {
                item.kit = kits[0] || '';
            }

            item.size_id = '';
        },

        onKitChange(item) {
            item.size_id = '';
        },

        rowPrice(item) {
            const variant = this.selectedVariant(item);

            if (!variant) {
                return 0;
            }

            const nnsFee = item.nns && variant.customization_enabled
                ? Number(variant.customization_price)
                : 0;
            const patchFee = item.patch && variant.patch_enabled
                ? Number(variant.patch_price)
                : 0;
            const longsleeveFee = item.longsleeve && variant.longsleeve_enabled
                ? Number(variant.longsleeve_price)
                : 0;

            return Number(variant.price) + nnsFee + patchFee + longsleeveFee;
        },

        rowSubtotal(item) {
            return this.rowPrice(item) * Number(item.qty || 0);
        },

        get subtotal() {
            return this.items.reduce((total, item) => total + this.rowSubtotal(item), 0);
        },

        get totalQty() {
            return this.items.reduce((total, item) => total + Number(item.qty || 0), 0);
        },

        formatNumber(value) {
            return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
        },
    };
}
</script>
@endpush
