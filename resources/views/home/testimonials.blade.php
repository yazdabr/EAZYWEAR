<section class="bg-white py-28">

    <x-ui.container>

        {{-- Heading --}}
        <x-ui.reveal>

            <div class="mb-16 text-center">

                <p
                    class="mb-4 font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">

                    TESTIMONIALS

                </p>

                <h2
                    class="mx-auto max-w-3xl text-4xl font-bold leading-tight lg:text-5xl">

                    Trusted by Over 500+ Teams

                    <br>

                    in Indonesia

                </h2>

                <p
                    class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-gray-600">

                    Hear what our customers have to say about the quality,
                    service, and experience of working with Eazywear for their
                    custom sportswear needs.

                </p>

            </div>

        </x-ui.reveal>

        {{-- Testimonials --}}
        <div class="grid gap-8 lg:grid-cols-3">

            {{-- Testimonial 1 --}}
            <x-ui.reveal
                animation="scale"
                :index="0">

                <x-website.testimonial-card
                    name="Rizky Ramadhan"
                    position="Captain, FC Jakarta Tigers"
                    image="images/testimonials/fortis.png"
                    quote="The quality of the material surpassed our expectations. Eazywear handled our custom design perfectly, and the delivery was ahead of schedule."/>

            </x-ui.reveal>

            {{-- Testimonial 2 --}}
            <x-ui.reveal
                animation="scale"
                :index="1">

                <x-website.testimonial-card
                    name="Santi Wijaya"
                    position="HR Manager, Tech Corp Indonesia"
                    image="images/testimonials/fortis.png"
                    quote="Ordering jerseys for our corporate tournament was a breeze. The team at Eazywear was very responsive to our branding requirements."/>

            </x-ui.reveal>

            {{-- Testimonial 3 --}}
            <x-ui.reveal
                animation="scale"
                :index="2">

                <x-website.testimonial-card
                    name="Bambang S."
                    position="Founder, Garuda Esports"
                    image="images/testimonials/fortis.png"
                    quote="The colors are vibrant and don't fade after washing. We've been using their jerseys for two seasons now and they still look new."/>

            </x-ui.reveal>

        </div>

    </x-ui.container>

</section>