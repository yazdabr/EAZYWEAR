<section class="py-8 sm:py-16">
    <x-ui.container>
        <x-ui.reveal animation="scale">
            <div
                class="relative overflow-hidden rounded-2xl bg-cover bg-center bg-no-repeat px-5 py-10 text-center sm:rounded-[2rem] sm:px-8 sm:py-20 lg:px-20"
                style="background-image: url('{{ asset('images/cta/bg.png') }}');"
            >
                {{-- Overlay --}}
                <div class="absolute inset-0 bg-white/60 backdrop-blur-[1px] sm:hidden"></div>

                {{-- Content --}}
                <div class="relative z-10">

                    {{-- Label --}}
                    <p class="mb-2 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mb-4 sm:text-xs sm:tracking-[0.3em]">
                        LET'S START YOUR PROJECT
                    </p>

                    {{-- Title --}}
                    <h2 class="mx-auto max-w-3xl text-xl font-bold leading-tight text-gray-900 sm:text-4xl lg:text-5xl">
                        Ready to Create Your Own Jersey?
                    </h2>

                    {{-- Description --}}
                    <p class="mx-auto mt-2 max-w-2xl text-xs leading-relaxed text-gray-700 sm:mt-6 sm:text-base sm:leading-8 lg:text-lg">
                        <span class="block sm:hidden">
                            Start your custom sportswear project today. Our team is ready to build your ideal jersey.
                        </span>

                        <span class="hidden sm:inline">
                            Start your champion's journey today with Eazywear
                            Indonesia. Our experts are ready to help you create
                            premium custom sportswear tailored to your team's
                            identity.
                        </span>
                    </p>

                    {{-- CTA --}}
                    <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:mt-10 sm:flex-row sm:gap-5">

                        {{-- WhatsApp --}}
                        <x-ui.button
                            href="https://wa.me/6285754431105"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="!px-5 !py-2.5 !text-xs shadow-lg sm:!px-10 sm:!py-4 sm:!text-base"
                        >
                            <x-heroicon-o-chat-bubble-left-right class="mr-1.5 h-4 w-4 sm:mr-2 sm:h-6 sm:w-6"/>
                            WhatsApp Us
                        </x-ui.button>

                        {{-- Internal Link --}}
                        <a
                            href="{{ route('contact') }}"
                            class="group inline-flex items-center gap-2 text-xs font-semibold text-gray-700 transition-colors duration-300 hover:text-[#AE7C18] sm:text-sm"
                        >
                            Contact Eazywear
                            <x-heroicon-o-arrow-right class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"/>
                        </a>

                    </div>

                </div>
            </div>
        </x-ui.reveal>
    </x-ui.container>
</section>