<section class="bg-white py-16">
    <x-ui.container>
        <div class="grid gap-16 lg:grid-cols-2">
            {{-- LEFT --}}
            <div>
                <div class="mb-10" data-aos="fade-up">
                    <p class="mb-3 font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">
                        PRODUCT FEATURES
                    </p>
                    <h2 class="text-4xl font-bold lg:text-5xl">
                        Key Features
                    </h2>
                </div>

                <div class="space-y-6">
                    {{-- Card 1 --}}
                    <div
                        data-aos="fade-up"
                        data-aos-delay="100"
                        class="group flex gap-5 rounded-3xl bg-[#AE7C18] p-7 text-white shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl"
                    >
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15">
                            <x-heroicon-o-sparkles class="h-8 w-8"/>
                        </div>

                        <div>
                            <h3 class="text-xl font-bold">
                                Premium Materials
                            </h3>
                            <p class="mt-3 leading-7 text-white/90">
                                Carefully selected materials designed to provide
                                comfort, durability, and a premium feel for
                                everyday wear and various activities.
                            </p>
                        </div>
                    </div>

                    {{-- Card 2 --}}
                    <div
                        data-aos="fade-up"
                        data-aos-delay="200"
                        class="group flex gap-5 rounded-3xl bg-[#AE7C18] p-7 text-white shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl"
                    >
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15">
                            <x-heroicon-o-shield-check class="h-8 w-8"/>
                        </div>

                        <div>
                            <h3 class="text-xl font-bold">
                                Quality Construction
                            </h3>
                            <p class="mt-3 leading-7 text-white/90">
                                Carefully constructed with attention to detail
                                to provide a comfortable fit, reliable durability,
                                and long-lasting product quality.
                            </p>
                        </div>
                    </div>

                    {{-- Card 3 --}}
                    <div
                        data-aos="fade-up"
                        data-aos-delay="300"
                        class="group flex gap-5 rounded-3xl bg-[#AE7C18] p-7 text-white shadow-lg transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl"
                    >
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15">
                            <x-heroicon-o-paint-brush class="h-8 w-8"/>
                        </div>

                        <div>
                            <h3 class="text-xl font-bold">
                                Custom Design
                            </h3>
                            <p class="mt-3 leading-7 text-white/90">
                                Flexible customization options allow you to create
                                unique apparel with personalized colors, graphics,
                                logos, and other design elements.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT --}}
            <div>
                <div class="mb-10" data-aos="fade-up">
                    <p class="mb-3 font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">
                        SPECIFICATIONS
                    </p>
                    <h2 class="text-4xl font-bold lg:text-5xl">
                        Product Specifications
                    </h2>
                </div>

                <div
                    data-aos="fade-up"
                    class="overflow-hidden rounded-3xl shadow-xl"
                >
                    <table class="w-full border-collapse">
                        <tbody>
                            <tr>
                                <td class="w-[35%] bg-[#8F6514] px-6 py-5 font-semibold uppercase tracking-wide text-white">
                                    Material
                                </td>
                                <td class="bg-[#AE7C18] px-6 py-5 text-white">
                                    Premium Quality Fabric
                                </td>
                            </tr>

                            <tr>
                                <td class="bg-[#8F6514] px-6 py-5 font-semibold uppercase text-white">
                                    Quality
                                </td>
                                <td class="bg-[#AE7C18] px-6 py-5 text-white">
                                    Premium Standard
                                </td>
                            </tr>

                            <tr>
                                <td class="bg-[#8F6514] px-6 py-5 font-semibold uppercase text-white">
                                    Fit
                                </td>
                                <td class="bg-[#AE7C18] px-6 py-5 text-white">
                                    Comfortable Fit
                                </td>
                            </tr>

                            <tr>
                                <td class="bg-[#8F6514] px-6 py-5 font-semibold uppercase text-white">
                                    Design
                                </td>
                                <td class="bg-[#AE7C18] px-6 py-5 text-white">
                                    Customizable
                                </td>
                            </tr>

                            <tr>
                                <td class="bg-[#8F6514] px-6 py-5 font-semibold uppercase text-white">
                                    Production
                                </td>
                                <td class="bg-[#AE7C18] px-6 py-5 text-white">
                                    High-Quality Manufacturing
                                </td>
                            </tr>

                            <tr>
                                <td class="bg-[#8F6514] px-6 py-5 font-semibold uppercase text-white">
                                    Origin
                                </td>
                                <td class="bg-[#AE7C18] px-6 py-5 text-white">
                                    Made in Indonesia
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-ui.container>
</section>