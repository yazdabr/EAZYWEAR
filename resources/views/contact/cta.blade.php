<section class="bg-slate-50/50 py-12 sm:py-16 md:py-24">
    <x-ui.container>
        <x-ui.reveal animation="scale">
            {{-- Card Container Emas/Kuning Mustard dengan Motif Gradient Halus --}}
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#C89B3C] via-[#AE7C18] to-[#96690F] p-6 shadow-2xl sm:rounded-[2.5rem] sm:p-10 md:p-12 lg:p-16">
                
                {{-- Decorative Glow Overlay --}}
                <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-black/10 blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center lg:gap-8">

                    {{-- Teks Kiri --}}
                    <div class="max-w-2xl">
                        <h2 class="mt-3 text-2xl font-black tracking-tight text-white sm:text-3xl lg:text-4xl">
                            Ready to Start Your Custom Jersey Project?
                        </h2>

                        <p class="mt-2 text-xs font-medium leading-relaxed text-amber-50 sm:mt-3 sm:text-base">
                            Our consultants are ready to walk you through the technical fabric selections and design options.
                        </p>
                    </div>

                    {{-- Tombol Kanan --}}
                    <div class="w-full shrink-0 sm:w-auto">
                        <a
                            href="https://wa.me/6285754431105"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="group inline-flex w-full items-center justify-center gap-3 rounded-full bg-slate-950 px-6 py-3.5 text-xs font-semibold text-white shadow-xl transition-all duration-300 hover:bg-black hover:shadow-2xl active:scale-95 sm:w-auto sm:px-8 sm:py-4 sm:text-sm"
                        >
                            <span>Contact Us</span>

                            {{-- Icon Circle --}}
                            <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/10 text-white transition-all duration-300 group-hover:bg-[#AE7C18] group-hover:text-white sm:h-8 sm:w-8">
                                <x-heroicon-o-arrow-right class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-0.5 sm:h-4 sm:w-4"/>
                            </span>
                        </a>
                    </div>

                </div>

            </div>
        </x-ui.reveal>
    </x-ui.container>
</section>