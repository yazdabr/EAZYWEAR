@php
    $role = auth()->user()->role;
@endphp

<div class="flex h-screen w-64 flex-col bg-[#121212] text-white">

    {{-- LOGO --}}
    <div class="flex items-center px-6 pb-4 pt-6">
        <a
            href="{{
                $role === 'super_admin'
                    ? route('admin.dashboard')
                    : ($role === 'production'
                        ? route('admin.production-reports')
                        : route('admin.transactions'))
            }}"
            class="flex items-center gap-3"
        >
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#C4902C]">
                <x-heroicon-o-tag class="h-5 w-5 text-white"/>
            </div>

            <span class="text-xl font-bold tracking-wider text-white">
                EAZYWEAR
            </span>
        </a>
    </div>


    {{-- MENU CONTENT --}}
    <div class="flex-1 overflow-y-auto px-4 py-2">

        {{-- ========================================================= --}}
        {{-- SUPER ADMIN --}}
        {{-- ========================================================= --}}

        @if ($role === 'super_admin')

            {{-- DASHBOARD --}}
            <nav class="space-y-1">
                <a
                    href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition-all duration-200
                    {{ request()->routeIs('admin.dashboard')
                        ? 'bg-[#C4902C] text-white shadow-md'
                        : 'text-gray-400 hover:bg-[#C4902C] hover:text-white' }}"
                >
                    <x-heroicon-o-squares-2x2 class="h-5 w-5 shrink-0"/>
                    <span>Dashboard</span>
                </a>
            </nav>


            {{-- DATA UTAMA --}}
            <div class="mt-6 px-2">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                    DATA UTAMA
                </p>
            </div>

            <nav class="mt-2 space-y-1">

                {{-- Produk --}}
                <a
                    href="{{ route('admin.products') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.products*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-archive-box class="h-5 w-5 shrink-0"/>
                    <span>Produk</span>
                </a>

                {{-- Kategori --}}
                <a
                    href="{{ route('admin.categories') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.categories*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-squares-2x2 class="h-5 w-5 shrink-0"/>
                    <span>Kategori</span>
                </a>

                {{-- Ukuran --}}
                <a
                    href="{{ route('admin.sizes') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.sizes*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-scale class="h-5 w-5 shrink-0"/>
                    <span>Ukuran</span>
                </a>

            </nav>


            {{-- TRANSAKSI --}}
            <div class="mt-6 px-2">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                    TRANSAKSI
                </p>
            </div>

            <nav class="mt-2 space-y-1">

                <a
                    href="{{ route('admin.transactions') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.transactions*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-receipt-percent class="h-5 w-5 shrink-0"/>
                    <span>Daftar Transaksi</span>
                </a>

            </nav>


            {{-- PRODUKSI --}}
            <div class="mt-6 px-2">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                    PRODUKSI
                </p>
            </div>

            <nav class="mt-2 space-y-1">

                <a
                    href="{{ route('admin.productions') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.productions*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 shrink-0"/>
                    <span>Daftar Produksi</span>
                </a>

            </nav>

        @endif


        {{-- ========================================================= --}}
        {{-- MANAGEMENT --}}
        {{-- ========================================================= --}}

        @if ($role === 'management')

            {{-- TRANSAKSI --}}
            <div class="mt-2 px-2">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                    TRANSAKSI
                </p>
            </div>

            <nav class="mt-2 space-y-1">

                <a
                    href="{{ route('admin.transactions') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.transactions*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-receipt-percent class="h-5 w-5 shrink-0"/>
                    <span>Daftar Transaksi</span>
                </a>

            </nav>


            {{-- PRODUKSI --}}
            <div class="mt-6 px-2">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                    PRODUKSI
                </p>
            </div>

            <nav class="mt-2 space-y-1">

                <a
                    href="{{ route('admin.productions') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.productions*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 shrink-0"/>
                    <span>Daftar Produksi</span>
                </a>

            </nav>

        @endif

        {{-- ========================================================= --}}
        {{-- LAPORAN --}}
        {{-- ========================================================= --}}

        @if (in_array($role, ['super_admin', 'management', 'production']))

            <div class="mt-6 px-2">
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                    LAPORAN
                </p>
            </div>

            <nav class="mt-2 space-y-1">

                {{-- Laporan Penjualan --}}
                @if (in_array($role, ['super_admin', 'management']))

                    <a
                        href="{{ route('admin.sales-reports') }}"
                        class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                        {{ request()->routeIs('admin.sales-reports*')
                            ? 'bg-[#C99322] text-white shadow-lg'
                            : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                    >
                        <x-heroicon-o-chart-bar class="h-5 w-5 shrink-0"/>
                        <span>Laporan Penjualan</span>
                    </a>

                @endif


                {{-- Laporan Produksi --}}
                <a
                    href="{{ route('admin.production-reports') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.production-reports*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 shrink-0"/>
                    <span>Laporan Produksi</span>
                </a>

            </nav>

        @endif

    </div>

</div>