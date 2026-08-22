@props(['product'])

@php
    $thumbnail = $product->images
        ->where('is_thumbnail', true)
        ->sortBy('sort_order')
        ->first();

    $image = $thumbnail
        ? asset('storage/' . $thumbnail->image)
        : asset('images/products/placeholder.png');

    $price = $product->variants->min('price') ?? 0;
    $category = $product->category?->name ?? 'Product';
    $detailUrl = route('product.detail', ['product' => $product->id]);
@endphp

<div class="group flex h-full flex-col overflow-hidden rounded-2xl bg-white shadow-md transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
    <div
        class="relative cursor-pointer overflow-hidden"
        @click="$dispatch('quick-view', {
            id: @js($product->id),
            url: @js($detailUrl),
            title: @js($product->name),
            series: @js($category),
            image: @js($image),
            price: @js('Rp ' . number_format($price, 0, ',', '.'))
        })"
    >
        <img
            src="{{ $image }}"
            alt="{{ $product->name }}"
            class="aspect-[3/4] w-full object-cover transition duration-700 group-hover:scale-110"
        >

        <div class="absolute inset-0 hidden items-center justify-center bg-black/0 transition duration-500 lg:flex lg:group-hover:bg-black/30">
            <div class="translate-y-6 opacity-0 transition duration-500 group-hover:translate-y-0 group-hover:opacity-100">
                <button
                    type="button"
                    class="rounded-full bg-white/95 px-6 py-3 font-semibold shadow-xl transition hover:bg-[#AE7C18] hover:text-white"
                >
                    Quick View
                </button>
            </div>
        </div>
    </div>

    <div class="flex flex-1 flex-col p-3 lg:p-5">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-[#AE7C18] lg:text-xs">
            {{ $category }}
        </p>

        <h3 class="mt-2 line-clamp-2 text-base font-bold leading-snug text-gray-900 transition duration-300 group-hover:text-[#AE7C18] lg:text-xl">
            {{ $product->name }}
        </h3>

        <div class="mt-2 lg:mt-3">
            <p class="text-[10px] uppercase tracking-wide text-gray-500 lg:text-sm">
                <span class="hidden lg:inline">Starting from</span>
                <span class="lg:hidden">From</span>
            </p>

            <h4 class="mt-1 text-lg font-bold leading-none text-[#AE7C18] lg:text-2xl">
                Rp {{ number_format($price, 0, ',', '.') }}
            </h4>
        </div>

        <div class="mt-auto pt-3">
            <x-ui.button
                :href="$detailUrl"
                variant="outline"
                class="w-full text-xs lg:text-base"
            >
                View Detail
            </x-ui.button>
        </div>
    </div>
</div>