
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
                    : (in_array($role, ['production', 'reports'], true)
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
    <div class="sidebar-menu-scroll flex-1 overflow-y-auto px-4 py-2">

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
            <div class="mt-3 px-2">
                <button
                    type="button"
                    class="sidebar-section-toggle flex w-full items-center justify-between py-1 text-left"
                    data-sidebar-toggle="data-utama"
                    aria-expanded="true"
                    aria-controls="sidebar-data-utama"
                >
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                        DATA UTAMA
                    </span>

                    <x-heroicon-o-chevron-down
                        class="sidebar-chevron h-4 w-4 text-gray-500 transition-transform duration-300"
                    />
                </button>
            </div>

            <nav
                id="sidebar-data-utama"
                class="mt-1 space-y-0.5 sidebar-section-panel"
                data-sidebar-panel
            >

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
            <div class="mt-3 px-2">
                <button
                    type="button"
                    class="sidebar-section-toggle flex w-full items-center justify-between py-1 text-left"
                    data-sidebar-toggle="transaksi"
                    aria-expanded="true"
                    aria-controls="sidebar-transaksi"
                >
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                        TRANSAKSI
                    </span>

                    <x-heroicon-o-chevron-down
                        class="sidebar-chevron h-4 w-4 text-gray-500 transition-transform duration-300"
                    />
                </button>
            </div>

            <nav
                id="sidebar-transaksi"
                class="mt-1 space-y-0.5 sidebar-section-panel"
                data-sidebar-panel
            >

                <a
                    href="{{ route('admin.transactions') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.transactions*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-receipt-percent class="h-5 w-5 shrink-0"/>
                    <span>Data Transaksi Web</span>
                </a>

                <a
                    href="{{ route('admin.offline-transactions.index') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.offline-transactions.*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 shrink-0"/>
                    <span>Data Transaksi WA</span>
                </a>

                <a
                    href="{{ route('admin.stadium-transactions.index') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.stadium-transactions.*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-trophy class="h-5 w-5 shrink-0"/>
                    <span>Data Transaksi Stadion</span>
                </a>

                <a
                    href="{{ route('admin.nns-transactions.index') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.nns-transactions.*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-tag class="h-5 w-5 shrink-0"/>
                    <span>Data Transaksi NNS</span>
                </a>

            </nav>


            {{-- PRODUKSI --}}
            <div class="mt-3 px-2">
                <button
                    type="button"
                    class="sidebar-section-toggle flex w-full items-center justify-between py-1 text-left"
                    data-sidebar-toggle="produksi"
                    aria-expanded="true"
                    aria-controls="sidebar-produksi"
                >
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                        PRODUKSI
                    </span>

                    <x-heroicon-o-chevron-down
                        class="sidebar-chevron h-4 w-4 text-gray-500 transition-transform duration-300"
                    />
                </button>
            </div>

            <nav
                id="sidebar-produksi"
                class="mt-1 space-y-0.5 sidebar-section-panel"
                data-sidebar-panel
            >

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

            {{-- DASHBOARD MANAGEMENT --}}
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


            {{-- TRANSAKSI --}}
            <div class="mt-3 px-2">
                <button
                    type="button"
                    class="sidebar-section-toggle flex w-full items-center justify-between py-1 text-left"
                    data-sidebar-toggle="transaksi"
                    aria-expanded="true"
                    aria-controls="sidebar-transaksi"
                >
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                        TRANSAKSI
                    </span>

                    <x-heroicon-o-chevron-down
                        class="sidebar-chevron h-4 w-4 text-gray-500 transition-transform duration-300"
                    />
                </button>
            </div>

            <nav
                id="sidebar-transaksi"
                class="mt-1 space-y-0.5 sidebar-section-panel"
                data-sidebar-panel
            >

                <a
                    href="{{ route('admin.transactions') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.transactions*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-receipt-percent class="h-5 w-5 shrink-0"/>
                    <span>Transaksi Web</span>
                </a>

                <a
                    href="{{ route('admin.offline-transactions.index') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.offline-transactions.*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-clipboard-document-list class="h-5 w-5 shrink-0"/>
                    <span>Transaksi WhatsApp</span>
                </a>

                <a
                    href="{{ route('admin.stadium-transactions.index') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.stadium-transactions.*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-trophy class="h-5 w-5 shrink-0"/>
                    <span>Transaksi Stadion</span>
                </a>

                <a
                    href="{{ route('admin.nns-transactions.index') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.nns-transactions.*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-tag class="h-5 w-5 shrink-0"/>
                    <span>Transaksi NNS</span>
                </a>

            </nav>


            {{-- PRODUKSI --}}
            <div class="mt-3 px-2">
                <button
                    type="button"
                    class="sidebar-section-toggle flex w-full items-center justify-between py-1 text-left"
                    data-sidebar-toggle="produksi"
                    aria-expanded="true"
                    aria-controls="sidebar-produksi"
                >
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                        PRODUKSI
                    </span>

                    <x-heroicon-o-chevron-down
                        class="sidebar-chevron h-4 w-4 text-gray-500 transition-transform duration-300"
                    />
                </button>
            </div>

            <nav
                id="sidebar-produksi"
                class="mt-1 space-y-0.5 sidebar-section-panel"
                data-sidebar-panel
            >

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

        @if (in_array($role, ['super_admin', 'management', 'production', 'reports']))

            {{-- LAPORAN PENJUALAN --}}
            <div class="mt-3 px-2">
                <button
                    type="button"
                    class="sidebar-section-toggle flex w-full items-center justify-between py-1 text-left"
                    data-sidebar-toggle="laporan-penjualan"
                    aria-expanded="true"
                    aria-controls="sidebar-laporan-penjualan"
                >
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                        LAPORAN PENJUALAN
                    </span>

                    <x-heroicon-o-chevron-down
                        class="sidebar-chevron h-4 w-4 text-gray-500 transition-transform duration-300"
                    />
                </button>
            </div>

            <nav
                id="sidebar-laporan-penjualan"
                class="mt-1 space-y-0.5 sidebar-section-panel"
                data-sidebar-panel
            >

                {{-- Laporan Penjualan Web --}}
                <a
                    href="{{ route('admin.sales-reports') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.sales-reports*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-chart-bar class="h-5 w-5 shrink-0"/>
                    <span>Penjualan Web</span>
                </a>

                {{-- Laporan Penjualan Offline --}}
                <a
                    href="{{ route('admin.offline-sales-reports') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.offline-sales-reports*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-building-storefront class="h-5 w-5 shrink-0"/>
                    <span>Penjualan WhatsApp</span>
                </a>

                {{-- Laporan Penjualan Stadion --}}
                <a
                    href="{{ route('admin.stadium-sales-reports') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.stadium-sales-reports*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-trophy class="h-5 w-5 shrink-0"/>
                    <span>Penjualan Stadion</span>
                </a>

                {{-- Laporan Penjualan NNS --}}
                <a
                    href="{{ route('admin.nns-sales-reports') }}"
                    class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                    {{ request()->routeIs('admin.nns-sales-reports*')
                        ? 'bg-[#C99322] text-white shadow-lg'
                        : 'text-gray-400 hover:bg-white/5 hover:text-white' }}"
                >
                    <x-heroicon-o-chart-bar class="h-5 w-5 shrink-0"/>
                    <span>Penjualan NNS</span>
                </a>

            </nav>


            {{-- LAPORAN PRODUKSI --}}
            <div class="mt-3 px-2">
                <button
                    type="button"
                    class="sidebar-section-toggle flex w-full items-center justify-between py-1 text-left"
                    data-sidebar-toggle="laporan-produksi"
                    aria-expanded="true"
                    aria-controls="sidebar-laporan-produksi"
                >
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                        LAPORAN PRODUKSI
                    </span>

                    <x-heroicon-o-chevron-down
                        class="sidebar-chevron h-4 w-4 text-gray-500 transition-transform duration-300"
                    />
                </button>
            </div>

            <nav
                id="sidebar-laporan-produksi"
                class="mt-1 space-y-0.5 sidebar-section-panel"
                data-sidebar-panel
            >

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


{{-- ========================================================= --}}
{{-- SIDEBAR DROPDOWN SCRIPT --}}
{{-- ========================================================= --}}

<style>
    .sidebar-section-panel {
        overflow: hidden;
        max-height: 2000px;
        opacity: 1;
        transition:
            max-height 280ms ease-in-out,
            opacity 220ms ease-in-out;
        will-change: max-height, opacity;
    }

    .sidebar-section-panel.is-collapsed {
        max-height: 0 !important;
        opacity: 0;
        pointer-events: none;
    }

    .sidebar-chevron {
        transition: transform 250ms ease-in-out;
    }

    .sidebar-section-toggle:hover .sidebar-chevron {
        color: #C4902C;
    }

    .sidebar-section-toggle:focus-visible {
        outline: 2px solid #C4902C;
        outline-offset: 4px;
        border-radius: 4px;
    }
    /* Sembunyikan scrollbar sidebar */
    .sidebar-menu-scroll {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .sidebar-menu-scroll::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }
</style>

<script>
(function () {
    function initializeSidebarDropdowns() {
        const role = @json($role);
        const storageKey = 'eazywear-sidebar-sections-' + role;

        const toggles = document.querySelectorAll(
            '[data-sidebar-toggle]'
        );

        let savedStates = {};

        try {
            savedStates = JSON.parse(
                localStorage.getItem(storageKey)
            ) || {};
        } catch (error) {
            savedStates = {};
        }

        function setPanelState(toggle, isOpen, animate = false) {
            const panelId = toggle.getAttribute('aria-controls');
            const panel = document.getElementById(panelId);
            const chevron = toggle.querySelector('.sidebar-chevron');

            if (!panel) return;

            if (animate) {
                panel.style.transition =
                    'max-height 280ms ease-in-out, opacity 220ms ease-in-out';
            } else {
                panel.style.transition = 'none';
            }

            if (isOpen) {
                panel.classList.remove('is-collapsed');
                panel.style.maxHeight = panel.scrollHeight + 'px';
                panel.style.opacity = '1';
                panel.style.pointerEvents = 'auto';
            } else {
                panel.style.maxHeight = panel.scrollHeight + 'px';

                if (animate) {
                    panel.offsetHeight;
                }

                panel.classList.add('is-collapsed');
                panel.style.maxHeight = '0px';
                panel.style.opacity = '0';
                panel.style.pointerEvents = 'none';
            }

            toggle.setAttribute(
                'aria-expanded',
                isOpen ? 'true' : 'false'
            );

            if (chevron) {
                chevron.style.transform = isOpen
                    ? 'rotate(0deg)'
                    : 'rotate(-90deg)';
            }
        }

        toggles.forEach(function (toggle) {
            const section = toggle.dataset.sidebarToggle;

            // Semua kelompok terbuka secara default.
            const isOpen = Object.prototype.hasOwnProperty.call(
                savedStates,
                section
            )
                ? savedStates[section]
                : true;

            setPanelState(toggle, isOpen);

            toggle.addEventListener('click', function () {
                const currentlyOpen =
                    toggle.getAttribute('aria-expanded') === 'true';

                const nextState = !currentlyOpen;

                setPanelState(toggle, nextState, true);

                savedStates[section] = nextState;

                try {
                    localStorage.setItem(
                        storageKey,
                        JSON.stringify(savedStates)
                    );
                } catch (error) {
                    console.warn(
                        'Sidebar preference could not be saved.',
                        error
                    );
                }
            });
        });

        // Sesuaikan tinggi panel ketika ukuran konten berubah.
        window.addEventListener('resize', function () {
            toggles.forEach(function (toggle) {
                const isOpen =
                    toggle.getAttribute('aria-expanded') === 'true';

                if (isOpen) {
                    const panel = document.getElementById(
                        toggle.getAttribute('aria-controls')
                    );

                    if (panel) {
                        panel.style.maxHeight =
                            panel.scrollHeight + 'px';
                    }
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initializeSidebarDropdowns
        );
    } else {
        initializeSidebarDropdowns();
    }
})();
</script>
