<section class="bg-slate-50/50 pb-16 md:pb-24">
    <x-ui.container>
        <x-ui.reveal>
            {{-- Wrapper Utama: max-w-7xl agar membentang lebar hampir selebar layar PC --}}
            <div class="group relative mx-auto max-w-7xl overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-2 sm:p-3 shadow-md transition-all duration-300 hover:shadow-xl">

                {{-- Header Ringkas (Mobile & Tablet View) --}}
                <div class="flex items-center justify-between px-3 py-2 sm:px-4 sm:py-3">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#AE7C18] opacity-75"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-[#AE7C18]"></span>
                        </span>
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-700">
                            Our Location
                        </span>
                    </div>
                    <span class="text-xs font-medium text-slate-500">
                        Jakarta Selatan, Indonesia
                    </span>
                </div>

                {{-- Container Peta: Tinggi adaptif 70% tinggi layar di PC (70vh), responsif di HP --}}
                <div class="group relative h-[380px] w-full overflow-hidden rounded-2xl bg-slate-100 sm:h-[480px] lg:h-[70vh] lg:min-h-[450px]">
                    
                    {{-- Iframe Google Maps (Atribut width/height diubah jadi 100% dan ditambahkan class h-full w-full) --}}
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.9101642697265!2d114.66493917583898!3d-3.372133996602541!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2de427a9dade31c5%3A0x149240bd7aa8a0c!2sEazywear!5e0!3m2!1sen!2sid!4v1785898053526!5m2!1sen!2sid" 
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="strict-origin-when-cross-origin"
                        class="h-full w-full grayscale-[15%] contrast-[1.02] transition-all duration-500 group-hover:grayscale-0"
                    ></iframe>

                    {{-- Floating Direct Link Button (Petunjuk Arah) --}}
                    <div class="absolute bottom-4 right-4 z-10 sm:bottom-6 sm:right-6">
                        <a
                            href="https://maps.app.goo.gl/2x4ybZ1GFubDYDU37"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-900/90 px-4 py-2.5 text-xs font-semibold text-white shadow-lg backdrop-blur-md transition-all duration-300 hover:bg-[#AE7C18] hover:text-white"
                        >
                            <span>Buka di Google Maps</span>
                            <x-heroicon-o-arrow-up-right class="h-4 w-4" />
                        </a>
                    </div>

                </div>

            </div>
        </x-ui.reveal>
    </x-ui.container>
</section>