<section class="bg-white py-12 sm:py-20 lg:py-28">

    <x-ui.container>

        {{-- Heading --}}
        <x-ui.reveal>

            <div class="mb-8 text-center sm:mb-14 lg:mb-16">

                <p
                    class="mb-2 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mb-4 sm:text-xs lg:tracking-[0.3em]">

                    WHY CHOOSE EAZYWEAR

                </p>

                <h2
                    class="text-2xl font-bold leading-tight sm:text-4xl lg:text-5xl">

                    Crafted for Performance

                    <br>

                    Designed for Champions

                </h2>

                <p
                    class="mx-auto mt-3 max-w-3xl text-xs leading-relaxed text-gray-600 sm:mt-6 sm:text-base sm:leading-8 lg:text-lg">

                    {{-- Ringkas Khusus Mobile --}}
                    <span class="block sm:hidden">
                        We combine premium materials and modern craftsmanship to deliver high-performance custom sportswear.
                    </span>

                    {{-- Versi Lengkap Desktop --}}
                    <span class="hidden sm:inline">
                        We combine premium materials, modern production technology,
                        and professional craftsmanship to create sportswear that
                        delivers comfort, durability, and outstanding performance.
                    </span>

                </p>

            </div>

        </x-ui.reveal>

        {{-- Feature Cards --}}
        <div class="grid gap-4 sm:gap-6 md:grid-cols-2 xl:grid-cols-4">

            {{-- Card 1 --}}
            <x-ui.reveal delay="100">

                <x-website.feature-card
                    title="Premium Material"
                    description="High-quality dry-fit fabrics that provide exceptional comfort, breathability, and durability for every activity.">

                    <x-heroicon-o-shield-check class="h-6 w-6 sm:h-8 sm:w-8"/>

                </x-website.feature-card>

            </x-ui.reveal>

            {{-- Card 2 --}}
            <x-ui.reveal delay="200">

                <x-website.feature-card
                    title="Fast Production"
                    description="Efficient production process with consistent quality, ensuring your custom jerseys are delivered on time.">

                    <x-heroicon-o-bolt class="h-6 w-6 sm:h-8 sm:w-8"/>

                </x-website.feature-card>

            </x-ui.reveal>

            {{-- Card 3 --}}
            <x-ui.reveal delay="300">

                <x-website.feature-card
                    title="Unlimited Design"
                    description="Bring your ideas to life with fully customized jersey designs tailored to your team's identity.">

                    <x-heroicon-o-paint-brush class="h-6 w-6 sm:h-8 sm:w-8"/>

                </x-website.feature-card>

            </x-ui.reveal>

            {{-- Card 4 --}}
            <x-ui.reveal delay="400">

                <x-website.feature-card
                    title="Quality Guarantee"
                    description="Every product goes through strict quality control to ensure excellent finishing and customer satisfaction.">

                    <x-heroicon-o-star class="h-6 w-6 sm:h-8 sm:w-8"/>

                </x-website.feature-card>

            </x-ui.reveal>

        </div>

    </x-ui.container>

</section>