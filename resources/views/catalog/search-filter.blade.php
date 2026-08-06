<section
    class="relative overflow-hidden border-y border-[#E7DFC9] bg-[#9e9686] py-8">

    {{-- Background Glow --}}
    <div
        class="absolute inset-0 overflow-hidden">

        <div
            class="absolute -left-24 top-1/2 h-56 w-56 -translate-y-1/2 rounded-full bg-[#AE7C18]/6 blur-3xl">
        </div>

        <div
            class="absolute right-0 top-0 h-64 w-64 rounded-full bg-[#AE7C18]/5 blur-[90px]">
        </div>

    </div>

    <x-ui.container>

        <div
            class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            {{-- Search Bar --}}
            <div class="relative w-full lg:max-w-xs xl:max-w-sm shrink-0">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5 text-slate-400" />
                </div>

                <input
                    type="text"
                    placeholder="Search jersey design..."
                    class="w-full rounded-full border border-[#E4DCC8] bg-white py-3 pl-11 pr-10 text-sm font-medium text-slate-800 placeholder-slate-400 shadow-md transition-all duration-300 focus:border-[#AE7C18] focus:ring-4 focus:ring-[#AE7C18]/15"
                >
            </div>

            {{-- Component Category Filter --}}
            <div class="flex-1 min-w-0">
                @include('catalog.category-filter')
            </div>

        </div>
    </x-ui.container>
</section>