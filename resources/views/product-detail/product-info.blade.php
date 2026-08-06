<section
    x-data="gallery()"
    class="bg-white py-14">

    <x-ui.container>

        <div class="grid gap-16 lg:grid-cols-2">

            {{-- Gallery --}}
            <div>

                <div
                    class="overflow-hidden rounded-3xl shadow-xl">

                    <img
                        :src="currentImage"
                        class="aspect-square w-full object-cover transition duration-500">

                </div>

                {{-- Thumbnail --}}
                <div class="mt-5 flex gap-4">

                    <template
                        x-for="image in images">

                        <button
                            @click="currentImage=image"
                            class="overflow-hidden rounded-xl border-2 transition"
                            :class="currentImage===image ? 'border-[#AE7C18]' : 'border-gray-200'">

                            <img
                                :src="image"
                                class="h-24 w-24 object-cover">

                        </button>

                    </template>

                </div>

            </div>

            {{-- CTA WhatsApp (Mobile Only) --}}
            <div class="mt-2 lg:hidden">

                <a
                    href="https://wa.me/6285754431105?text=Halo%20Eazywear,%20saya%20ingin%20bertanya%20tentang%20produk%20Apex%20Pro%20Kit."
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex w-full items-center justify-center gap-3 rounded-full border-2 border-[#AE7C18] px-6 py-4 text-lg font-semibold text-[#AE7C18] transition-all duration-300 hover:bg-[#AE7C18] hover:text-white hover:shadow-lg">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="currentColor"
                        viewBox="0 0 24 24">

                        <path d="M20.52 3.48A11.86 11.86 0 0012.05 0C5.5 0 .17 5.33.17 11.88c0 2.1.55 4.15 1.59 5.96L0 24l6.35-1.67a11.88 11.88 0 005.7 1.45h.01c6.55 0 11.88-5.33 11.88-11.88 0-3.17-1.23-6.15-3.42-8.42zm-8.47 18.3h-.01a9.87 9.87 0 01-5.03-1.38l-.36-.21-3.77.99 1.01-3.67-.23-.38a9.84 9.84 0 01-1.51-5.25c0-5.45 4.44-9.88 9.9-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 012.9 6.98c0 5.46-4.44 9.9-9.89 9.9zm5.43-7.39c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.66.15-.2.3-.76.97-.93 1.17-.17.2-.35.22-.65.07-.3-.15-1.27-.47-2.42-1.5-.9-.8-1.5-1.8-1.68-2.1-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.48-.5-.66-.5h-.56c-.2 0-.52.08-.8.37-.27.3-1.04 1.02-1.04 2.5s1.07 2.9 1.22 3.1c.15.2 2.1 3.2 5.08 4.49.71.3 1.27.48 1.7.62.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.42.25-.69.25-1.28.17-1.42-.08-.13-.27-.2-.57-.35z"/>

                    </svg>

                    <span>

                        Tanyakan Produk

                    </span>

                </a>

            </div>

            {{-- Product --}}
            <div>

                <p
                    class="text-xs font-semibold uppercase tracking-[0.3em] text-[#AE7C18]">

                    FOOTBALL // PRO LINE

                </p>

                <h1
                    class="mt-3 text-5xl font-bold">

                    Apex Pro Kit

                </h1>

                <h2
                    class="mt-4 text-4xl font-bold text-[#AE7C18]">

                    Starting from Rp 149.000

                </h2>

                <p
                    class="mt-10 text-lg leading-8 text-gray-600">

                    Engineered for the highest level of competition.
                    Our Apex Pro Kit features premium dry-fit fabric,
                    unlimited custom sublimation,
                    and professional-grade finishing.

                </p>

                {{-- Size --}}
                <div class="mt-10">

                    <div class="mb-4 flex justify-between">

                        <h3
                            class="font-semibold uppercase">

                            Available Sizes

                        </h3>

                        <button
                            class="text-sm text-[#AE7C18]">

                            Size Guide

                        </button>

                    </div>

                    <div
                        x-data="{size:'M'}"
                        class="flex flex-wrap gap-3">

                        <template
                            x-for="item in ['S','M','L','XL','XXL','3XL']">

                            <button
                                @click="size=item"
                                class="h-11 min-w-[54px] rounded-full border transition"
                                :class="size===item ? 'bg-[#AE7C18] text-white border-[#AE7C18]' : 'border-gray-300 hover:border-[#AE7C18]'">

                                <span x-text="item"></span>

                            </button>

                        </template>

                    </div>

                </div>

                {{-- Color --}}
                <div class="mt-10">

                    <h3
                        class="mb-4 font-semibold uppercase">

                        Color Options

                    </h3>

                    <div
                        x-data="{color:0}"
                        class="flex gap-4">

                        <template
                            x-for="(item,index) in ['#F1DEAF','#151B43','#535C68']">

                            <button
                                @click="color=index"
                                class="h-12 w-12 rounded-full border-4 transition"
                                :class="color===index ? 'border-[#AE7C18]' : 'border-white'"
                                :style="'background:'+item">

                            </button>

                        </template>

                    </div>

                </div>

                {{-- Feature Box --}}
                <div class="mt-10 grid gap-4 sm:grid-cols-2">

                    <div
                        class="rounded-2xl bg-[#AE7C18] p-5 text-white">

                        <h4
                            class="font-semibold">

                            Aero-Mesh Fabric

                        </h4>

                        <p
                            class="mt-2 text-sm">

                            Moisture-wicking &
                            breathable

                        </p>

                    </div>

                    <div
                        class="rounded-2xl bg-[#AE7C18] p-5 text-white">

                        <h4
                            class="font-semibold">

                            Production Time

                        </h4>

                        <p
                            class="mt-2 text-sm">

                            10–14 Working Days

                        </p>

                    </div>

                </div>

                {{-- CTA WhatsApp --}}
                <div class="mt-10 hidden lg:block">

                    <a
                        href="https://wa.me/6285754431105?text=Halo%20Eazywear,%20saya%20ingin%20bertanya%20tentang%20produk%20Apex%20Pro%20Kit."
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex w-full items-center justify-center gap-3 rounded-full border-2 border-[#AE7C18] px-6 py-4 text-lg font-semibold text-[#AE7C18] transition-all duration-300 hover:bg-[#AE7C18] hover:text-white hover:shadow-lg">

                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-6 w-6"
                            fill="currentColor"
                            viewBox="0 0 24 24">

                            <path d="M20.52 3.48A11.86 11.86 0 0012.05 0C5.5 0 .17 5.33.17 11.88c0 2.1.55 4.15 1.59 5.96L0 24l6.35-1.67a11.88 11.88 0 005.7 1.45h.01c6.55 0 11.88-5.33 11.88-11.88 0-3.17-1.23-6.15-3.42-8.42zm-8.47 18.3h-.01a9.87 9.87 0 01-5.03-1.38l-.36-.21-3.77.99 1.01-3.67-.23-.38a9.84 9.84 0 01-1.51-5.25c0-5.45 4.44-9.88 9.9-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 012.9 6.98c0 5.46-4.44 9.9-9.89 9.9zm5.43-7.39c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.66.15-.2.3-.76.97-.93 1.17-.17.2-.35.22-.65.07-.3-.15-1.27-.47-2.42-1.5-.9-.8-1.5-1.8-1.68-2.1-.17-.3-.02-.46.13-.61.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.48-.5-.66-.5h-.56c-.2 0-.52.08-.8.37-.27.3-1.04 1.02-1.04 2.5s1.07 2.9 1.22 3.1c.15.2 2.1 3.2 5.08 4.49.71.3 1.27.48 1.7.62.71.23 1.36.2 1.87.12.57-.08 1.76-.72 2.01-1.42.25-.69.25-1.28.17-1.42-.08-.13-.27-.2-.57-.35z"/>

                        </svg>

                        <span>

                            Tanyakan Produk

                        </span>

                    </a>

                </div>

            </div>

        </div>

    </x-ui.container>

</section>

<script>

function gallery(){

    return{

        images:[

            "{{ asset('images/products/detail/1.png') }}",

            "{{ asset('images/products/detail/2.png') }}",

            "{{ asset('images/products/detail/3.png') }}",

            "{{ asset('images/products/detail/4.png') }}",

            "{{ asset('images/products/detail/5.png') }}"

        ],

        currentImage:"{{ asset('images/products/detail/1.png') }}"

    }

}

</script>