@extends('admin.layouts.app')

@section('title', 'Transaksi')
@section('page-title', 'Transaksi')

@section('content')
<div class="space-y-8">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h2 class="text-3xl font-bold text-slate-900">Transaksi</h2>
            <p class="mt-2 text-slate-500">Kelola semua transaksi dan pembayaran pelanggan.</p>
        </div>

        <a href="{{ route('admin.transactions.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 py-3 font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F]">
            <x-heroicon-o-plus class="h-5 w-5"/>
            Transaksi Baru
        </a>
    </div>

    <form method="GET" action="{{ route('admin.transactions') }}" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5 flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-xl font-semibold text-slate-800">Filter Data</h3>
            </div>
            <span class="text-xs text-slate-400">Filter berdasarkan pelanggan dan kriteria transaksi</span>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-12">
            <div class="md:col-span-2 lg:col-span-4">
                <label class="mb-1.5 block text-xs font-medium text-slate-600">Cari Pelanggan</label>

                <div x-data="transactionCustomerSearch()" class="relative w-full">
                    <x-admin.search-input
                        name="search"
                        placeholder="Cari nama pelanggan..."
                        autocomplete="off"
                        x-model="search"
                        @focus="searchCustomers()"
                        @input.debounce.300ms="searchCustomers()"
                    />

                    <input type="hidden" name="customer_id" x-model="selectedCustomerId">

                    <div
                        x-show="showResults"
                        x-cloak
                        @click.outside="showResults=false"
                        class="absolute left-0 right-0 z-[100] mt-2 max-h-60 overflow-y-auto overflow-x-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
                    >
                        <template x-if="loading">
                            <div class="flex items-center gap-3 px-4 py-4 text-sm text-slate-400">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/>
                                    <path d="M21 12a9 9 0 0 1-9 9" stroke="currentColor" stroke-width="3"/>
                                </svg>
                                Mencari pelanggan...
                            </div>
                        </template>

                        <template x-for="customer in results" :key="customer.id">
                            <button
                                type="button"
                                @click="selectCustomer(customer)"
                                class="flex w-full items-center justify-between border-b border-slate-100 px-4 py-3 text-left transition hover:bg-slate-50 last:border-none"
                            >
                                <div class="min-w-0 pr-2">
                                    <p class="truncate text-sm font-semibold text-slate-800" x-text="customer.name"></p>

                                    <p
                                        x-show="customer.email"
                                        class="mt-0.5 truncate text-xs text-slate-400"
                                        x-text="customer.email"
                                    ></p>
                                </div>

                                <x-heroicon-o-chevron-right class="h-4 w-4 shrink-0 text-slate-400"/>
                            </button>
                        </template>

                        <template x-if="!loading && search.trim().length >= 2 && results.length === 0">
                            <div class="px-4 py-4 text-center text-sm text-slate-400">
                                Pelanggan tidak ditemukan.
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 lg:col-span-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-end">
                    <div class="w-full sm:w-52">
                        <label class="mb-1.5 block text-xs font-medium text-slate-600">Bulan</label>

                        <select
                            name="month"
                            onchange="this.form.submit()"
                            class="h-[50px] w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 transition-all duration-200 focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                        >
                            <option value="">Semua Bulan</option>
                            <option value="1" @selected(request('month') == '1')>Januari</option>
                            <option value="2" @selected(request('month') == '2')>Februari</option>
                            <option value="3" @selected(request('month') == '3')>Maret</option>
                            <option value="4" @selected(request('month') == '4')>April</option>
                            <option value="5" @selected(request('month') == '5')>Mei</option>
                            <option value="6" @selected(request('month') == '6')>Juni</option>
                            <option value="7" @selected(request('month') == '7')>Juli</option>
                            <option value="8" @selected(request('month') == '8')>Agustus</option>
                            <option value="9" @selected(request('month') == '9')>September</option>
                            <option value="10" @selected(request('month') == '10')>Oktober</option>
                            <option value="11" @selected(request('month') == '11')>November</option>
                            <option value="12" @selected(request('month') == '12')>Desember</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-40">
                        <label class="mb-1.5 block text-xs font-medium text-slate-600">Tahun</label>

                        <select
                            name="year"
                            onchange="this.form.submit()"
                            class="h-[50px] w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 transition-all duration-200 focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                        >
                            <option value="">Semua Tahun</option>

                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}" @selected(request('year') == $year)>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <a
                            href="{{ route('admin.transactions') }}"
                            title="Atur Ulang Filter"
                            class="inline-flex h-[50px] w-[50px] items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition-all duration-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 active:scale-[0.98]"
                        >
                            <x-heroicon-o-arrow-path class="h-4 w-4"/>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- ================= STATISTICS ================= --}}
    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        <x-admin.stat-card
            title="Total Transaksi"
            value="{{ number_format($totalTransactions, 0, ',', '.') }}"
            growth="{{ $transactionGrowth['value'] }}"
            :positive="$transactionGrowth['positive']"
            :neutral="$transactionGrowth['neutral']"
        >
            <x-slot:icon>
                <x-heroicon-o-receipt-percent class="h-7 w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Total Pendapatan"
            value="Rp {{ number_format($totalRevenue, 0, ',', '.') }}"
            growth="{{ $revenueGrowth['value'] }}"
            :positive="$revenueGrowth['positive']"
            :neutral="$revenueGrowth['neutral']"
        >
            <x-slot:icon>
                <x-heroicon-o-banknotes class="h-7 w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

        <x-admin.stat-card
            title="Pesanan Selesai"
            value="{{ number_format($completedOrders, 0, ',', '.') }}"
            growth="{{ $completedGrowth['value'] }}"
            :positive="$completedGrowth['positive']"
            :neutral="$completedGrowth['neutral']"
        >
            <x-slot:icon>
                <x-heroicon-o-check-badge class="h-7 w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>
    </div>

    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Faktur</th>
                        <th class="px-6 py-4">Tanggal</th>
                        <th class="px-6 py-4">Pelanggan</th>
                        <th class="px-6 py-4 text-center">Total</th>
                        <th class="px-6 py-4 text-center">Pembayaran</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200">
                    @forelse($transactions as $transaction)
                        <x-admin.transaction-row :transaction="$transaction"/>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center">
                                    <x-heroicon-o-receipt-percent class="h-10 w-10 text-slate-300"/>
                                    <p class="mt-3 font-medium text-slate-600">Tidak ada transaksi</p>
                                    <p class="mt-1 text-sm text-slate-400">
                                        Belum ada transaksi yang sesuai dengan filter.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 px-4 py-4 text-center sm:px-6 sm:py-5 md:flex-row md:text-left">
            <p class="text-xs font-medium text-slate-500 sm:text-sm">
                Menampilkan
                <span class="font-semibold text-slate-900">{{ $transactions->firstItem() ?? 0 }}</span>
                sampai
                <span class="font-semibold text-slate-900">{{ $transactions->lastItem() ?? 0 }}</span>
                dari
                <span class="font-semibold text-slate-900">{{ $transactions->total() }}</span>
                transaksi
            </p>

            <x-admin.pagination :paginator="$transactions"/>
        </div>
    </div>
</div>
@endsection

@include('admin.transactions.partials.view-transaction')
@include('admin.transactions.partials.delete-transaction')

@push('scripts')
<script>
function transactionCustomerSearch() {
    return {
        search: @js(request('search', '')),
        selectedCustomerId: @js(request('customer_id', '')),
        results: [],
        showResults: false,
        loading: false,

        async searchCustomers() {
            const keyword = this.search.trim();

            if (keyword.length < 2) {
                this.results = [];
                this.showResults = false;
                return;
            }

            this.selectedCustomerId = '';

            this.loading = true;
            this.showResults = true;

            try {
                const response = await fetch(
                    '{{ route('admin.transactions.customer-search') }}?search=' + encodeURIComponent(keyword),
                    {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                if (!response.ok) {
                    throw new Error('Gagal mencari pelanggan.');
                }

                const data = await response.json();

                this.results = data.data || [];
            } catch (error) {
                console.error('Customer Search Error:', error);
                this.results = [];
            } finally {
                this.loading = false;
            }
        },

        selectCustomer(customer) {
            const form = this.$root.closest('form');
            const url = new URL(form.action, window.location.origin);

            url.searchParams.set('search', customer.name);
            url.searchParams.set('customer_id', customer.id);

            const month = form.querySelector('[name="month"]')?.value;
            const year = form.querySelector('[name="year"]')?.value;

            if (month) {
                url.searchParams.set('month', month);
            }

            if (year) {
                url.searchParams.set('year', year);
            }

            window.location.href = url.toString();
        }
    };
}
</script>
@endpush