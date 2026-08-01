<section class="bg-gray-50 py-28">

    <x-ui.container>

        <div
            class="mb-16 text-center"
            data-aos="fade-up">

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

            {{-- <x-ui.button
                variant="outline">

                View All

            </x-ui.button> --}}

        </div>

        <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-4">

            {{-- Jersey --}}
            <div
                data-aos="fade-up"
                data-aos-delay="100">

                <x-website.category-card
                    title="Jersey"
                    image="images/categories/adsy.png"
                    href="#" />

            </div>

            {{-- Jacket --}}
            <div
                data-aos="fade-up"
                data-aos-delay="200">

                <x-website.category-card
                    title="Jacket"
                    image="images/categories/fortis.png"
                    href="#" />

            </div>

            {{-- T-Shirt --}}
            <div
                data-aos="fade-up"
                data-aos-delay="300">

                <x-website.category-card
                    title="T-Shirt"
                    image="images/categories/sujud.png"
                    href="#" />

            </div>

            {{-- Pants --}}
            <div
                data-aos="fade-up"
                data-aos-delay="400">

                <x-website.category-card
                    title="Pants"
                    image="images/categories/fortis.png"
                    href="#" />

            </div>

        </div>

    </x-ui.container>

</section>