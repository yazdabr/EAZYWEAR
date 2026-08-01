<section class="bg-white py-28">

    <x-ui.container>

        {{-- Heading --}}
        <div class="mb-20 text-center" data-aos="fade-up">

            <p
                class="mb-4 font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">

                HOW IT WORKS

            </p>

            <h2
                class="text-4xl font-bold lg:text-5xl">

                Seamless Production

            </h2>

        </div>

        {{-- Timeline --}}
        <div class="relative">

            {{-- Line --}}
            <div
                class="absolute left-0 right-0 top-10 hidden h-[2px] bg-gray-300 lg:block">
            </div>

            <div
                class="grid gap-12 md:grid-cols-2 lg:grid-cols-5">

                {{-- Step 1 --}}
                <div
                    data-aos="fade-right"
                    data-aos-delay="100">

                    <x-website.process-card
                        :step="1"
                        title="Consultation"
                        description="Discuss your team's identity, fabric preferences, and order quantity."/>

                </div>

                {{-- Step 2 --}}
                <div
                    data-aos="fade-right"
                    data-aos-delay="250">

                    <x-website.process-card
                        :step="2"
                        title="Design"
                        description="Our designers create custom mockups until you are completely satisfied."/>

                </div>

                {{-- Step 3 --}}
                <div
                    data-aos="fade-right"
                    data-aos-delay="400">

                    <x-website.process-card
                        :step="3"
                        title="Production"
                        description="High-quality sublimation printing and precision sewing process begin."/>

                </div>

                {{-- Step 4 --}}
                <div
                    data-aos="fade-right"
                    data-aos-delay="550">

                    <x-website.process-card
                        :step="4"
                        title="Quality Check"
                        description="Every jersey is carefully inspected before packaging and shipment."/>

                </div>

                {{-- Step 5 --}}
                <div
                    data-aos="fade-right"
                    data-aos-delay="700">

                    <x-website.process-card
                        :step="5"
                        title="Delivery"
                        description="Products are securely packed and shipped to your location across Indonesia."/>

                </div>

            </div>

        </div>

    </x-ui.container>

</section>