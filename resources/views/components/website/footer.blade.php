<footer class="border-t border-slate-200 bg-[#111827] text-slate-300">
    <x-ui.container>
        <div class="py-8 sm:py-12">
            
            {{-- Top Row: Brand & Quick Action --}}
            <div class="flex flex-col items-center justify-between gap-6 border-b border-slate-800 pb-8 sm:flex-row text-center sm:text-left">
                <div class="space-y-2">
                    <img
                        src="{{ asset('images/hero/logoo.png') }}"
                        alt="Eazywear"
                        class="mx-auto h-6 w-auto brightness-0 invert sm:mx-0"
                    >
                    <p class="text-xs text-slate-400">
                        Engineered for performance and technical excellence.
                    </p>
                </div>
            </div>

            {{-- Mobile View: Accordion / Collapse (Hanya Tampil di Mobile < sm) --}}
            <div class="block divide-y divide-slate-800/80 sm:hidden">
                
                {{-- Quick Links Accordion --}}
                <details class="group py-3">
                    <summary class="flex cursor-pointer items-center justify-between text-xs font-bold uppercase tracking-wider text-[#AE7C18]">
                        <span>Quick Links</span>
                        <x-heroicon-o-chevron-down class="h-4 w-4 transition-transform duration-200 group-open:rotate-180 text-slate-400" />
                    </summary>
                    <ul class="mt-3 space-y-2.5 pl-1 text-xs text-slate-300">
                        <li><a href="{{ route('home') }}" class="hover:text-white">Home</a></li>
                        <li><a href="{{ route('catalog') }}" class="hover:text-white">Catalog</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-white">About Us</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white">Contact</a></li>
                    </ul>
                </details>

                {{-- Categories Accordion --}}
                <details class="group py-3">
                    <summary class="flex cursor-pointer items-center justify-between text-xs font-bold uppercase tracking-wider text-[#AE7C18]">
                        <span>Categories</span>
                        <x-heroicon-o-chevron-down class="h-4 w-4 transition-transform duration-200 group-open:rotate-180 text-slate-400" />
                    </summary>
                    @php
                        $footerCategories = \App\Models\Category::whereIn('name', [
                            'Kaos Jersey',
                            'Kaos Polo',
                        ])->orderBy('name')->get();
                    @endphp
                    <ul class="mt-3 space-y-2.5 pl-1 text-xs text-slate-300">
                        @foreach($footerCategories as $category)
                            <li>
                                <a href="{{ route('catalog', ['category' => $category->id]) }}" class="hover:text-white">
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </details>

                {{-- Location & Contact Accordion --}}
                <details class="group py-3">
                    <summary class="flex cursor-pointer items-center justify-between text-xs font-bold uppercase tracking-wider text-[#AE7C18]">
                        <span>Contact & Address</span>
                        <x-heroicon-o-chevron-down class="h-4 w-4 transition-transform duration-200 group-open:rotate-180 text-slate-400" />
                    </summary>
                    <div class="mt-3 space-y-2.5 pl-1 text-xs text-slate-300">
                        <a href="https://maps.app.goo.gl/RyfBQ86cTGevVaa98" target="_blank" class="flex items-start gap-2 hover:text-white">
                            <x-heroicon-o-map-pin class="h-4 w-4 shrink-0 text-[#AE7C18] mt-0.5" />
                            <span>Jl. Asang Permai No.Km 11.200, Banjar, Kalsel</span>
                        </a>
                        <a href="https://wa.me/6285754431105" target="_blank" class="flex items-center gap-2 hover:text-white">
                            <x-heroicon-o-phone class="h-4 w-4 shrink-0 text-[#AE7C18]" />
                            <span>+62 857 5443 1105</span>
                        </a>
                    </div>
                </details>

            </div>

            {{-- Desktop / Tablet View: Standard Grid (Sembunyi di Mobile < sm) --}}
            <div class="hidden sm:grid sm:grid-cols-3 sm:gap-8 sm:pt-8">
                {{-- Navigation --}}
                <div>
                    <h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-[#AE7C18]">
                        Navigation
                    </h3>
                    <ul class="space-y-2 text-xs font-medium text-slate-300">
                        <li><a href="{{ route('home') }}" class="hover:text-white transition">Home</a></li>
                        <li><a href="{{ route('catalog') }}" class="hover:text-white transition">Catalog</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-white transition">About</a></li>
                        <li><a href="{{ route('contact') }}" class="hover:text-white transition">Contact</a></li>
                    </ul>
                </div>

                {{-- Products --}}
                <div>
                    <h3 class="mb-3 text-xs font-bold uppercase tracking-widest text-[#AE7C18]">
                        Categories
                    </h3>
                    <ul class="space-y-2 text-xs font-medium text-slate-300">
                        @foreach($footerCategories as $category)
                            <li>
                                <a href="{{ route('catalog', ['category' => $category->id]) }}" class="hover:text-white transition">
                                    {{ $category->name }}
                                </a>
                            </li>
                        @endforeach
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
                        <p class="font-semibold text-white">+62 857 5443 1105</p>
                    </div>
                </div>
            </div>

            {{-- Bottom Legal Bar --}}
            <div class="mt-8 border-t border-slate-800/80 pt-6 text-center sm:text-left text-[11px] text-slate-500 flex flex-col sm:flex-row justify-between items-center gap-2">
                <p>© {{ date('Y') }} Eazywear Indonesia. All rights reserved.</p>
                <p class="text-slate-600">Built for performance.</p>
            </div>

        </div>
    </x-ui.container>
</footer>