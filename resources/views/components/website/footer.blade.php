<footer class="border-t border-slate-200 bg-[#111827] text-slate-300">
    <x-ui.container>
        <div class="py-4 sm:py-12">
            {{-- Top Row --}}
            <div class="flex flex-col items-center justify-between gap-2 border-b border-slate-800 pb-3 text-center sm:flex-row sm:gap-6 sm:pb-8 sm:text-left">
                <div class="space-y-1 sm:space-y-2">
                    <img
                        src="{{ asset('images/hero/logoo.png') }}"
                        alt="Eazywear"
                        class="mx-auto h-5 w-auto brightness-0 invert sm:mx-0 sm:h-6"
                    >
                    <p class="text-[11px] text-slate-400 sm:text-xs">
                        Engineered for performance and technical excellence.
                    </p>
                </div>
            </div>

            {{-- Mobile Accordion --}}
            <div class="divide-y divide-slate-800/80 sm:hidden">
                {{-- Quick Links --}}
                <div x-data="{ open: false }" class="border-b border-slate-800/80">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full items-center justify-between py-2.5 text-left"
                    >
                        <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-[#AE7C18]">
                            Quick Links
                        </span>
                        <x-heroicon-o-chevron-down
                            class="h-4 w-4 text-slate-400 transition-transform duration-300 ease-out"
                            x-bind:class="open ? 'rotate-180' : ''"
                        />
                    </button>

                    <div
                        class="grid overflow-hidden transition-[grid-template-rows] duration-300 ease-out"
                        x-bind:class="open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                    >
                        <div class="min-h-0 overflow-hidden">
                            <ul class="space-y-1.5 pb-2.5 text-[11px] text-slate-300">
                                <li>
                                    <a href="{{ route('home') }}" class="transition hover:text-white">
                                        Home
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('catalog') }}" class="transition hover:text-white">
                                        Catalog
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('about') }}" class="transition hover:text-white">
                                        About Us
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('contact') }}" class="transition hover:text-white">
                                        Contact
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Categories --}}
                <div x-data="{ open: false }" class="border-b border-slate-800/80">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full items-center justify-between py-2.5 text-left"
                    >
                        <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-[#AE7C18]">
                            Categories
                        </span>
                        <x-heroicon-o-chevron-down
                            class="h-4 w-4 text-slate-400 transition-transform duration-300 ease-out"
                            x-bind:class="open ? 'rotate-180' : ''"
                        />
                    </button>

                    <div
                        class="grid overflow-hidden transition-[grid-template-rows] duration-300 ease-out"
                        x-bind:class="open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                    >
                        <div class="min-h-0 overflow-hidden">
                            <ul class="pb-2.5 text-[11px] text-slate-300">
                                <li>
                                    <span class="text-slate-400">
                                        Soon
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Contact & Address --}}
                <div x-data="{ open: false }" class="border-b border-slate-800/80">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full items-center justify-between py-2.5 text-left"
                    >
                        <span class="text-[11px] font-bold uppercase tracking-[0.12em] text-[#AE7C18]">
                            Contact & Address
                        </span>
                        <x-heroicon-o-chevron-down
                            class="h-4 w-4 text-slate-400 transition-transform duration-300 ease-out"
                            x-bind:class="open ? 'rotate-180' : ''"
                        />
                    </button>

                    <div
                        class="grid overflow-hidden transition-[grid-template-rows] duration-300 ease-out"
                        x-bind:class="open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                    >
                        <div class="min-h-0 overflow-hidden">
                            <div class="space-y-2 pb-2.5 text-[11px] text-slate-300">
                                <a
                                    href="https://maps.app.goo.gl/RyfBQ86cTGevVaa98"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-start gap-2 transition hover:text-white"
                                >
                                    <x-heroicon-o-map-pin class="mt-0.5 h-3.5 w-3.5 shrink-0 text-[#AE7C18]"/>
                                    <span class="leading-4">
                                        Jl. Asang Permai No.Km 11.200, Banjar, Kalsel
                                    </span>
                                </a>

                                <a
                                    href="https://wa.me/6285754431105"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center gap-2 transition hover:text-white"
                                >
                                    <x-heroicon-o-phone class="h-3.5 w-3.5 shrink-0 text-[#AE7C18]"/>
                                    <span>
                                        +62 857 5443 1105
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Desktop / Tablet --}}
            <div class="hidden sm:grid sm:grid-cols-3 sm:gap-8 sm:pt-8">
                {{-- Navigation --}}
                <div>
                    <h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-[#AE7C18]">
                        Navigation
                    </h3>
                    <ul class="space-y-2 text-xs font-medium text-slate-300">
                        <li>
                            <a href="{{ route('home') }}" class="transition hover:text-white">
                                Home
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('catalog') }}" class="transition hover:text-white">
                                Catalog
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('about') }}" class="transition hover:text-white">
                                About
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('contact') }}" class="transition hover:text-white">
                                Contact
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- Products --}}
                <div>
                    <h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-[#AE7C18]">
                        Categories
                    </h3>
                    <ul class="space-y-2 text-xs font-medium text-slate-300">
                        <li>
                            <span class="text-slate-400">
                                Soon
                            </span>
                        </li>
                    </ul>
                </div>

                {{-- Location --}}
                <div>
                    <h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-[#AE7C18]">
                        Headquarters
                    </h3>
                    <div class="space-y-2 text-xs text-slate-300">
                        <p class="leading-relaxed">
                            Jl. Asang Permai No.Km 11.200, Mekar Raya, Kertak Hanyar, Banjar, Kalsel
                        </p>
                        <p class="font-semibold text-white">
                            +62 857 5443 1105
                        </p>
                    </div>
                </div>
            </div>

            {{-- Bottom Legal Bar --}}
            <div class="mt-0 flex flex-col items-center justify-between gap-0 border-t border-slate-800/80 pt-1 text-center text-[10px] text-slate-500 sm:mt-8 sm:flex-row sm:gap-2 sm:pt-6 sm:text-left sm:text-[11px]">
                <p>
                    © {{ date('Y') }} Eazywear Indonesia. All rights reserved.
                </p>
                <p class="text-slate-600">
                    Built for performance.
                </p>
            </div>
        </div>
    </x-ui.container>
</footer>