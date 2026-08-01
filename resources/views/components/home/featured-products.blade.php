<section class="bg-white py-28">

    <x-ui.container>

        <div
            class="mb-16 text-center"
            data-aos="fade-up">

            <p
                class="mb-4 font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">

                FEATURED PRODUCTS

            </p>

            <h2
                class="text-4xl font-bold lg:text-5xl">

                Our Best Collections

            </h2>

            <p
                class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-gray-600">

                Discover our most popular custom sportswear, crafted with premium
                materials, modern printing technology, and exceptional attention
                to detail.

            </p>

        </div>

        <div class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">

            {{-- Product 1 --}}
            <div
                data-aos="fade-up"
                data-aos-delay="100">

                <x-website.product-card
                    title="Football Jersey"
                    category="Jersey"
                    image="images/products/adsy.png"
                    price="149.000"
                    href="#"/>

            </div>

            {{-- Product 2 --}}
            <div
                data-aos="fade-up"
                data-aos-delay="200">

                <x-website.product-card
                    title="Training Hoodie"
                    category="Jacket"
                    image="images/products/fortis.png"
                    price="189.000"
                    href="#"/>

            </div>

            {{-- Product 3 --}}
            <div
                data-aos="fade-up"
                data-aos-delay="300">

                <x-website.product-card
                    title="Basketball Jersey"
                    category="Jersey"
                    image="images/products/sujud.png"
                    price="159.000"
                    href="#"/>

            </div>

        </div>

    </x-ui.container>

</section>