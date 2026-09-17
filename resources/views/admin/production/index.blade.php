
@extends('admin.layouts.app')

@section('title', 'Produksi')
@section('page-title', 'Produksi')

@section('content')
<div class="space-y-5 sm:space-y-8">

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">
                Daftar Produksi
            </h2>
            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                Kelola dan pantau seluruh proses produksi produk.
            </p>
        </div>

        {{-- TOMBOL DESKTOP --}}
        <div class="hidden items-center gap-3 lg:flex">
            <a
                href="{{ route('admin.productions.create') }}"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-6 py-3 font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] active:scale-95"
            >
                <x-heroicon-o-plus class="h-5 w-5"/>
                Tambah Produksi
            </a>
        </div>
    </div>

    {{-- FLOATING ACTION BUTTON MOBILE --}}
    <a
        href="{{ route('admin.productions.create') }}"
        class="fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#AE7C18] text-white shadow-xl shadow-[#AE7C18]/40 transition hover:bg-[#96690F] active:scale-95 lg:hidden"
        aria-label="Tambah Produksi"
    >
        <x-heroicon-o-plus class="h-6 w-6"/>
    </a>

    {{-- ================= STATISTICS ================= --}}
    <div class="grid gap-3 sm:gap-6 md:grid-cols-2 xl:grid-cols-4">

        {{-- TOTAL PRODUKSI --}}
        <x-admin.stat-card
            title="Total Produksi"
            value="{{ number_format($totalProductions, 0, ',', '.') }}"
            growth="Seluruh data produksi"
            :positive="false"
            :neutral="true"
        >
            <x-slot:icon>
                <x-heroicon-o-clipboard-document-list class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

        {{-- TOTAL KUANTITAS --}}
        <x-admin.stat-card
            title="Total Barang"
            value="{{ number_format($totalQuantity, 0, ',', '.') }}"
            growth="Total barang diproduksi"
            :positive="true"
            :neutral="false"
        >
            <x-slot:icon>
                <x-heroicon-o-cube class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

        {{-- PRODUKSI SELESAI --}}
        <x-admin.stat-card
            title="Produksi Selesai"
            value="{{ number_format($completedProductions, 0, ',', '.') }}"
            growth="Status completed"
            :positive="true"
            :neutral="false"
        >
            <x-slot:icon>
                <x-heroicon-o-check-badge class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

        {{-- PRODUKSI BERJALAN --}}
        <x-admin.stat-card
            title="Sedang Diproses"
            value="{{ number_format($inProgressProductions, 0, ',', '.') }}"
            growth="Status in progress"
            :positive="false"
            :neutral="true"
        >
            <x-slot:icon>
                <x-heroicon-o-clock class="h-6 w-6 sm:h-7 sm:w-7"/>
            </x-slot:icon>
        </x-admin.stat-card>

    </div>

    {{-- ================= PRODUCTION LIST ================= --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">

        {{-- ================= MOBILE VIEW ================= --}}
        <div class="block divide-y divide-slate-100 md:hidden">

            @forelse($productions as $production)

                @php
                    $statusColor = match($production->status) {
                        'planned' => 'bg-amber-100 text-amber-700',
                        'in_progress' => 'bg-sky-100 text-sky-700',
                        'completed' => 'bg-emerald-100 text-emerald-700',
                        'cancelled' => 'bg-red-100 text-red-700',
                        default => 'bg-slate-100 text-slate-700',
                    };

                    $statusLabel = match($production->status) {
                        'planned' => 'Direncanakan',
                        'in_progress' => 'Sedang Diproses',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => ucfirst($production->status),
                    };

                    $periodLabel = match($production->period_type) {
                        'daily' => 'Harian',
                        'weekly' => 'Mingguan',
                        'monthly' => 'Bulanan',
                        'yearly' => 'Tahunan',
                        default => ucfirst($production->period_type),
                    };
                @endphp

                <div class="p-4 transition hover:bg-slate-50/60 active:bg-slate-50">

                    {{-- BARIS 1: KODE DAN STATUS --}}
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-900">
                                {{ $production->production_code }}
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-400">
                                {{ $production->period_start->format('d M Y') }}
                                -
                                {{ $production->period_end->format('d M Y') }}
                            </p>
                        </div>

                        <span class="inline-flex shrink-0 rounded-md px-2 py-1 text-[10px] font-bold uppercase tracking-wide {{ $statusColor }}">
                            {{ $statusLabel }}
                        </span>
                    </div>

                    {{-- BARIS 2: DETAIL PRODUK --}}
                    <div class="mt-3 rounded-xl bg-slate-50 p-3">

                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold uppercase text-slate-400">
                                    Nama Produk
                                </p>

                                <p class="mt-0.5 truncate text-sm font-semibold text-slate-800">
                                    {{ $production->product_name }}
                                </p>
                            </div>

                            <div class="shrink-0 text-right">
                                <p class="text-[10px] font-semibold uppercase text-slate-400">
                                    Total
                                </p>

                                <p class="text-sm font-bold text-[#AE7C18]">
                                    {{ number_format($production->total_quantity, 0, ',', '.') }}
                                    pcs
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-2">
                            <div>
                                <p class="text-[10px] font-semibold uppercase text-slate-400">
                                    Kategori
                                </p>

                                <p class="mt-0.5 text-xs font-medium text-slate-700">
                                    {{ $production->category->name ?? '-' }}
                                </p>
                            </div>

                            <div class="text-right">
                                <p class="text-[10px] font-semibold uppercase text-slate-400">
                                    Periode
                                </p>

                                <p class="mt-0.5 text-xs font-medium text-slate-700">
                                    {{ $periodLabel }}
                                </p>
                            </div>
                        </div>

                    </div>

                    {{-- DETAIL UKURAN --}}
                    @if($production->items->count())
                        <div class="mt-3">
                            <p class="mb-1.5 text-[10px] font-semibold uppercase text-slate-400">
                                Jumlah Per Ukuran
                            </p>

                            <div class="flex flex-wrap gap-1.5">
                                @foreach($production->items as $item)
                                    <span class="rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] font-semibold text-slate-700">
                                        {{ $item->size->name ?? '-' }}:
                                        {{ number_format($item->quantity, 0, ',', '.') }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- AKSI MOBILE --}}
                    <div class="mt-3 flex items-center justify-end gap-2">

                        @php
                            $viewData = $production->toArray();
                        @endphp
                        <button type="button" @click="$dispatch('open-view-production', @js($viewData))" class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-100 active:scale-95">
                            <x-heroicon-o-eye class="h-3.5 w-3.5 text-slate-500"/>
                            Lihat
                        </button>

                        <a
                            href="{{ route('admin.productions.edit', $production) }}"
                            class="inline-flex items-center justify-center rounded-lg border border-sky-200 bg-sky-50 p-2 text-sky-600 transition hover:bg-sky-100 active:scale-95"
                            aria-label="Edit Produksi"
                        >
                            <x-heroicon-o-pencil-square class="h-3.5 w-3.5"/>
                        </a>

                        <button
                            type="button"
                            @click="
                                window.dispatchEvent(
                                    new CustomEvent('open-delete-production', {
                                        detail: {
                                            id: @js($production->id),
                                            production_code: @js($production->production_code),
                                            product_name: @js($production->product_name),
                                            period: @js(
                                                $periodLabel
                                                . ' | '
                                                . $production->period_start->format('d/m/Y')
                                                . ' - '
                                                . $production->period_end->format('d/m/Y')
                                            ),
                                            total_quantity: @js($production->total_quantity),
                                            status: @js($statusLabel)
                                        }
                                    })
                                );
                            "
                            class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 p-2 text-red-600 transition hover:bg-red-100 active:scale-95"
                            aria-label="Hapus Produksi"
                        >
                            <x-heroicon-o-trash class="h-3.5 w-3.5"/>
                        </button>

                    </div>

                </div>

            @empty

                <div class="px-6 py-12 text-center">
                    <x-heroicon-o-clipboard-document-list class="mx-auto h-10 w-10 text-slate-300"/>

                    <p class="mt-3 font-medium text-slate-600">
                        Belum Ada Produksi
                    </p>

                    <p class="mt-1 text-xs text-slate-400">
                        Data produksi yang dibuat akan muncul di sini.
                    </p>
                </div>

            @endforelse

        </div>

        {{-- ================= DESKTOP VIEW ================= --}}
        <div class="hidden overflow-x-auto md:block">

            <table class="min-w-full">

                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Kode Produksi</th>
                        <th class="px-6 py-4">Periode</th>
                        <th class="px-6 py-4">Produk</th>
                        <th class="px-6 py-4">Kategori</th>
                        <th class="px-6 py-4 text-center">Ukuran</th>
                        <th class="px-6 py-4 text-center">Total</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200">

                    @forelse($productions as $production)

                        @php
                            $statusColor = match($production->status) {
                                'planned' => 'bg-amber-100 text-amber-700',
                                'in_progress' => 'bg-sky-100 text-sky-700',
                                'completed' => 'bg-emerald-100 text-emerald-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                default => 'bg-slate-100 text-slate-700',
                            };

                            $statusLabel = match($production->status) {
                                'planned' => 'Direncanakan',
                                'in_progress' => 'Sedang Diproses',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                default => ucfirst($production->status),
                            };

                            $periodLabel = match($production->period_type) {
                                'daily' => 'Harian',
                                'weekly' => 'Mingguan',
                                'monthly' => 'Bulanan',
                                'yearly' => 'Tahunan',
                                default => ucfirst($production->period_type),
                            };
                        @endphp

                        <tr class="transition hover:bg-slate-50/70">

                            {{-- KODE --}}
                            <td class="whitespace-nowrap px-6 py-4">
                                <p class="text-sm font-bold text-slate-900">
                                    {{ $production->production_code }}
                                </p>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ $production->created_at->format('d M Y') }}
                                </p>
                            </td>

                            {{-- PERIODE --}}
                            <td class="whitespace-nowrap px-6 py-4">
                                <p class="text-sm font-medium text-slate-700">
                                    {{ $periodLabel }}
                                </p>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ $production->period_start->format('d/m/Y') }}
                                    -
                                    {{ $production->period_end->format('d/m/Y') }}
                                </p>
                            </td>

                            {{-- PRODUK --}}
                            <td class="max-w-[180px] px-6 py-4">
                                <p class="truncate text-sm font-semibold text-slate-800">
                                    {{ $production->product_name }}
                                </p>
                            </td>

                            {{-- KATEGORI --}}
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="text-sm text-slate-600">
                                    {{ $production->category->name ?? '-' }}
                                </span>
                            </td>

                            {{-- UKURAN --}}
                            <td class="min-w-[180px] px-6 py-4">
                                <div class="flex flex-wrap justify-center gap-1">
                                    @forelse($production->items as $item)
                                        <span class="rounded-md bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-700">
                                            {{ $item->size->name ?? '-' }}:
                                            {{ number_format($item->quantity, 0, ',', '.') }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400">
                                            -
                                        </span>
                                    @endforelse
                                </div>
                            </td>

                            {{-- TOTAL --}}
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <span class="text-sm font-bold text-[#AE7C18]">
                                    {{ number_format($production->total_quantity, 0, ',', '.') }}
                                </span>

                                <span class="text-xs text-slate-400">
                                    pcs
                                </span>
                            </td>

                            {{-- STATUS --}}
                            <td class="whitespace-nowrap px-6 py-4 text-center">
                                <span class="inline-flex rounded-md px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $statusColor }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            {{-- AKSI --}}
                            <td
                                class="px-6 py-5 text-center"
                                x-data="{
                                    open: false,
                                    dropUp: false,
                                    top: 0,
                                    left: 0,
                                    width: 176,

                                    toggleDropdown(event) {
                                        const rect = event.currentTarget.getBoundingClientRect();

                                        this.open = !this.open;

                                        if (this.open) {
                                            const menuHeight = 148;

                                            this.dropUp =
                                                (window.innerHeight - rect.bottom) < menuHeight + 20;

                                            this.width = 176;

                                            this.left = rect.right - this.width;

                                            this.top = this.dropUp
                                                ? rect.top - menuHeight - 8
                                                : rect.bottom + 8;
                                        }
                                    },

                                    close() {
                                        this.open = false;
                                    }
                                }"
                                @resize.window="close()"
                                @scroll.window="close()"
                            >
                                {{-- Tombol titik tiga --}}
                                <button
                                    type="button"
                                    @click="toggleDropdown($event)"
                                    title="Aksi"
                                    class="rounded-lg p-2 transition-all duration-200 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                                    :class="open ? 'bg-slate-100' : ''"
                                >
                                    <x-heroicon-o-ellipsis-horizontal
                                        class="h-5 w-5 text-slate-500"
                                    />
                                </button>

                                {{-- Dropdown Menu --}}
                                <template x-teleport="body">
                                    <div
                                        x-show="open"
                                        x-cloak
                                        @click.outside="open = false"
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        :style="`
                                            position: fixed;
                                            top: ${top}px;
                                            left: ${left}px;
                                            width: ${width}px;
                                        `"
                                        class="z-[999999] overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-2xl shadow-slate-900/20"
                                        style="display: none;"
                                    >

                                        {{-- Lihat --}}
                                        <button type="button" @click="$dispatch('open-view-production', @js($production->toArray())); open = false" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                            <x-heroicon-o-eye class="h-4 w-4 shrink-0 text-slate-500"/>
                                            <span>Lihat</span>
                                        </button>

                                        <a
                                            href="{{ route('admin.productions.edit', $production) }}"
                                            @click="open = false"
                                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                        >
                                            <x-heroicon-o-pencil-square
                                                class="h-4 w-4 shrink-0 text-slate-500"
                                            />

                                            <span>Ubah</span>
                                        </a>

                                        {{-- Hapus --}}
                                        <button
                                            type="button"
                                            @click="
                                                window.dispatchEvent(
                                                    new CustomEvent('open-delete-production', {
                                                        detail: {
                                                            id: @js($production->id),
                                                            production_code: @js($production->production_code),
                                                            product_name: @js($production->product_name),
                                                            period: @js(
                                                                $periodLabel
                                                                . ' | '
                                                                . $production->period_start->format('d/m/Y')
                                                                . ' - '
                                                                . $production->period_end->format('d/m/Y')
                                                            ),
                                                            total_quantity: @js($production->total_quantity),
                                                            status: @js($statusLabel)
                                                        }
                                                    })
                                                );

                                                open = false;
                                            "
                                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50"
                                        >
                                            <x-heroicon-o-trash
                                                class="h-4 w-4 shrink-0"
                                            />

                                            <span>Hapus</span>
                                        </button>

                                    </div>
                                </template>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center">
                                    <x-heroicon-o-clipboard-document-list class="h-10 w-10 text-slate-300"/>

                                    <p class="mt-3 font-medium text-slate-600">
                                        Belum Ada Produksi
                                    </p>

                                    <p class="mt-1 text-sm text-slate-400">
                                        Data produksi yang dibuat akan muncul di sini.
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
                    {{ $productions->firstItem() ?? 0 }}
                </span>
                sampai
                <span class="font-semibold text-slate-900">
                    {{ $productions->lastItem() ?? 0 }}
                </span>
                dari
                <span class="font-semibold text-slate-900">
                    {{ $productions->total() }}
                </span>
                produksi
            </p>

            <x-admin.pagination :paginator="$productions"/>

        </div>

    </div>

</div>
@include('admin.production.partials.view-production')
@include('admin.production.partials.delete-production-modal')
@endsection