<section class="bg-white py-12 sm:py-20 lg:py-28">
    <x-ui.container>
        {{-- Heading --}}
        <x-ui.reveal>
            <div class="mb-8 text-center sm:mb-14 lg:mb-16">
                <p class="mb-2 text-[9px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mb-3 sm:text-xs lg:tracking-[0.3em]">
                    TESTIMONIALS
                </p>

                <h2 class="mx-auto max-w-3xl text-2xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                    Trusted by Over 500+ Teams
                    <span class="inline sm:block">in Indonesia</span>
                </h2>

                <p class="mx-auto mt-3 max-w-2xl text-xs leading-relaxed text-gray-600 sm:mt-6 sm:text-base sm:leading-8 lg:text-lg">
                    <span class="block sm:hidden">
                        Hear what our customers have to say about the quality, service, and experience of working with Eazywear.
                    </span>

                    <span class="hidden sm:inline">
                        Hear what our customers have to say about the quality,
                        service, and experience of working with Eazywear for their
                        custom sportswear needs.
                    </span>
                </p>
            </div>
        </x-ui.reveal>

        {{-- Testimonials --}}
        <div class="grid gap-4 sm:gap-8 lg:grid-cols-3">

            {{-- Testimonial 1 --}}
            <x-ui.reveal animation="scale" :index="0">
                <x-website.testimonial-card
                    name="M Rizky Ananda"
                    position="Sports Enthusiast"
                    image="images/testimonials/fortis.png"
                    quote="Kualitas jersey & desain nya sangat bagus & jgn diragukan lagi"
                />
            </x-ui.reveal>

            {{-- Testimonial 2 --}}
            <x-ui.reveal animation="scale" :index="1">
                <x-website.testimonial-card
                    name="Hadi Yani"
                    position="Sports Enthusiast"
                    image="images/testimonials/tala.png"
                    quote="Saya pernah buat baju di sana. Bagi yang ingin bikin baju bisa langsung datang ke jln ayani km 11 200jalan asang permai Handil Dua Banyu Hirang"
                />
            </x-ui.reveal>

            {{-- Testimonial 3 --}}
            <x-ui.reveal animation="scale" :index="2">
                <x-website.testimonial-card
                    name="Akmal Nurdin"
                    position="Sports Enthusiast"
                    image="images/testimonials/kuin.png"
                    quote="Produk jerseynya menggunakan bahan yang premium jdinya bagus 👍👍👍"
                />
            </x-ui.reveal>

        </div>
    </x-ui.container>
</section>