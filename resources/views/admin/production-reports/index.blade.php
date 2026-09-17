
@extends('admin.layouts.app')

@section('title', 'Laporan Produksi')
@section('page-title', 'Laporan Produksi')

@section('content')

<div class="space-y-5 sm:space-y-8">

    {{-- HEADER --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                Laporan Produksi
            </h2>

            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Ringkasan kegiatan produksi berdasarkan periode dan jumlah produk.
            </p>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:flex sm:gap-3">

            {{-- *CETAK LAPORAN* --}}
            <a
                href="{{ route('admin.production-reports.print', request()->query()) }}"
                target="_blank"
                class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 active:scale-[0.98] sm:h-[50px] sm:w-auto sm:gap-2 sm:px-5 sm:text-sm"
            >
                <x-heroicon-o-printer class="h-4 w-4 shrink-0 sm:h-5 sm:w-5"/>

                <span class="truncate">
                    Cetak Laporan
                </span>
            </a>


            {{-- *EXPORT EXCEL* --}}
            <a
                href="{{ route('admin.production-reports.export', request()->query()) }}"
                class="inline-flex h-10 w-full items-center justify-center gap-1.5 rounded-xl bg-[#AE7C18] px-3 text-xs font-semibold text-white shadow-sm transition hover:bg-[#96690F] active:scale-[0.98] sm:h-[50px] sm:w-auto sm:gap-2 sm:px-5 sm:text-sm"
            >
                <x-heroicon-o-document-arrow-down class="h-4 w-4 shrink-0 sm:h-5 sm:w-5"/>

                <span class="truncate">
                    Export Excel
                </span>
            </a>

        </div>

    </div>


    {{-- FILTER --}}
    <form
        method="GET"
        action="{{ route('admin.production-reports') }}"
        class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:rounded-3xl sm:p-8"
    >

        <div class="mb-5 flex flex-col gap-1 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-center gap-2.5">

                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10 text-[#AE7C18]">
                    <x-heroicon-o-funnel class="h-5 w-5"/>
                </div>

                <div>
                    <h3 class="text-sm font-bold text-slate-800 sm:text-lg">
                        Filter Data
                    </h3>

                    <p class="text-[11px] text-slate-500 sm:hidden">
                        Filter berdasarkan periode produksi
                    </p>
                </div>

            </div>

            <span class="hidden text-xs text-slate-400 sm:inline-block">
                Filter berdasarkan periode produksi
            </span>

        </div>


        <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-12 lg:items-end">

            {{-- TANGGAL MULAI --}}
            <div class="col-span-1 lg:col-span-3">

                <label class="mb-1 block text-[11px] font-semibold text-slate-600 sm:text-xs">
                    Dari Tanggal
                </label>

                <input
                    type="date"
                    name="start_date"
                    value="{{ request('start_date') }}"
                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 text-xs font-medium text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-11 sm:px-3 sm:text-sm"
                >

            </div>


            {{-- TANGGAL SELESAI --}}
            <div class="col-span-1 lg:col-span-3">

                <label class="mb-1 block text-[11px] font-semibold text-slate-600 sm:text-xs">
                    Sampai Tanggal
                </label>

                <input
                    type="date"
                    name="end_date"
                    value="{{ request('end_date') }}"
                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 text-xs font-medium text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-11 sm:px-3 sm:text-sm"
                >

            </div>


            {{-- BULAN --}}
            <div class="col-span-1 lg:col-span-2">

                <label class="mb-1 block text-[11px] font-semibold text-slate-600 sm:text-xs">
                    Bulan
                </label>

                <select
                    name="month"
                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 text-xs font-medium text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-11 sm:px-3 sm:text-sm"
                >
                    <option value="">Semua Bulan</option>

                    @foreach([
                        1 => 'Januari',
                        2 => 'Februari',
                        3 => 'Maret',
                        4 => 'April',
                        5 => 'Mei',
                        6 => 'Juni',
                        7 => 'Juli',
                        8 => 'Agustus',
                        9 => 'September',
                        10 => 'Oktober',
                        11 => 'November',
                        12 => 'Desember',
                    ] as $month => $monthName)

                        <option
                            value="{{ $month }}"
                            @selected((string) request('month') === (string) $month)
                        >
                            {{ $monthName }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- TAHUN --}}
            <div class="col-span-1 lg:col-span-2">

                <label class="mb-1 block text-[11px] font-semibold text-slate-600 sm:text-xs">
                    Tahun
                </label>

                <select
                    name="year"
                    class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50/50 px-2.5 text-xs font-medium text-slate-700 transition focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 sm:h-11 sm:px-3 sm:text-sm"
                >
                    <option value="">Semua Tahun</option>

                    @foreach($years as $year)

                        <option
                            value="{{ $year }}"
                            @selected((string) request('year') === (string) $year)
                        >
                            {{ $year }}
                        </option>

                    @endforeach

                </select>

            </div>


            {{-- ACTION --}}
            <div class="col-span-2 lg:col-span-2">

                <div class="flex items-center gap-2">

                    <button
                        type="submit"
                        class="inline-flex h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-[#96690F] active:scale-[0.98] sm:h-11 sm:text-sm"
                    >
                        <x-heroicon-o-magnifying-glass class="h-4 w-4"/>

                        <span>
                            Filter
                        </span>
                    </button>

                    <a
                        href="{{ route('admin.production-reports') }}"
                        title="Reset Filter"
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 transition hover:bg-slate-100 active:scale-[0.98] sm:h-11 sm:w-11"
                    >
                        <x-heroicon-o-arrow-path class="h-4 w-4"/>
                    </a>

                </div>

            </div>

        </div>

    </form>


    {{-- STATISTIK --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-6 xl:grid-cols-3">

        {{-- TOTAL PRODUKSI --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm sm:p-6">

            <div class="flex items-start justify-between gap-2">

                <div class="min-w-0">

                    <p class="text-[11px] font-medium text-slate-500 sm:text-sm">
                        Total Produksi
                    </p>

                    <h3 class="mt-1 truncate text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        {{ number_format($totalProductions, 0, ',', '.') }}
                    </h3>

                </div>

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-blue-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-clipboard-document-list class="h-4 w-4 text-blue-600 sm:h-6 sm:w-6"/>
                </div>

            </div>

            <p class="mt-3 text-[10px] text-slate-400 sm:text-xs">
                Data produksi
            </p>

        </div>


        {{-- TOTAL KUANTITAS --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm sm:p-6">

            <div class="flex items-start justify-between gap-2">

                <div class="min-w-0">

                    <p class="text-[11px] font-medium text-slate-500 sm:text-sm">
                        Total Kuantitas
                    </p>

                    <h3 class="mt-1 truncate text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        {{ number_format($totalQuantity, 0, ',', '.') }}
                    </h3>

                </div>

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-amber-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-cube class="h-4 w-4 text-amber-600 sm:h-6 sm:w-6"/>
                </div>

            </div>

            <p class="mt-3 text-[10px] text-slate-400 sm:text-xs">
                Total unit produksi
            </p>

        </div>


        {{-- PRODUK SELESAI --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm sm:p-6">

            <div class="flex items-start justify-between gap-2">

                <div class="min-w-0">

                    <p class="text-[11px] font-medium text-slate-500 sm:text-sm">
                        Produksi Selesai
                    </p>

                    <h3 class="mt-1 truncate text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        {{ number_format($completedProductions, 0, ',', '.') }}
                    </h3>

                </div>

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-emerald-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-check-circle class="h-4 w-4 text-emerald-600 sm:h-6 sm:w-6"/>
                </div>

            </div>

            <p class="mt-3 text-[10px] text-slate-400 sm:text-xs">
                Status selesai
            </p>

        </div>


        {{-- DALAM PROSES
        <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-sm sm:p-6">

            <div class="flex items-start justify-between gap-2">

                <div class="min-w-0">

                    <p class="text-[11px] font-medium text-slate-500 sm:text-sm">
                        Dalam Proses
                    </p>

                    <h3 class="mt-1 truncate text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                        {{ number_format($inProgressProductions, 0, ',', '.') }}
                    </h3>

                </div>

                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-sky-100 sm:h-12 sm:w-12">
                    <x-heroicon-o-arrow-path class="h-4 w-4 text-sky-600 sm:h-6 sm:w-6"/>
                </div>

            </div>

            <p class="mt-3 text-[10px] text-slate-400 sm:text-xs">
                Status dalam proses
            </p>

        </div> --}}

    </div>


    {{-- TABEL --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">

        <div class="border-b border-slate-200 px-4 py-4 sm:px-6 sm:py-5">

            <div class="flex items-center justify-between gap-3">

                <div>
                    <h3 class="text-base font-semibold text-slate-900 sm:text-xl">
                        Daftar Produksi
                    </h3>

                    <p class="mt-0.5 text-[11px] text-slate-500 sm:text-sm">
                        Detail produksi berdasarkan periode yang dipilih.
                    </p>
                </div>

                <span class="shrink-0 rounded-full bg-[#AE7C18]/10 px-2.5 py-1 text-[10px] font-semibold text-[#AE7C18] sm:px-3 sm:text-xs">
                    {{ $productions->count() }} Data
                </span>

            </div>

        </div>


        {{-- MOBILE --}}
        <div class="divide-y divide-slate-100 md:hidden">

            @forelse($productions as $production)

                @php
                    $statusColor = match(strtolower((string) $production->status)) {
                        'planned' => 'bg-amber-100 text-amber-700',
                        'in_progress' => 'bg-sky-100 text-sky-700',
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                        default => 'bg-slate-100 text-slate-700',
                    };

                    $statusLabel = match(strtolower((string) $production->status)) {
                        'planned' => 'Direncanakan',
                        'in_progress' => 'Dalam Proses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst(str_replace('_', ' ', (string) $production->status)),
                    };
                @endphp

                <div class="space-y-3 p-4">

                    <div class="flex items-start justify-between gap-3">

                        <div class="min-w-0">

                            <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                {{ $production->production_code }}
                            </p>

                            <h4 class="mt-1 truncate text-sm font-bold text-slate-900">
                                {{ $production->product_name ?? '-' }}
                            </h4>

                            <p class="mt-0.5 text-[11px] text-slate-500">
                                {{ $production->category?->name ?? '-' }}
                            </p>

                        </div>

                        <span class="inline-flex shrink-0 rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>

                    </div>


                    <div class="rounded-xl bg-slate-50 p-3">

                        <div class="flex items-center justify-between text-xs">

                            <span class="text-slate-500">
                                Periode
                            </span>

                            <span class="font-semibold text-slate-700">
                                {{ $production->period_start ? \Carbon\Carbon::parse($production->period_start)->format('d M Y') : '-' }}
                                -
                                {{ $production->period_end ? \Carbon\Carbon::parse($production->period_end)->format('d M Y') : '-' }}
                            </span>

                        </div>

                        <div class="mt-2 flex items-start justify-between gap-3 border-t border-slate-200/70 pt-2 text-xs">

                            <span class="shrink-0 text-slate-500">
                                Ukuran
                            </span>

                            <div class="text-right font-medium text-slate-700">

                                @forelse($production->items as $item)

                                    <span class="inline-block">
                                        {{ $item->size?->name ?? '-' }}:
                                        {{ number_format((int) $item->quantity, 0, ',', '.') }} pcs
                                    </span>

                                    @if(!$loop->last)
                                        <span class="text-slate-400">, </span>
                                    @endif

                                @empty
                                    -
                                @endforelse

                            </div>

                        </div>

                        <div class="mt-2 flex items-center justify-between border-t border-slate-200/70 pt-2 text-xs">

                            <span class="text-slate-500">
                                Total
                            </span>

                            <span class="font-bold text-[#AE7C18]">
                                {{ number_format((int) $production->total_quantity, 0, ',', '.') }} pcs
                            </span>

                        </div>

                    </div>

                </div>

            @empty

                <div class="px-6 py-12 text-center">

                    <x-heroicon-o-clipboard-document-list class="mx-auto h-10 w-10 text-slate-300"/>

                    <p class="mt-3 font-medium text-slate-600">
                        Belum Ada Data Produksi
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Data produksi akan muncul di sini.
                    </p>

                </div>

            @endforelse

        </div>


        {{-- DESKTOP --}}
        <div class="hidden overflow-x-auto md:block">

            <table class="min-w-full">

                <thead class="border-b border-slate-200 bg-slate-50">

                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">

                        <th class="px-6 py-4">
                            Kode Produksi
                        </th>

                        <th class="px-6 py-4">
                            Periode
                        </th>

                        <th class="px-6 py-4">
                            Produk
                        </th>

                        <th class="px-6 py-4">
                            Kategori
                        </th>

                        <th class="px-6 py-4">
                            Ukuran
                        </th>

                        <th class="px-6 py-4 text-center">
                            Total
                        </th>

                        <th class="px-6 py-4 text-center">
                            Status
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-200">

                    @forelse($productions as $production)

                        @php
                            $statusColor = match(strtolower((string) $production->status)) {
                                'planned' => 'bg-amber-100 text-amber-700',
                                'in_progress' => 'bg-sky-100 text-sky-700',
                                'completed' => 'bg-emerald-100 text-emerald-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                default => 'bg-slate-100 text-slate-700',
                            };

                            $statusLabel = match(strtolower((string) $production->status)) {
                                'planned' => 'Direncanakan',
                                'in_progress' => 'Dalam Proses',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                default => ucfirst(str_replace('_', ' ', (string) $production->status)),
                            };
                        @endphp

                        <tr class="transition hover:bg-slate-50/60">

                            <td class="whitespace-nowrap px-6 py-4">

                                <p class="text-xs font-bold text-slate-900">
                                    {{ $production->production_code }}
                                </p>

                            </td>


                            <td class="whitespace-nowrap px-6 py-4">

                                <p class="text-xs font-medium text-slate-700">
                                    {{ $production->period_start ? \Carbon\Carbon::parse($production->period_start)->format('d M Y') : '-' }}
                                </p>

                                <p class="mt-0.5 text-[11px] text-slate-400">
                                    s/d
                                    {{ $production->period_end ? \Carbon\Carbon::parse($production->period_end)->format('d M Y') : '-' }}
                                </p>

                            </td>


                            <td class="px-6 py-4">

                                <p class="max-w-[180px] truncate text-xs font-semibold text-slate-900">
                                    {{ $production->product_name ?? '-' }}
                                </p>

                            </td>


                            <td class="whitespace-nowrap px-6 py-4 text-xs text-slate-600">
                                {{ $production->category?->name ?? '-' }}
                            </td>


                            <td class="max-w-[230px] px-6 py-4 text-xs text-slate-600">

                                @forelse($production->items as $item)

                                    <span class="inline-block">
                                        {{ $item->size?->name ?? '-' }}:
                                        {{ number_format((int) $item->quantity, 0, ',', '.') }}
                                    </span>

                                    @if(!$loop->last)
                                        <span class="text-slate-400">, </span>
                                    @endif

                                @empty
                                    -
                                @endforelse

                            </td>


                            <td class="whitespace-nowrap px-6 py-4 text-center">

                                <span class="text-xs font-bold text-[#AE7C18]">
                                    {{ number_format((int) $production->total_quantity, 0, ',', '.') }}
                                </span>

                            </td>


                            <td class="whitespace-nowrap px-6 py-4 text-center">

                                <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="px-6 py-12 text-center text-sm text-slate-400"
                            >
                                Tidak ada data produksi pada periode yang dipilih.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- FOOTER --}}
        <div class="border-t border-slate-200 px-4 py-3 sm:px-6 sm:py-4">

            <p class="text-[10px] text-slate-500 sm:text-xs">
                {{ $productions->count() }} data produksi ditampilkan.
            </p>

        </div>

    </div>

</div>

@endsection