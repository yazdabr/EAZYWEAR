@props([
    'product',
])

<div
    class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-md transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">

    {{-- Image --}}
    <div
        class="relative overflow-hidden cursor-pointer"

        @click="$dispatch('quick-view', {

            title: '{{ $product['title'] }}',

            series: '{{ $product['series'] }}',

            image: '{{ asset($product['image']) }}',

            price: '{{ $product['price'] }}'

        })">

        @if(!empty($product['badge']))

            <span
                class="absolute left-2 top-2 z-20 rounded-full bg-[#18AE3D] px-2 py-1 text-[9px] font-semibold text-white lg:left-4 lg:top-4 lg:px-3 lg:py-1 lg:text-xs">

                {{ $product['badge'] }}

            </span>

        @endif

        <img
            src="{{ asset($product['image']) }}"
            alt="{{ $product['title'] }}"
            class="aspect-[3/4] w-full object-cover transition duration-700 group-hover:scale-110">

        {{-- Desktop Overlay --}}
        <div
            class="absolute inset-0 hidden items-center justify-center bg-black/0 transition duration-500 lg:flex lg:group-hover:bg-black/30">

            <div
                class="translate-y-6 opacity-0 transition duration-500 group-hover:translate-y-0 group-hover:opacity-100">

                <button
                    class="rounded-full bg-white/95 px-6 py-3 font-semibold shadow-xl transition hover:bg-[#AE7C18] hover:text-white">

                    Quick View

                </button>

            </div>

        </div>

    </div>

    {{-- Content --}}
    <div class="flex flex-1 flex-col p-3 lg:p-5">

        <p
            class="text-[10px] font-semibold uppercase tracking-wide text-[#AE7C18] lg:text-xs">

            {{ $product['series'] }}

        </p>

        <h3
            class="mt-2 line-clamp-2 text-base font-bold leading-snug text-gray-900 transition duration-300 group-hover:text-[#AE7C18] lg:text-xl">

            {{ $product['title'] }}

        </h3>

        <div class="mt-2 lg:mt-3">

            <p class="text-[10px] uppercase tracking-wide text-gray-500 lg:text-sm">

                <span class="hidden lg:inline">

                    Starting from

                </span>

                <span class="lg:hidden">

                    From

                </span>

            </p>

            <h4
                class="mt-1 text-lg font-bold leading-none text-[#AE7C18] lg:text-2xl">

                {{ $product['price'] }}

            </h4>

        </div>

        <div class="mt-auto pt-3">

            <x-ui.button
                :href="route('product.detail')"
                variant="outline"
                class="w-full text-xs lg:text-base">

                View Detail

            </x-ui.button>

        </div>

    </div>

</div>