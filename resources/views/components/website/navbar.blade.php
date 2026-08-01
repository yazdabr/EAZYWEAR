<div
    x-data="{ open: false }"
    class="relative">

{{-- ================= MOBILE MENU (DROPDOWN) ================= --}}
<div
    x-show="open"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[999] bg-slate-900/40 backdrop-blur-sm"
    style="display:none;">

    {{-- Dropdown Container (Animate from top) --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="-translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="-translate-y-full"
        @click.away="open = false"
        class="relative flex w-full max-h-[85vh] flex-col rounded-b-3xl bg-white px-6 pb-8 pt-6 shadow-2xl overflow-y-auto">

        {{-- Top Header: Logo & Close Button --}}
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <img
                src="{{ asset('images/hero/logo.png') }}"
                alt="Logo"
                class="h-9 w-auto object-contain">

            <button
                @click="open = false"
                class="group rounded-full p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 focus:outline-none">
                <x-heroicon-o-x-mark class="h-6 w-6 transition group-hover:rotate-90"/>
            </button>
        </div>

        {{-- Navigation Menu --}}
        <nav class="flex flex-col space-y-1.5 py-6">
            <a
                href="/"
                @click="open = false"
                class="flex items-center justify-between rounded-xl bg-[#AE7C18]/10 px-4 py-3 text-base font-semibold text-[#AE7C18]">
                <span>Home</span>
                <x-heroicon-o-chevron-right class="h-5 w-5"/>
            </a>

            <a
                href="/catalog"
                @click="open = false"
                class="flex items-center justify-between rounded-xl px-4 py-3 text-base font-medium text-gray-700 transition hover:bg-gray-50 hover:text-[#AE7C18]">
                <span>Catalog</span>
                <x-heroicon-o-chevron-right class="h-5 w-5 opacity-40"/>
            </a>

            <a
                href="/about"
                @click="open = false"
                class="flex items-center justify-between rounded-xl px-4 py-3 text-base font-medium text-gray-700 transition hover:bg-gray-50 hover:text-[#AE7C18]">
                <span>About</span>
                <x-heroicon-o-chevron-right class="h-5 w-5 opacity-40"/>
            </a>

            <a
                href="/contact"
                @click="open = false"
                class="flex items-center justify-between rounded-xl px-4 py-3 text-base font-medium text-gray-700 transition hover:bg-gray-50 hover:text-[#AE7C18]">
                <span>Contact</span>
                <x-heroicon-o-chevron-right class="h-5 w-5 opacity-40"/>
            </a>
        </nav>

        {{-- Bottom Actions: Search & Login --}}
        <div class="space-y-3 pt-4 border-t border-gray-100">
            {{-- Search Input / Button --}}
            <button
                class="flex w-full items-center justify-center gap-2.5 rounded-xl border border-gray-200 bg-gray-50 py-3 text-sm font-medium text-gray-600 transition hover:border-[#AE7C18] hover:bg-white hover:text-[#AE7C18]">
                <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400"/>
                <span>Cari Produk...</span>
            </button>

            {{-- Login Button --}}
            <a
                href="/login"
                class="flex w-full items-center justify-center rounded-xl bg-[#AE7C18] py-3 text-center text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/25 transition hover:bg-[#96690F] active:scale-[0.98]">
                Login / Masuk
            </a>
        </div>

    </div>
</div>

    {{-- ================= HEADER ================= --}}

    <header
        class="sticky top-0 z-50 border-b border-gray-200/70 bg-white/90 backdrop-blur-md">

        <x-ui.container>

            <nav
                class="flex h-20 items-center justify-between">

                {{-- Logo --}}
                <a href="/">

                    <img
                        src="{{ asset('images/hero/logo.png') }}"
                        class="h-16 w-auto">

                </a>

                {{-- Desktop Menu --}}
                <ul
                    class="hidden items-center gap-10 lg:flex">

                    <li>

                        <a
                            href="/"
                            class="border-b-2 border-[#AE7C18] pb-2 text-sm font-semibold text-[#AE7C18]">

                            Home

                        </a>

                    </li>

                    <li>

                        <a
                            href="/catalog"
                            class="pb-2 text-sm font-semibold transition hover:text-[#AE7C18]">

                            Catalog

                        </a>

                    </li>

                    <li>

                        <a
                            href="/about"
                            class="pb-2 text-sm font-semibold transition hover:text-[#AE7C18]">

                            About

                        </a>

                    </li>

                    <li>

                        <a
                            href="/contact"
                            class="pb-2 text-sm font-semibold transition hover:text-[#AE7C18]">

                            Contact

                        </a>

                    </li>

                </ul>

                {{-- Desktop Right --}}
                <div
                    class="hidden items-center gap-4 lg:flex">

                    <button
                        class="flex items-center gap-2 rounded-full border border-gray-300 px-4 py-2 text-sm transition hover:border-[#AE7C18] hover:text-[#AE7C18]">

                        <x-heroicon-o-magnifying-glass
                            class="h-5 w-5"/>

                        <span>

                            Search

                        </span>

                    </button>

                    <a
                        href="/login"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-[#AE7C18] text-white transition hover:bg-[#96690F]">

                        <x-heroicon-o-user
                            class="h-5 w-5"/>

                    </a>

                </div>

                {{-- Mobile --}}
                <button
                    @click="open=true"
                    class="lg:hidden">

                    <x-heroicon-o-bars-3
                        class="h-8 w-8"/>

                </button>

            </nav>

        </x-ui.container>

    </header>

</div>