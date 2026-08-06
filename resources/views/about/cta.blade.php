<section
    class="relative overflow-hidden bg-cover bg-center bg-no-repeat py-28"
    style="background-image: url('{{ asset('images/cta/bg.png') }}');">

    {{-- Overlay
    <div
        class="absolute inset-0 bg-black/60">
    </div> --}}

    <div
        class="relative z-10">

        <x-ui.container>

            <x-ui.reveal animation="scale">

                <div
                    class="mx-auto max-w-4xl text-center text-black">

                    {{-- Label --}}
                    <p
                        class="mb-5 font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">

                        READY TO START?

                    </p>

                    {{-- Heading --}}
                    <h2
                        class="text-4xl font-bold leading-tight lg:text-6xl">

                        Let's Create Your

                        <span class="italic text-[#AE7C18]">

                            Dream Jersey

                        </span>

                    </h2>

                    {{-- Description --}}
                    <p
                        class="mx-auto mt-8 max-w-2xl text-lg leading-8 text-black">

                        Whether you're building a professional team,
                        representing your community, or creating apparel
                        for your company, Eazywear is ready to help turn
                        your ideas into premium-quality custom sportswear.

                    </p>

                    {{-- Buttons --}}
                    <div
                        class="mt-12 flex flex-col justify-center gap-5 sm:flex-row">

                        <x-ui.button
                            :href="route('catalog')">

                            Browse Catalog

                        </x-ui.button>

                        <x-ui.button
                            href="https://wa.me/6285754431105"
                            target="_blank"
                            rel="noopener noreferrer"
                            variant="outline">

                            Contact Us  

                        </x-ui.button>

                    </div>

                </div>

            </x-ui.reveal>

        </x-ui.container>

    </div>

</section>