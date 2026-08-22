<section class="bg-gray-50 py-12 sm:py-20 lg:py-24">

    <x-ui.container>

        <div class="grid gap-5 sm:gap-8 lg:grid-cols-2">

            {{-- Vision (Kartu Utama Gold) --}}
            <x-ui.reveal
                animation="up"
                :index="0">

                <div
                    class="group h-full rounded-2xl bg-[#AE7C18] p-6 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl sm:rounded-3xl sm:p-10 sm:hover:-translate-y-2 sm:shadow-xl">

                    {{-- Icon --}}
                    <div
                        class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-white/15 sm:mb-8 sm:h-16 sm:w-16 sm:rounded-2xl">

                        <x-heroicon-o-eye
                            class="h-5 w-5 text-white sm:h-8 sm:w-8"/>

                    </div>

                    {{-- Title --}}
                    <h2
                        class="text-xl font-bold sm:text-3xl">

                        Our Vision

                    </h2>

                    {{-- Description --}}
                    <p
                        class="mt-2 text-xs leading-relaxed text-white/95 sm:mt-6 sm:text-base sm:leading-8">

                        To become Indonesia's leading innovator in custom
                        athletic apparel, recognized globally for combining
                        premium craftsmanship, technical excellence, and
                        modern design into every product.

                    </p>

                </div>

            </x-ui.reveal>

            {{-- Mission (Kartu Kontras Gelap agar tidak monoton) --}}
            <x-ui.reveal
                animation="up"
                :index="1">

                <div
                    class="group h-full rounded-2xl bg-gray-900 p-6 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl sm:rounded-3xl sm:p-10 sm:hover:-translate-y-2 sm:shadow-xl">

                    {{-- Icon --}}
                    <div
                        class="mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-[#AE7C18]/20 sm:mb-8 sm:h-16 sm:w-16 sm:rounded-2xl">

                        <x-heroicon-o-rocket-launch
                            class="h-5 w-5 text-[#AE7C18] sm:h-8 sm:w-8"/>

                    </div>

                    {{-- Title --}}
                    <h2
                        class="text-xl font-bold sm:text-3xl">

                        Our Mission

                    </h2>

                    {{-- Description --}}
                    <p
                        class="mt-2 text-xs leading-relaxed text-gray-300 sm:mt-6 sm:text-base sm:leading-8">

                        To empower teams, schools, communities, and companies
                        with premium-quality custom sportswear through
                        innovative production technology, outstanding customer
                        service, and continuous product development.

                    </p>

                </div>

            </x-ui.reveal>

        </div>

    </x-ui.container>

</section>