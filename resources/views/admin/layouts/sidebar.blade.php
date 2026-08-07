<div class="flex h-screen w-64 flex-col bg-[#121212] text-white">

    {{-- ================= LOGO ================= --}}
    <div class="flex items-center px-6 pt-6 pb-4">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            {{-- Menggunakan Hanger Heroicon jika image PNG belum diset dengan benar --}}
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-[#C4902C]">
                <x-heroicon-o-tag class="h-5 w-5 text-white" />
            </div>
            <span class="text-xl font-bold tracking-wider text-white">EAZYWEAR</span>
        </a>
    </div>

    {{-- ================= MENU ================= --}}
    <div class="flex-1 overflow-y-auto px-4 py-2">

        {{-- DASHBOARD --}}
        <nav class="space-y-1">
            <a href="{{ route('admin.dashboard') }}" 
            class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-semibold transition-all duration-200 
                    {{ request()->routeIs('admin.dashboard') ? 'bg-[#C4902C] text-white shadow-md' : 'text-gray-400 hover:bg-[#C4902C] hover:text-white' }}">
                <x-heroicon-o-squares-2x2 class="h-5 w-5 shrink-0" />
                <span>Dashboard</span>
            </a>
        </nav>

        {{-- MASTER DATA --}}
        <div class="mt-6 px-2">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                MASTER DATA
            </p>
        </div>

        <nav class="mt-2 space-y-1">
            <a

                href="{{ route('admin.products') }}"

                class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200

                {{ request()->routeIs('admin.products')
                    ? 'bg-[#C99322] text-white shadow-lg'
                    : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">

                <x-heroicon-o-archive-box
                    class="h-5 w-5 shrink-0"/>

                <span>

                    Products

                </span>

            </a>

            <a

                href="{{ route('admin.categories') }}"

                class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200

                {{ request()->routeIs('admin.categories')
                    ? 'bg-[#C99322] text-white shadow-lg'
                    : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">

                <x-heroicon-o-squares-2x2
                    class="h-5 w-5 shrink-0"/>

                <span>

                    Categories

                </span>

            </a>

            <a

                href="{{ route('admin.sizes') }}"

                class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                {{ request()->routeIs('admin.sizes')
                    ? 'bg-[#C99322] text-white shadow-lg'
                    : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">

                <x-heroicon-o-scale
                    class="h-5 w-5 shrink-0" />

                <span>

                    Sizes

                </span>

            </a>
            
        </nav>

        {{-- TRANSACTIONS --}}
        <div class="mt-6 px-2">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                TRANSACTIONS
            </p>
        </div>

        <nav class="mt-2 space-y-1">
            <a
                href="{{ route('admin.transactions') }}"
                class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium transition-all duration-200
                {{ request()->routeIs('admin.transactions')
                    ? 'bg-[#C99322] text-white shadow-lg'
                    : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <x-heroicon-o-receipt-percent
                    class="h-5 w-5 shrink-0" />
                <span>
                    Transaction List
                </span>
            </a>

            <a href="#" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-400 transition-all duration-200 hover:bg-white/5 hover:text-white">
                <x-heroicon-o-code-bracket-square class="h-5 w-5 shrink-0" />
                <span>API Logs</span>
            </a>
        </nav>

        {{-- REPORTS --}}
        <div class="mt-6 px-2">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                REPORTS
            </p>
        </div>

        <nav class="mt-2 space-y-1">
            <a href="#" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-400 transition-all duration-200 hover:bg-white/5 hover:text-white">
                <x-heroicon-o-chart-bar class="h-5 w-5 shrink-0" />
                <span>Sales Report</span>
            </a>
        </nav>

        {{-- USERS --}}
        <div class="mt-6 px-2">
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">
                USERS
            </p>
        </div>

        <nav class="mt-2 space-y-1">
            <a href="#" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-400 transition-all duration-200 hover:bg-white/5 hover:text-white">
                <x-heroicon-o-users class="h-5 w-5 shrink-0" />
                <span>User Management</span>
            </a>
        </nav>

    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="px-4 py-4 space-y-1">
        <a href="#" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-400 transition-all duration-200 hover:bg-white/5 hover:text-white">
            <x-heroicon-o-user class="h-5 w-5 shrink-0" />
            <span>Profile</span>
        </a>

        <a href="#" class="flex items-center gap-3 rounded-lg px-4 py-2.5 text-sm font-medium text-red-500 transition-all duration-200 hover:bg-red-500/10">
            <x-heroicon-o-arrow-left-on-rectangle class="h-5 w-5 shrink-0 text-red-500" />
            <span>Logout</span>
        </a>
    </div>

</div>