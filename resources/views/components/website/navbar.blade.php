<div x-data="{open:false,scrolled:false,searchOpen:false}" @scroll.window="scrolled=window.scrollY>20" x-effect="document.body.style.overflow=open?'hidden':'';document.documentElement.style.overflowX='hidden';" class="relative w-full overflow-x-clip">
    {{-- MOBILE MENU --}}
    <div id="mobile-menu" x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed left-0 right-0 top-0 z-[999] h-[100dvh] w-screen overflow-hidden bg-slate-900/40 backdrop-blur-sm" style="display:none;">
        <div x-show="open" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="-translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-200 transform" x-transition:leave-start="translate-y-0" x-transition:leave-end="-translate-y-full" @click.away="open=false" class="relative flex h-auto max-h-[85vh] w-full max-w-none flex-col overflow-y-auto overflow-x-hidden rounded-b-3xl bg-white px-6 pb-8 pt-6 shadow-2xl">
            {{-- Logo & Close --}}
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <a href="{{ route('home') }}" aria-label="Eazywear Indonesia - Home">
                    <img src="{{ asset('images/hero/logo-navbar.webp') }}" alt="Eazywear" width="450" height="318" class="h-9 w-auto object-contain">
                </a>
                <button @click="open=false" type="button" aria-label="Close navigation menu" aria-controls="mobile-menu" :aria-expanded="open.toString()" class="group rounded-full p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 focus:outline-none">
                    <x-heroicon-o-x-mark class="h-6 w-6 transition group-hover:rotate-90"/>
                </button>
            </div>
            {{-- Navigation --}}
            <nav aria-label="Mobile navigation" class="flex flex-col space-y-1.5 py-6">
                <x-ui.nav-link route="home" mobile @click="open=false">Home</x-ui.nav-link>
                <x-ui.nav-link route="catalog" mobile @click="open=false">Catalog</x-ui.nav-link>
                <x-ui.nav-link route="about" mobile @click="open=false">About</x-ui.nav-link>
                <x-ui.nav-link route="contact" mobile @click="open=false">Contact</x-ui.nav-link>
            </nav>
            {{-- Bottom Actions --}}
            <div class="space-y-3 border-t border-gray-100 pt-4">
                {{-- Mobile Search --}}
                <form method="GET" action="{{ route('catalog') }}" class="relative" role="search">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <x-heroicon-o-magnifying-glass class="h-5 w-5 text-gray-400"/>
                    </div>
                    <label for="mobile-search" class="sr-only">Search products</label>
                    <input id="mobile-search" type="text" name="search" value="{{ request('search') }}" placeholder="Search Products..." autocomplete="off" class="w-full rounded-xl border border-gray-200 bg-gray-50 py-3 pl-11 pr-4 text-sm font-medium text-gray-700 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-2 focus:ring-[#AE7C18]/10">
                </form>
                {{-- Mobile Cart --}}
                @php $cartCount=collect(session('cart',[]))->sum('qty'); @endphp
                <a id="navbar-cart-mobile" href="{{ route('cart.index') }}" @click="open=false" aria-label="View shopping cart{{ $cartCount>0 ? ', '.$cartCount.' items' : '' }}" class="flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-3 text-center text-sm font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-800 active:scale-[0.98]">
                    <x-heroicon-o-shopping-cart class="h-5 w-5" aria-hidden="true"/>
                    <span>Cart @if($cartCount>0)({{ $cartCount }})@endif</span>
                </a>
            </div>
        </div>
    </div>

    {{-- MOBILE FLOATING CART --}}
    @php
        $floatingCart=collect(session('cart',[]));
        $floatingCartCount=$floatingCart->sum('qty');
        $floatingCartTotal=$floatingCart->sum(function($item){return (float)$item['price']*(int)$item['qty'];});
    @endphp
    @if($floatingCartCount>0&&!request()->routeIs('cart.index')&&!request()->routeIs('checkout.index'))
        <div x-data="{visible:true}" x-show="visible" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="translate-y-20 opacity-0 scale-95" x-transition:enter-end="translate-y-0 opacity-100 scale-100" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0 opacity-100 scale-100" x-transition:leave-end="translate-y-20 opacity-0 scale-95" class="fixed inset-x-4 bottom-4 z-[1100] lg:hidden" style="display:none;">
            <a href="{{ route('cart.index') }}" aria-label="View cart with {{ $floatingCartCount }} items, total Rp {{ number_format($floatingCartTotal,0,',','.') }}" class="flex min-h-[64px] items-center gap-3 rounded-2xl bg-slate-900 px-4 py-3 text-white shadow-[0_12px_35px_rgba(0,0,0,0.22)] ring-1 ring-black/5 transition active:scale-[0.98]">
                <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15">
                    <x-heroicon-o-shopping-cart class="h-6 w-6" aria-hidden="true"/>
                    <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-white px-1 text-[10px] font-bold text-slate-900 shadow-sm" aria-hidden="true">{{ $floatingCartCount>99?'99+':$floatingCartCount }}</span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-medium text-white/80">Keranjang</p>
                    <p class="truncate text-sm font-bold text-white">{{ $floatingCartCount }} item</p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] font-medium text-white/70">Total</p>
                    <p class="text-sm font-bold text-white">Rp {{ number_format($floatingCartTotal,0,',','.') }}</p>
                </div>
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/15">
                    <x-heroicon-o-arrow-right class="h-4 w-4" aria-hidden="true"/>
                </div>
            </a>
        </div>
    @endif

    {{-- HEADER --}}
    <header :class="scrolled?'bg-white/95 backdrop-blur-xl shadow-lg border-gray-200/70':'bg-white/70 backdrop-blur-md border-transparent'" class="fixed inset-x-0 top-0 z-[1000] border-b transition-all duration-300">
        <x-ui.container>
            <nav aria-label="Main navigation" class="flex h-20 items-center justify-between">
                {{-- Logo --}}
                <a href="{{ route('home') }}" aria-label="Eazywear Indonesia - Home" class="shrink-0">
                    <img src="{{ asset('images/hero/logo-navbar.webp') }}" alt="Eazywear" width="450" height="318" class="h-16 w-auto object-contain">
                </a>
                {{-- Desktop Menu --}}
                <ul class="hidden items-center gap-10 lg:flex">
                    <li><x-ui.nav-link route="home">Home</x-ui.nav-link></li>
                    <li><x-ui.nav-link route="catalog">Catalog</x-ui.nav-link></li>
                    <li><x-ui.nav-link route="about">About</x-ui.nav-link></li>
                    <li><x-ui.nav-link route="contact">Contact</x-ui.nav-link></li>
                </ul>
                {{-- Desktop Right --}}
                <div class="hidden items-center gap-4 lg:flex">
                    {{-- Search --}}
                    <form method="GET" action="{{ route('catalog') }}" class="relative" x-data="{search:@js(request('search','')),focused:false}" role="search">
                        <div x-cloak :class="focused?'w-40':'w-28'" class="{{ request('search')?'w-40':'w-28' }} flex items-center rounded-full border border-gray-300 transition-all duration-300 focus-within:border-[#AE7C18] focus-within:ring-2 focus-within:ring-[#AE7C18]/10">
                            <x-heroicon-o-magnifying-glass class="ml-4 h-5 w-5 shrink-0 text-gray-500" aria-hidden="true"/>
                            <label for="desktop-search" class="sr-only">Search products</label>
                            <input id="desktop-search" type="text" name="search" x-model="search" @focus="focused=true" @input="focused=search.length>0" placeholder="Search" autocomplete="off" class="search-navbar-input w-full min-w-0 bg-transparent px-3 py-2 text-sm outline-none placeholder:text-gray-500">
                            <button type="button" x-show="search.length>0" x-cloak @click="search='';focused=false" aria-label="Clear search" class="mr-3 shrink-0 text-xs font-semibold text-slate-400 transition hover:text-[#AE7C18]">Clear</button>
                        </div>
                    </form>
                    {{-- Cart --}}
                    @php $cartCount=collect(session('cart',[]))->sum('qty'); @endphp
                    <a id="navbar-cart" href="{{ route('cart.index') }}" aria-label="Shopping cart{{ $cartCount>0 ? ', '.$cartCount.' items' : '' }}" class="relative flex h-10 w-10 items-center justify-center rounded-full bg-[#AE7C18] text-white transition hover:bg-[#96690F]">
                        <x-heroicon-o-shopping-cart class="h-5 w-5" aria-hidden="true"/>
                        @if($cartCount>0)
                            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white shadow-sm" aria-hidden="true">{{ $cartCount>99?'99+':$cartCount }}</span>
                        @endif
                    </a>
                </div>
                {{-- Mobile Menu Button --}}
                <button @click="open=true" type="button" aria-label="Open navigation menu" aria-controls="mobile-menu" :aria-expanded="open.toString()" class="lg:hidden rounded-full p-2 text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]">
                    <x-heroicon-o-bars-3 class="h-8 w-8" aria-hidden="true"/>
                </button>
            </nav>
        </x-ui.container>
    </header>
</div>