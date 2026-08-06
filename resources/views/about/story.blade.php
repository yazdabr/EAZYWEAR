<section class="bg-white py-24">

    <x-ui.container>

        <div class="grid items-center gap-20 lg:grid-cols-2">

            {{-- LEFT --}}
            <x-ui.reveal animation="right">

                <div>

                    {{-- Label --}}
                    <div class="mb-6 flex items-center gap-3">

                        <div class="h-px w-12 bg-[#AE7C18]"></div>

                        <span
                            class="text-xs font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">

                            OUR LEGACY

                        </span>

                    </div>

                    {{-- Heading --}}
                    <h2
                        class="text-4xl font-bold leading-tight lg:text-5xl">

                        Driven by

                        <span class="italic text-[#AE7C18]">

                            Performance

                        </span>

                    </h2>

                    {{-- Paragraph --}}
                    <p
                        class="mt-8 leading-8 text-gray-600">

                        Founded in the heart of South Jakarta, Eazywear Indonesia
                        began with a simple observation: Indonesian athletes
                        deserved gear that matched their ambition.

                        We bridged the gap between local craftsmanship and
                        international-quality sportswear through premium materials,
                        precision manufacturing, and unlimited customization.

                    </p>

                    <p
                        class="mt-6 leading-8 text-gray-600">

                        Today, we proudly serve schools, universities,
                        communities, companies, and professional sports teams
                        across Indonesia.

                        Every jersey is produced with meticulous attention to
                        detail, ensuring every customer receives apparel that is
                        comfortable, durable, and built to perform.

                    </p>

                </div>

            </x-ui.reveal>

            {{-- RIGHT --}}
            <x-ui.reveal
                animation="left"
                delay="150">

                <div class="relative">

                    {{-- Image --}}
                    <img
                        src="{{ asset('images/about/df.png') }}"
                        alt="Eazywear Story"
                        width="700"
                        height="900"
                        loading="eager"
                        decoding="sync"
                        class="w-full rounded-3xl shadow-2xl">

                    {{-- Floating Badge --}}
                    <div
                        class="absolute -bottom-6 -left-2 rounded-2xl bg-[#AE7C18] px-6 py-5 text-white shadow-xl">

                        <h3
                            class="text-4xl font-bold">

                            8+

                        </h3>

                        <p
                            class="mt-1 text-xs uppercase tracking-[0.25em]">

                            Years of Craft

                        </p>

                    </div>

                </div>

            </x-ui.reveal>

        </div>

    </x-ui.container>

</section>