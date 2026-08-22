<div
    x-data="{
        open: false,
        scrolled: false,
        searchOpen: false
    }"
    @scroll.window="scrolled = window.scrollY > 20"
    x-effect="
        document.body.style.overflow = open ? 'hidden' : '';
        document.documentElement.style.overflowX = 'hidden';
    "
    class="relative w-full overflow-x-clip"
>
    {{-- ================= MOBILE MENU ================= --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed left-0 right-0 top-0 z-[999] h-[100dvh] w-screen overflow-hidden bg-slate-900/40 backdrop-blur-sm"
        style="display:none;"
    >
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="-translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="-translate-y-full"
            @click.away="open = false"
            class="relative flex h-auto max-h-[85vh] w-full max-w-none flex-col overflow-y-auto overflow-x-hidden rounded-b-3xl bg-white px-6 pb-8 pt-6 shadow-2xl"
        >
            {{-- Logo & Close --}}
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <img
                    src="{{ asset('images/hero/logo.png') }}"
                    alt="Logo"
                    class="h-9 w-auto object-contain"
                >

                <button
                    @click="open = false"
                    type="button"
                    class="group rounded-full p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 focus:outline-none"
                >
                    <x-heroicon-o-x-mark class="h-6 w-6 transition group-hover:rotate-90"/>
                </button>
            </div>

            {{-- Navigation --}}
            <nav class="flex flex-col space-y-1.5 py-6">
                <x-ui.nav-link
                    route="home"
                    mobile
                    @click="open = false"
                >
                    Home
                </x-ui.nav-link>

                <x-ui.nav-link
                    route="catalog"
                    mobile
                    @click="open = false"
                >
                    Catalog
                </x-ui.nav-link>

                <x-ui.nav-link
                    route="about"
                    mobile
                    @click="open = false"
                >
                    About
                </x-ui.nav-link>

                <x-ui.nav-link
                    route="contact"
                    mobile
                    @click="open = false"
                >
                    Contact
                </x-ui.nav-link>
            </nav>

            {{-- Bottom Actions --}}
            <div class="space-y-3 border-t border-gray-100 pt-4">

                {{-- Mobile Search --}}
                <form
                    method="GET"
                    action="{{ route('catalog') }}"
                    class="relative"
                >
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400"/>
                    </div>

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari produk..."
                        autocomplete="off"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-11 pr-4 text-sm font-medium text-gray-700 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-2 focus:ring-[#AE7C18]/10"
                    >
                </form>

                {{-- Login --}}
                <a
                    href="{{ route('login') }}"
                    class="flex w-full items-center justify-center rounded-xl bg-[#AE7C18] py-3 text-center text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/25 transition hover:bg-[#96690F] active:scale-[0.98]"
                >
                    Login / Masuk
                </a>
            </div>
        </div>
    </div>

    {{-- ================= HEADER ================= --}}
    <header
        :class="scrolled
            ? 'bg-white/95 backdrop-blur-xl shadow-lg border-gray-200/70'
            : 'bg-white/70 backdrop-blur-md border-transparent'"
        class="fixed inset-x-0 top-0 z-[1000] border-b transition-all duration-300"
    >
        <x-ui.container>
            <nav class="flex h-20 items-center justify-between">

                {{-- Logo --}}
                <a href="{{ route('home') }}">
                    <img
                        src="{{ asset('images/hero/logo.png') }}"
                        alt="Eazywear"
                        class="h-16 w-auto"
                    >
                </a>

                {{-- Desktop Menu --}}
                <ul class="hidden items-center gap-10 lg:flex">
                    <li>
                        <x-ui.nav-link route="home">
                            Home
                        </x-ui.nav-link>
                    </li>

                    <li>
                        <x-ui.nav-link route="catalog">
                            Catalog
                        </x-ui.nav-link>
                    </li>

                    <li>
                        <x-ui.nav-link route="about">
                            About
                        </x-ui.nav-link>
                    </li>

                    <li>
                        <x-ui.nav-link route="contact">
                            Contact
                        </x-ui.nav-link>
                    </li>
                </ul>

                {{-- Desktop Right --}}
                <div class="hidden items-center gap-4 lg:flex">

{{-- Search --}}
<form
    method="GET"
    action="{{ route('catalog') }}"
    class="relative"
    x-data="{
        search: @js(request('search', '')),
        focused: false
    }"
>
    <div
        x-cloak
        :class="focused ? 'w-40' : 'w-28'"
        class="{{ request('search') ? 'w-40' : 'w-28' }} flex items-center rounded-full border border-gray-300 transition-all duration-300 focus-within:border-[#AE7C18] focus-within:ring-2 focus-within:ring-[#AE7C18]/10"
    >
        <x-heroicon-o-magnifying-glass
            class="ml-4 h-5 w-5 shrink-0 text-gray-500"
        />

        <input
            type="text"
            name="search"
            x-model="search"
            @focus="focused = true"
            @input="focused = search.length > 0"
            placeholder="Search"
            autocomplete="off"
            class="search-navbar-input w-full min-w-0 bg-transparent px-3 py-2 text-sm outline-none placeholder:text-gray-500"
        >

        {{-- Clear --}}
        <button
            type="button"
            x-show="search.length > 0"
            x-cloak
            @click="search = ''; focused = false"
            class="mr-3 shrink-0 text-xs font-semibold text-slate-400 transition hover:text-[#AE7C18]"
        >
            Clear
        </button>
    </div>
</form>

<style>
    .search-navbar-input,
    .search-navbar-input:hover,
    .search-navbar-input:focus,
    .search-navbar-input:active {
        border: 0 !important;
        outline: none !important;
        box-shadow: none !important;
        background: transparent !important;
        appearance: none !important;
        -webkit-appearance: none !important;
    }

    .search-navbar-input:focus {
        border: 0 !important;
        outline: none !important;
        box-shadow: none !important;
    }

    [x-cloak] {
        display: none !important;
    }
</style>

                    {{-- Login --}}
                    <a
                        href="{{ route('login') }}"
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-[#AE7C18] text-white transition hover:bg-[#96690F]"
                    >
                        <x-heroicon-o-user class="h-5 w-5"/>
                    </a>
                </div>

                {{-- Mobile --}}
                <button
                    @click="open = true"
                    type="button"
                    class="lg:hidden"
                >
                    <x-heroicon-o-bars-3 class="h-8 w-8"/>
                </button>

            </nav>
        </x-ui.container>
    </header>
</div>