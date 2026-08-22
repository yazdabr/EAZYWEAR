<section class="relative overflow-hidden border-y border-[#E7DFC9] bg-[#9e9686] py-8">
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -left-24 top-1/2 h-56 w-56 -translate-y-1/2 rounded-full bg-[#AE7C18]/6 blur-3xl"></div>
        <div class="absolute right-0 top-0 h-64 w-64 rounded-full bg-[#AE7C18]/5 blur-[90px]"></div>
    </div>
    <x-ui.container>
        <div class="relative z-10 flex flex-nowrap items-center gap-3 overflow-x-auto pb-2 lg:overflow-visible [scrollbar-width:none] [-ms-overflow-style:none] [&::-webkit-scrollbar]:hidden">
            <!-- Form Search -->
            <form method="GET" action="{{ route('catalog') }}" class="relative min-w-[280px] shrink-0 lg:w-[380px]">
                @if(request('category'))
                    <input type="hidden" name="category" value="{{ request('category') }}">
                @endif

                <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-4">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-slate-400"/>
                </div>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search products..."
                    autocomplete="off"
                    class="box-border h-[42px] w-full rounded-full border border-[#E4DCC8] bg-white pl-11 pr-16 text-sm font-medium text-slate-800 placeholder-slate-400 shadow-md transition-all duration-300 focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/15"
                >

                @if(request('search'))
                    <a
                        href="{{ route('catalog', request('category') ? ['category' => request('category')] : []) }}"
                        class="absolute inset-y-0 right-4 z-10 flex items-center text-xs font-semibold text-slate-400 transition hover:text-[#AE7C18]"
                    >
                        Clear
                    </a>
                @endif
            </form>

            <!-- Category Filter Included -->
            @include('catalog.category-filter')
        </div>
    </x-ui.container>
</section>