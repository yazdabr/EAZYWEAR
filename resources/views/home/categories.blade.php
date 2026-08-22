<section class="bg-gray-50 py-12 sm:py-20 lg:py-28">
    <x-ui.container>
        {{-- Heading --}}
        <x-ui.reveal>
            <div class="mb-8 text-center sm:mb-14 lg:mb-16">
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
        <div class="mx-auto grid max-w-4xl gap-4 sm:gap-8 md:grid-cols-2">
            {{-- Kaos Jersey --}}
            <x-ui.reveal delay="100">
                <x-website.category-card
                    title="Kaos Jersey"
                    image="images/categories/adsy.png"
                    href="{{ route('catalog', [
                        'category' => \App\Models\Category::where('name', 'Kaos Jersey')->value('id')
                    ]) }}"
                />
            </x-ui.reveal>

            {{-- Kaos Polo --}}
            <x-ui.reveal delay="200">
                <x-website.category-card
                    title="Kaos Polo"
                    image="images/categories/fortis.png"
                    href="{{ route('catalog', [
                        'category' => \App\Models\Category::where('name', 'Kaos Polo')->value('id')
                    ]) }}"
                />
            </x-ui.reveal>
        </div>
    </x-ui.container>
</section>