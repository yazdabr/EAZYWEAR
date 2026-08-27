<section class="bg-white py-6 sm:py-10 lg:py-14">
    <x-ui.container>
        <div class="grid gap-3 sm:gap-5 lg:grid-cols-2 lg:gap-8">
            {{-- LEFT: FEATURES --}}
            <div data-aos="fade-up" x-data="{ open: false }">
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all duration-300" x-bind:class="open ? 'border-[#AE7C18]/30 shadow-md' : ''">
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full items-center gap-3 px-4 py-3 text-left sm:px-5 sm:py-4"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="mb-0.5 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mb-1 sm:text-xs sm:tracking-[0.25em]">
                                PRODUCT FEATURES
                            </p>
                            <h2 class="text-lg font-bold tracking-tight text-slate-900 sm:text-2xl lg:text-3xl">
                                Key Features
                            </h2>
                        </div>

                        <x-heroicon-o-chevron-down
                            class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-300 sm:h-5 sm:w-5"
                            x-bind:class="open ? 'rotate-180' : ''"
                        />
                    </button>

                    <div
                        class="grid transition-[grid-template-rows] duration-500 ease-in-out"
                        x-bind:class="open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                    >
                        <div class="min-h-0 overflow-hidden border-t border-gray-100">
                            <div class="divide-y divide-gray-100">
                                {{-- Premium Materials --}}
                                <div class="flex gap-3 px-4 py-3 sm:gap-4 sm:px-5 sm:py-4">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-[#AE7C18] sm:h-10 sm:w-10">
                                        <x-heroicon-o-sparkles class="h-4 w-4 sm:h-5 sm:w-5"/>
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="text-sm font-bold text-slate-900 sm:text-base">
                                            Premium Materials
                                        </h3>

                                        <p class="mt-0.5 text-[10px] text-gray-400 sm:text-xs">
                                            Comfort, durability, and premium feel
                                        </p>

                                        <p class="mt-1.5 text-xs leading-5 text-gray-600 sm:text-sm sm:leading-6">
                                            Carefully selected materials designed to provide comfort, durability, and a premium feel for everyday wear and various activities.
                                        </p>
                                    </div>
                                </div>

                                {{-- Quality Construction --}}
                                <div class="flex gap-3 px-4 py-3 sm:gap-4 sm:px-5 sm:py-4">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-[#AE7C18] sm:h-10 sm:w-10">
                                        <x-heroicon-o-shield-check class="h-4 w-4 sm:h-5 sm:w-5"/>
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="text-sm font-bold text-slate-900 sm:text-base">
                                            Quality Construction
                                        </h3>

                                        <p class="mt-0.5 text-[10px] text-gray-400 sm:text-xs">
                                            Built for comfort and durability
                                        </p>

                                        <p class="mt-1.5 text-xs leading-5 text-gray-600 sm:text-sm sm:leading-6">
                                            Carefully constructed with attention to detail to provide a comfortable fit, reliable durability, and long-lasting product quality.
                                        </p>
                                    </div>
                                </div>

                                {{-- Custom Design --}}
                                <div class="flex gap-3 px-4 py-3 sm:gap-4 sm:px-5 sm:py-4">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#AE7C18]/10 text-[#AE7C18] sm:h-10 sm:w-10">
                                        <x-heroicon-o-paint-brush class="h-4 w-4 sm:h-5 sm:w-5"/>
                                    </div>

                                    <div class="min-w-0">
                                        <h3 class="text-sm font-bold text-slate-900 sm:text-base">
                                            Custom Design
                                        </h3>

                                        <p class="mt-0.5 text-[10px] text-gray-400 sm:text-xs">
                                            Personalize your apparel
                                        </p>

                                        <p class="mt-1.5 text-xs leading-5 text-gray-600 sm:text-sm sm:leading-6">
                                            Flexible customization options allow you to create unique apparel with personalized colors, graphics, logos, and other design elements.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: SPECIFICATIONS --}}
            <div data-aos="fade-up" data-aos-delay="100" x-data="{ open: false }">
                <div
                    class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all duration-300"
                    x-bind:class="open ? 'border-[#AE7C18]/30 shadow-md' : ''"
                >
                    <button
                        type="button"
                        @click="open = !open"
                        class="flex w-full items-center gap-3 px-4 py-3 text-left sm:px-5 sm:py-4"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="mb-0.5 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mb-1 sm:text-xs sm:tracking-[0.25em]">
                                SPECIFICATIONS
                            </p>

                            <h2 class="text-lg font-bold tracking-tight text-slate-900 sm:text-2xl lg:text-3xl">
                                Product Specifications
                            </h2>
                        </div>

                        <x-heroicon-o-chevron-down
                            class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-300 sm:h-5 sm:w-5"
                            x-bind:class="open ? 'rotate-180' : ''"
                        />
                    </button>

                    <div
                        class="grid transition-[grid-template-rows] duration-500 ease-in-out"
                        x-bind:class="open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                    >
                        <div class="min-h-0 overflow-hidden border-t border-gray-100">
                            <table class="w-full border-collapse">
                                <tbody>
                                    <tr class="border-b border-white/10">
                                        <td class="w-[38%] bg-[#8F6514] px-3 py-2.5 text-[9px] font-semibold uppercase tracking-wide text-white sm:w-[35%] sm:px-5 sm:py-4 sm:text-xs">
                                            Material
                                        </td>
                                        <td class="bg-[#AE7C18] px-3 py-2.5 text-[10px] text-white sm:px-5 sm:py-4 sm:text-sm">
                                            Premium Quality Fabric
                                        </td>
                                    </tr>

                                    <tr class="border-b border-white/10">
                                        <td class="bg-[#8F6514] px-3 py-2.5 text-[9px] font-semibold uppercase tracking-wide text-white sm:px-5 sm:py-4 sm:text-xs">
                                            Quality
                                        </td>
                                        <td class="bg-[#AE7C18] px-3 py-2.5 text-[10px] text-white sm:px-5 sm:py-4 sm:text-sm">
                                            Premium Standard
                                        </td>
                                    </tr>

                                    <tr class="border-b border-white/10">
                                        <td class="bg-[#8F6514] px-3 py-2.5 text-[9px] font-semibold uppercase tracking-wide text-white sm:px-5 sm:py-4 sm:text-xs">
                                            Fit
                                        </td>
                                        <td class="bg-[#AE7C18] px-3 py-2.5 text-[10px] text-white sm:px-5 sm:py-4 sm:text-sm">
                                            Comfortable Fit
                                        </td>
                                    </tr>

                                    <tr class="border-b border-white/10">
                                        <td class="bg-[#8F6514] px-3 py-2.5 text-[9px] font-semibold uppercase tracking-wide text-white sm:px-5 sm:py-4 sm:text-xs">
                                            Design
                                        </td>
                                        <td class="bg-[#AE7C18] px-3 py-2.5 text-[10px] text-white sm:px-5 sm:py-4 sm:text-sm">
                                            Customizable
                                        </td>
                                    </tr>

                                    <tr class="border-b border-white/10">
                                        <td class="bg-[#8F6514] px-3 py-2.5 text-[9px] font-semibold uppercase tracking-wide text-white sm:px-5 sm:py-4 sm:text-xs">
                                            Production
                                        </td>
                                        <td class="bg-[#AE7C18] px-3 py-2.5 text-[10px] text-white sm:px-5 sm:py-4 sm:text-sm">
                                            High-Quality Manufacturing
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="bg-[#8F6514] px-3 py-2.5 text-[9px] font-semibold uppercase tracking-wide text-white sm:px-5 sm:py-4 sm:text-xs">
                                            Origin
                                        </td>
                                        <td class="bg-[#AE7C18] px-3 py-2.5 text-[10px] text-white sm:px-5 sm:py-4 sm:text-sm">
                                            Made in Indonesia
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.container>
</section>