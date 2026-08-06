<section class="py-16">

    <x-ui.container>

        <x-ui.reveal animation="scale">

            <div
                class="relative overflow-hidden rounded-[2rem] bg-cover bg-center bg-no-repeat px-8 py-24 text-center lg:px-20"
                style="background-image: url('{{ asset('images/cta/bg.png') }}');">

                {{-- Overlay
                <div
                    class="absolute inset-0 bg-white/75 backdrop-blur-[2px]">
                </div> --}}

                {{-- Content --}}
                <div class="relative z-10">

                    {{-- Label --}}
                    <p
                        class="mb-4 font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">

                        LET'S START YOUR PROJECT

                    </p>

                    {{-- Title --}}
                    <h2
                        class="mx-auto max-w-3xl text-4xl font-bold leading-tight text-gray-900 lg:text-5xl">

                        Ready to Create Your Own Jersey?

                    </h2>

                    {{-- Description --}}
                    <p
                        class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-gray-600">

                        Start your champion's journey today with Eazywear
                        Indonesia. Our experts are ready to help you create
                        premium custom sportswear tailored to your team's
                        identity.

                    </p>

                    {{-- Button --}}
                    <div class="mt-12">

                        <x-ui.button
                            href="https://wa.me/6285754431105"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="px-10 py-4 text-base shadow-xl hover:-translate-y-1">

                            <x-heroicon-o-chat-bubble-left-right
                                class="mr-2 h-6 w-6"/>

                            WhatsApp Us

                        </x-ui.button>

                    </div>

                </div>

            </div>

        </x-ui.reveal>

    </x-ui.container>

</section>