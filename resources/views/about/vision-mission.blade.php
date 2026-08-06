<section class="bg-gray-50 py-24">

    <x-ui.container>

        <div class="grid gap-8 lg:grid-cols-2">

            {{-- Vision --}}
            <x-ui.reveal
                animation="up"
                :index="0">

                <div
                    class="group rounded-3xl bg-[#AE7C18] p-10 text-white shadow-xl transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">

                    {{-- Icon --}}
                    <div
                        class="mb-8 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10">

                        <x-heroicon-o-eye
                            class="h-8 w-8"/>

                    </div>

                    {{-- Title --}}
                    <h2
                        class="text-3xl font-bold">

                        Our Vision

                    </h2>

                    {{-- Description --}}
                    <p
                        class="mt-6 leading-8 text-white/90">

                        To become Indonesia's leading innovator in custom
                        athletic apparel, recognized globally for combining
                        premium craftsmanship, technical excellence, and
                        modern design into every product.

                    </p>

                </div>

            </x-ui.reveal>

            {{-- Mission --}}
            <x-ui.reveal
                animation="up"
                :index="1">

                <div
                    class="group rounded-3xl bg-[#AE7C18] p-10 text-white shadow-xl transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">

                    {{-- Icon --}}
                    <div
                        class="mb-8 flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10">

                        <x-heroicon-o-rocket-launch
                            class="h-8 w-8"/>

                    </div>

                    {{-- Title --}}
                    <h2
                        class="text-3xl font-bold">

                        Our Mission

                    </h2>

                    {{-- Description --}}
                    <p
                        class="mt-6 leading-8 text-white/90">

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