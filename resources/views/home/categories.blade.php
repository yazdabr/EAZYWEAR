<section class="bg-gray-50 py-8 sm:py-12 lg:py-16">
    <x-ui.container>

        {{-- Heading --}}
        <x-ui.reveal>
            <div class="mb-6 text-center sm:mb-10 lg:mb-12">
                <div>
                    <p class="mb-2 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mb-3 sm:text-xs lg:tracking-[0.3em]">
                        PRODUCT CATEGORIES
                    </p>

                    <h2 class="text-2xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                        Find Your Style
                    </h2>
                </div>
            </div>
        </x-ui.reveal>

        {{-- Category Grid --}}
        <div class="mx-auto grid max-w-3xl gap-4 sm:gap-6">

{{-- Kaos Jersey --}}
<x-ui.reveal delay="100">
    <a
        href="{{ route('catalog') }}"
        class="group relative block aspect-[4/3] overflow-hidden rounded-2xl bg-slate-300 transition-all duration-300 hover:shadow-lg active:scale-[0.98] sm:aspect-[16/10] lg:aspect-[16/9]"
    >
        <img
            src="{{ asset('images/hero/jersey4.png') }}"
            alt="Kaos Jersey Eazywear"
            class="absolute inset-0 h-full w-full object-cover object-top transition duration-500 group-hover:scale-105"
        >

        <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/10 to-transparent transition duration-300 group-hover:from-black/75"></div>

        <div class="absolute inset-x-0 bottom-0 flex items-end justify-between p-4 sm:p-5 lg:p-6">
            <div>
                <span class="block text-base font-bold uppercase tracking-[0.18em] text-white drop-shadow-lg sm:text-lg lg:text-xl">
                    Kaos Jersey
                </span>

                <span class="mt-1 block text-[8px] font-medium uppercase tracking-[0.25em] text-[#D4A72C] sm:text-[9px] lg:text-[10px]">
                    Custom Sportswear
                </span>
            </div>

            {{-- Button Panah --}}
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/50 bg-black/20 text-white backdrop-blur-md transition-all duration-300 group-hover:border-[#AE7C18] group-hover:bg-[#AE7C18] group-hover:scale-110 sm:h-10 sm:w-10">
                <svg
                    class="h-4 w-4 transition-transform duration-300 group-hover:scale-110 sm:h-[18px] sm:w-[18px]"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="1.8"
                        d="M5 12h14m-6-6 6 6-6 6"
                    />
                </svg>
            </div>
        </div>
    </a>
</x-ui.reveal>

        </div>

    </x-ui.container>
</section>