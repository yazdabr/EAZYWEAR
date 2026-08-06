<section class="bg-gray-50 py-28">

    <x-ui.container>

        {{-- Heading --}}
        <x-ui.reveal>

            <div class="mb-16 text-center">

                <div>

                    <p
                        class="mb-3 font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">

                        PRODUCT CATEGORIES

                    </p>

                    <h2
                        class="text-4xl font-bold lg:text-5xl">

                        Find Your Style

                    </h2>

                </div>

            </div>

        </x-ui.reveal>

        {{-- Category Grid --}}
        <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-4">

            {{-- Jersey --}}
            <x-ui.reveal delay="100">

                <x-website.category-card
                    title="Jersey"
                    image="images/categories/adsy.png"
                    href="#" />

            </x-ui.reveal>

            {{-- Jacket --}}
            <x-ui.reveal delay="200">

                <x-website.category-card
                    title="Jacket"
                    image="images/categories/fortis.png"
                    href="#" />

            </x-ui.reveal>

            {{-- T-Shirt --}}
            <x-ui.reveal delay="300">

                <x-website.category-card
                    title="T-Shirt"
                    image="images/categories/sujud.png"
                    href="#" />

            </x-ui.reveal>

            {{-- Pants --}}
            <x-ui.reveal delay="400">

                <x-website.category-card
                    title="Pants"
                    image="images/categories/fortis.png"
                    href="#" />

            </x-ui.reveal>

        </div>

    </x-ui.container>

</section>