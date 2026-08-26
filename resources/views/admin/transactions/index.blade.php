@extends('admin.layouts.app')

@section('title', 'Transaksi')
@section('page-title', 'Transaksi')

@section('content')
<div class="space-y-5 sm:space-y-8">
    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">Transaksi</h2>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">Kelola semua transaksi dan pembayaran pelanggan.</p>
        </div>

        {{-- TOMBOL DEKSTOP --}}
        <a href="{{ route('admin.transactions.create') }}" class="hidden items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 py-3 font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] active:scale-95 lg:inline-flex">
            <x-heroicon-o-plus class="h-5 w-5"/>
            Transaksi Baru
        </a>
    </div>

    {{-- FLOATING ACTION BUTTON (FAB) KHUSUS MOBILE --}}
    <a
        href="{{ route('admin.transactions.create') }}"
        class="fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#AE7C18] text-white shadow-xl shadow-[#AE7C18]/40 transition hover:bg-[#96690F] active:scale-95 lg:hidden"
        aria-label="Tambah Transaksi"
    >
        <x-heroicon-o-plus class="h-6 w-6"/>
    </a>

    {{-- FILTER FORM --}}
    <form method="GET" action="{{ route('admin.transactions') }}" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:rounded-3xl sm:p-6">
        <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3 sm:mb-5 sm:pb-4">
            <div>
                <h3 class="text-base font-semibold text-slate-800 sm:text-xl">Filter Data</h3>
            </div>
            <span class="text-[11px] text-slate-400 sm:text-xs">Filter pelanggan & kriteria</span>
        </div>

        <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-12 sm:gap-4">
            <div class="md:col-span-2 lg:col-span-4">
                <label class="mb-1 block text-xs font-medium text-slate-600 sm:mb-1.5">Cari Pelanggan</label>

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
                <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-row sm:items-end sm:justify-end sm:gap-4">
                    <div class="col-span-1 w-full sm:w-52">
                        <label class="mb-1 block text-xs font-medium text-slate-600 sm:mb-1.5">Bulan</label>
                        <select
                            name="month"
                            onchange="this.form.submit()"
                            class="h-[42px] w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition-all duration-200 focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-[50px] sm:px-4 sm:text-sm"
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

                    <div class="col-span-1 w-full sm:w-40">
                        <label class="mb-1 block text-xs font-medium text-slate-600 sm:mb-1.5">Tahun</label>
                        <select
                            name="year"
                            onchange="this.form.submit()"
                            class="h-[42px] w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-700 transition-all duration-200 focus:border-[#AE7C18] focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-[50px] sm:px-4 sm:text-sm"
                        >
                            <option value="">Semua Tahun</option>
                            @for($year = now()->year; $year >= now()->year - 5; $year--)
                                <option value="{{ $year }}" @selected(request('year') == $year)>
                                    {{ $year }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-span-2 flex items-center justify-end sm:col-span-1">
                        <a
                            href="{{ route('admin.transactions') }}"
                            title="Atur Ulang Filter"
                            class="inline-flex h-[42px] w-full items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition-all duration-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 active:scale-[0.98] sm:h-[50px] sm:w-[50px]"
                        >
                            <x-heroicon-o-arrow-path class="h-4 w-4"/>
                            <span class="ml-2 text-xs font-medium sm:hidden">Reset Filter</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- ================= STATISTICS ================= --}}
    <div class="grid gap-3 sm:gap-6 md:grid-cols-2 xl:grid-cols-3">
        <x-admin.stat-card
            title="Total Transaksi"
            value="{{ number_format($totalTransactions, 0, ',', '.') }}"
            growth="{{ $transactionGrowth['value'] }}"
            :positive="$transactionGrowth['positive']"
            :neutral="$transactionGrowth['neutral']"
        >
            <x-slot:icon>
                <x-heroicon-o-receipt-percent class="h-6 w-6 sm:h-7 sm:w-7"/>
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
                <x-heroicon-o-banknotes class="h-6 w-6 sm:h-7 sm:w-7"/>
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
                <x-heroicon-o-check-badge class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>
    </div>

    {{-- ================= TRANSACTION LIST ================= --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">

        {{-- ================= MOBILE ================= --}}
        <div class="divide-y divide-slate-100 block md:hidden">
            @forelse($transactions as $transaction)
                @php
                    $total=(float)($transaction['total'] ?? 0);
                    $paymentColor=match(strtoupper($transaction['payment'] ?? '')){
                        'QRIS'=>'bg-violet-100 text-violet-700',
                        'CASH'=>'bg-emerald-100 text-emerald-700',
                        'TRANSFER'=>'bg-sky-100 text-sky-700',
                        'EDC'=>'bg-orange-100 text-orange-700',
                        default=>'bg-slate-100 text-slate-700'
                    };
                @endphp

                <div class="p-4 transition hover:bg-slate-50/60 active:bg-slate-50">
                    {{-- Baris 1: Invoice & Status --}}
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-bold text-slate-900">
                                {{ $transaction['invoice'] ?? '-' }}
                            </p>
                            <p class="mt-0.5 text-[11px] text-slate-400">
                                {{ $transaction['date'] ?? '-' }}
                            </p>
                        </div>

                        <div class="flex shrink-0 items-center gap-1.5">
                            <span class="inline-flex rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $paymentColor }}">
                                {{ $transaction['payment'] ?? '-' }}
                            </span>
                            <x-admin.badge-status status="{{ $transaction['status'] ?? '-' }}"/>
                        </div>
                    </div>

                    {{-- Baris 2: Detail Pelanggan & Total --}}
                    <div class="mt-3 flex items-center justify-between rounded-xl bg-slate-50 p-2.5">
                        <div class="min-w-0 pr-2">
                            <p class="text-[10px] uppercase font-semibold text-slate-400">Pelanggan</p>
                            <p class="truncate text-xs font-medium text-slate-800">
                                {{ $transaction['customer'] ?? '-' }}
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="text-[10px] uppercase font-semibold text-slate-400">Total Tagihan</p>
                            <p class="text-xs font-bold text-[#AE7C18]">
                                Rp {{ number_format($total,0,',','.') }}
                            </p>
                        </div>
                    </div>

                    {{-- Baris 3: Tombol Aksi --}}
                    <div class="mt-3 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="window.dispatchEvent(new CustomEvent('open-view-transaction',{
                                detail:@js($transaction)
                            }))"
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-100 active:scale-95"
                        >
                            <x-heroicon-o-eye class="h-3.5 w-3.5 text-slate-500"/>
                            Lihat
                        </button>

                        <button
                            type="button"
                            @click="window.dispatchEvent(new CustomEvent('open-delete-transaction',{
                                detail:{
                                    id:@js($transaction['id'] ?? null),
                                    invoice:@js($transaction['invoice'] ?? ''),
                                    customer:@js($transaction['customer'] ?? ''),
                                    total:@js($total),
                                    status:@js($transaction['status'] ?? '')
                                }
                            }))"
                            class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 p-2 text-xs font-medium text-red-600 transition hover:bg-red-100 active:scale-95"
                            aria-label="Hapus Transaksi"
                        >
                            <x-heroicon-o-trash class="h-3.5 w-3.5"/>
                        </button>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <x-heroicon-o-receipt-percent class="mx-auto h-10 w-10 text-slate-300"/>
                    <p class="mt-3 font-medium text-slate-600">Tidak ada transaksi</p>
                    <p class="mt-1 text-xs text-slate-400">
                        Belum ada transaksi yang sesuai dengan filter.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- ================= DESKTOP ================= --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="min-w-full">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Invoice</th>
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

        {{-- ================= PAGINATION ================= --}}
        <div class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 px-4 py-4 text-center sm:px-6 sm:py-5 md:flex-row md:text-left">
            <p class="text-xs font-medium text-slate-500 sm:text-sm">
                Menampilkan
                <span class="font-semibold text-slate-900">
                    {{ $transactions->firstItem() ?? 0 }}
                </span>
                sampai
                <span class="font-semibold text-slate-900">
                    {{ $transactions->lastItem() ?? 0 }}
                </span>
                dari
                <span class="font-semibold text-slate-900">
                    {{ $transactions->total() }}
                </span>
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