@extends('admin.layouts.app')

@section('title', 'Log API')

@section('page-title', 'Log API')

@section('content')
<div class="space-y-6">

    {{-- ================= PAGE HEADER ================= --}}
    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

        {{-- Title --}}
        <div class="flex items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                    Log API
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Pantau permintaan API, respons, dan aktivitas sistem.
                </p>
            </div>
        </div>

        {{-- Header Actions --}}
        <div class="flex flex-col gap-3 sm:flex-row">
            {{-- Refresh --}}
                <a
                    href="{{ route('admin.api-logs') }}"
                    title="Atur Ulang Filter"
                    class="inline-flex h-[50px] w-[50px] shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition-all duration-200 hover:border-slate-300 hover:bg-slate-100 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300 active:scale-[0.98]"
                >
                    <x-heroicon-o-arrow-path class="h-4 w-4"/>
                </a>

            <button
                type="button"
                @click="$dispatch('open-clear-api-logs')"
                class="inline-flex h-[50px] items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18] focus:ring-offset-2 active:scale-[0.98]">
                <x-heroicon-o-trash class="h-5 w-5" />
                <span>
                    Bersihkan Log
                </span>
            </button>
        </div>

    </div>

    {{-- ================= STATUS INFO ================= --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white px-6 py-4 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100">
                    <x-heroicon-o-signal class="h-5 w-5 text-emerald-600" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">
                        Monitoring API Aktif
                    </p>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Permintaan API sedang dipantau oleh sistem.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-semibold text-emerald-600">
                    Sistem Online
                </span>
            </div>
        </div>
    </div>

    {{-- ================= API LOGS SUMMARY ================= --}}
    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Total Requests --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Total Permintaan
                    </p>
                    <h3 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                        1,284
                    </h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100">
                    <x-heroicon-o-command-line class="h-6 w-6 text-blue-600" />
                </div>
            </div>
            <div class="mt-5 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700">
                    <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5" />
                    +12.4%
                </span>
                <span class="text-xs text-slate-400">
                    dibandingkan periode sebelumnya
                </span>
            </div>
        </div>

        {{-- Successful Requests --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Permintaan Berhasil
                    </p>
                    <h3 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                        1,242
                    </h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100">
                    <x-heroicon-o-check-circle class="h-6 w-6 text-emerald-600" />
                </div>
            </div>
            <div class="mt-5 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    <x-heroicon-o-arrow-trending-up class="h-3.5 w-3.5" />
                    96.7%
                </span>
                <span class="text-xs text-slate-400">
                    tingkat keberhasilan
                </span>
            </div>
        </div>

        {{-- Failed Requests --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Permintaan Gagal
                    </p>
                    <h3 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                        42
                    </h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-100">
                    <x-heroicon-o-x-circle class="h-6 w-6 text-red-600" />
                </div>
            </div>
            <div class="mt-5 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">
                    3.3%
                </span>
                <span class="text-xs text-slate-400">
                    tingkat kegagalan
                </span>
            </div>
        </div>

        {{-- Average Response Time --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">
                        Rata-rata Waktu Respon
                    </p>
                    <h3 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                        124 ms
                    </h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#AE7C18]/10">
                    <x-heroicon-o-clock class="h-6 w-6 text-[#AE7C18]" />
                </div>
            </div>
            <div class="mt-5 flex items-center gap-2">
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    <x-heroicon-o-arrow-trending-down class="h-3.5 w-3.5" />
                    -8.2%
                </span>
                <span class="text-xs text-slate-400">
                    waktu respon
                </span>
            </div>
        </div>

    </div>

    {{-- ================= API LOGS TABLE ================= --}}
    @php
    $apiLogs = [
        [
            'request_id' => 'REQ-20260808-001',
            'method' => 'GET',
            'endpoint' => '/api/products',
            'status' => 200,
            'response_time' => '84 ms',
            'ip' => '192.168.1.20',
            'date' => '08 Aug 2026',
            'time' => '09:42:18',
        ],
        [
            'request_id' => 'REQ-20260808-002',
            'method' => 'POST',
            'endpoint' => '/api/orders',
            'status' => 201,
            'response_time' => '142 ms',
            'ip' => '192.168.1.21',
            'date' => '08 Aug 2026',
            'time' => '09:40:52',
        ],
        [
            'request_id' => 'REQ-20260808-003',
            'method' => 'GET',
            'endpoint' => '/api/customers',
            'status' => 200,
            'response_time' => '96 ms',
            'ip' => '192.168.1.22',
            'date' => '08 Aug 2026',
            'time' => '09:38:41',
        ],
        [
            'request_id' => 'REQ-20260808-004',
            'method' => 'POST',
            'endpoint' => '/api/orders',
            'status' => 422,
            'response_time' => '118 ms',
            'ip' => '192.168.1.23',
            'date' => '08 Aug 2026',
            'time' => '09:35:27',
        ],
        [
            'request_id' => 'REQ-20260808-005',
            'method' => 'DELETE',
            'endpoint' => '/api/products/24',
            'status' => 500,
            'response_time' => '231 ms',
            'ip' => '192.168.1.24',
            'date' => '08 Aug 2026',
            'time' => '09:31:05',
        ],
        [
            'request_id' => 'REQ-20260808-006',
            'method' => 'PUT',
            'endpoint' => '/api/products/18',
            'status' => 200,
            'response_time' => '105 ms',
            'ip' => '192.168.1.25',
            'date' => '08 Aug 2026',
            'time' => '09:28:44',
        ],
    ];
    @endphp

    <div
        x-data="{
            currentPage: 1,
            perPage: 5,

            get totalPages() {
                return Math.ceil({{ count($apiLogs) }} / this.perPage);
            },

            get startIndex() {
                return (this.currentPage - 1) * this.perPage;
            },

            get endIndex() {
                return Math.min(
                    this.startIndex + this.perPage,
                    {{ count($apiLogs) }}
                );
            },

            goToPage(page) {
                if (page < 1 || page > this.totalPages) {
                    return;
                }
                this.currentPage = page;
            },

            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                }
            },

            previousPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            }
        }"
        class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        {{-- ================= TABLE HEADER ================= --}}
        <div class="flex flex-col gap-3 border-b border-slate-200 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-slate-900">
                    Log API
                </h3>
                <p class="mt-1 text-sm text-slate-500">
                    Permintaan API terbaru dan respons sistem.
                </p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-[#AE7C18]/10 px-3 py-1.5 text-xs font-semibold text-[#AE7C18]">
                {{ count($apiLogs) }} Log Terbaru
            </span>
        </div>

        {{-- ================= TABLE ================= --}}
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="whitespace-nowrap px-6 py-4">ID Permintaan</th>
                        <th class="px-6 py-4 text-center">Metode</th>
                        <th class="px-6 py-4">Endpoint</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="whitespace-nowrap px-6 py-4 text-center">Waktu Respon</th>
                        <th class="whitespace-nowrap px-6 py-4">Alamat IP</th>
                        <th class="whitespace-nowrap px-6 py-4">Tanggal / Waktu</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200">
                    @foreach($apiLogs as $index => $log)
                        <tr
                            x-show="{{ $index }} >= startIndex && {{ $index }} < endIndex"
                            class="transition hover:bg-slate-50">

                            {{-- Request ID --}}
                            <td class="whitespace-nowrap px-6 py-5">
                                <span class="font-semibold text-slate-900">
                                    {{ $log['request_id'] }}
                                </span>
                            </td>

                            {{-- Method --}}
                            <td class="px-6 py-5 text-center">
                                @if($log['method'] === 'GET')
                                    <span class="inline-flex rounded-lg bg-blue-100 px-2.5 py-1 text-xs font-bold text-blue-700">
                                        GET
                                    </span>
                                @elseif($log['method'] === 'POST')
                                    <span class="inline-flex rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        POST
                                    </span>
                                @elseif($log['method'] === 'PUT')
                                    <span class="inline-flex rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">
                                        PUT
                                    </span>
                                @elseif($log['method'] === 'PATCH')
                                    <span class="inline-flex rounded-lg bg-violet-100 px-2.5 py-1 text-xs font-bold text-violet-700">
                                        PATCH
                                    </span>
                                @else
                                    <span class="inline-flex rounded-lg bg-red-100 px-2.5 py-1 text-xs font-bold text-red-700">
                                        DELETE
                                    </span>
                                @endif
                            </td>

                            {{-- Endpoint --}}
                            <td class="px-6 py-5">
                                <code class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-medium text-slate-700">
                                    {{ $log['endpoint'] }}
                                </code>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-5 text-center">
                                @if($log['status'] >= 200 && $log['status'] < 300)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        {{ $log['status'] }}
                                    </span>
                                @elseif($log['status'] >= 300 && $log['status'] < 400)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        {{ $log['status'] }}
                                    </span>
                                @elseif($log['status'] >= 400 && $log['status'] < 500)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-100 px-3 py-1 text-xs font-semibold text-orange-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                        {{ $log['status'] }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        {{ $log['status'] }}
                                    </span>
                                @endif
                            </td>

                            {{-- Response Time --}}
                            <td class="whitespace-nowrap px-6 py-5 text-center">
                                <span class="text-sm font-medium text-slate-700">
                                    {{ $log['response_time'] }}
                                </span>
                            </td>

                            {{-- IP --}}
                            <td class="whitespace-nowrap px-6 py-5">
                                <code class="text-xs text-slate-500">
                                    {{ $log['ip'] }}
                                </code>
                            </td>

                            {{-- Date / Time --}}
                            <td class="whitespace-nowrap px-6 py-5">
                                <div>
                                    <p class="text-sm font-medium text-slate-700">
                                        {{ $log['date'] }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $log['time'] }}
                                    </p>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-5 text-center">
                                <div x-data="{ open: false }" class="relative inline-block text-left">
                                    {{-- Tombol Aksi --}}
                                    <button
                                        type="button"
                                        @click="open = !open"
                                        title="Aksi"
                                        class="rounded-lg p-2 transition-all duration-200 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                                        :class="open ? 'bg-slate-100' : ''">
                                        <x-heroicon-o-ellipsis-horizontal class="h-5 w-5 text-slate-500" />
                                    </button>

                                    {{-- Dropdown Aksi --}}
                                    <div
                                        x-show="open"
                                        @click.outside="open = false"
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                        class="absolute right-0 top-full z-[80] mt-2 w-44 origin-top-right overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 text-left shadow-xl shadow-slate-900/10"
                                        style="display:none;">

                                        {{-- ================= LIHAT ================= --}}
                                        <button
                                            type="button"
                                            @click="open = false; $dispatch('open-api-log', @js($log));"
                                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                            <x-heroicon-o-eye class="h-4 w-4 shrink-0 text-slate-500" />
                                            <span>Lihat</span>
                                        </button>

                                        {{-- ================= HAPUS ================= --}}
                                        <button
                                            type="button"
                                            @click="open = false; $dispatch('delete-api-log', @js($log));"
                                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50">
                                            <x-heroicon-o-trash class="h-4 w-4 shrink-0" />
                                            <span>Hapus</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ================= PAGINATION ================= --}}
        <div class="flex flex-col items-center justify-center gap-4 border-t border-slate-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">

            {{-- Information --}}
            <p class="text-center text-sm text-slate-500 sm:text-left">
                Menampilkan
                <span class="font-semibold text-slate-700" x-text="{{ count($apiLogs) }} === 0 ? 0 : startIndex + 1"></span>
                sampai
                <span class="font-semibold text-slate-700" x-text="endIndex"></span>
                dari
                <span class="font-semibold text-slate-700">{{ count($apiLogs) }}</span>
                log API
            </p>

            {{-- Pagination --}}
            <div class="flex items-center justify-center gap-1.5 sm:justify-end">
                {{-- Previous --}}
                <button
                    type="button"
                    @click="previousPage()"
                    :disabled="currentPage === 1"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                    <x-heroicon-o-chevron-left class="h-4 w-4" />
                </button>

                {{-- Page Numbers --}}
                <template x-for="page in totalPages" :key="page">
                    <button
                        type="button"
                        @click="goToPage(page)"
                        class="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-2 text-sm font-medium transition"
                        :class="currentPage === page
                            ? 'bg-[#AE7C18] text-white shadow-sm'
                            : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                        x-text="page">
                    </button>
                </template>

                {{-- Next --}}
                <button
                    type="button"
                    @click="nextPage()"
                    :disabled="currentPage === totalPages"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">
                    <x-heroicon-o-chevron-right class="h-4 w-4" />
                </button>
            </div>
        </div>

    </div>

</div>
@endsection

@include('admin.api-logs.partials.view-api-log')

@include('admin.api-logs.partials.delete-api-log')