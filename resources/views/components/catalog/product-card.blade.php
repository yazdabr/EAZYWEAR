@props(['product'])

@php
    $thumbnail = $product->images
        ->where('is_thumbnail', true)
        ->sortBy('sort_order')
        ->first();

    $hasImage = (bool) $thumbnail;

    $image = $hasImage
        ? asset('storage/' . $thumbnail->image)
        : asset('images/products/placeholder.png');

    $price = $product->variants->min('price') ?? 0;
    $category = $product->category?->name ?? 'Product';
    $detailUrl = route('product.detail', ['product' => $product->id]);
@endphp

<div class="group flex h-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-lg lg:rounded-2xl">
    <div
        class="relative cursor-pointer overflow-hidden bg-gray-100"
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
            alt="{{ $hasImage ? $product->name . ' - Eazywear Indonesia' : 'Product image coming soon - Eazywear Indonesia' }}"
            loading="lazy"
            decoding="async"
            class="aspect-[4/5] w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
        >

        <div class="absolute inset-0 hidden items-center justify-center bg-black/0 transition-all duration-300 lg:flex lg:group-hover:bg-black/20">
            <button
                type="button"
                class="translate-y-2 rounded-full bg-white px-4 py-2 text-xs font-semibold text-slate-900 opacity-0 shadow-lg transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-[#AE7C18] hover:text-white"
            >
                Quick View
            </button>
        </div>
    </div>
    <div class="flex flex-1 flex-col px-2.5 py-2.5 sm:px-3 sm:py-3 lg:px-4 lg:py-4">
        <p class="text-[9px] font-semibold uppercase tracking-[0.16em] text-[#AE7C18] sm:text-[10px] lg:text-xs lg:tracking-[0.18em]">
            {{ $category }}
        </p>

        <h3 class="mt-1 line-clamp-2 min-h-[2rem] text-sm font-bold leading-4 text-slate-900 transition-colors duration-300 group-hover:text-[#AE7C18] sm:text-[15px] sm:leading-5 lg:mt-1.5 lg:min-h-[2.75rem] lg:text-lg lg:leading-6">
            {{ $product->name }}
        </h3>

        <div class="mt-2 sm:mt-2.5 lg:mt-2.5">
            <p class="text-[9px] font-medium uppercase tracking-wider text-gray-400 sm:text-[10px] lg:text-[11px]">
                Starting from
            </p>

            <p class="mt-0.5 text-[17px] font-bold leading-tight text-[#AE7C18] sm:text-lg lg:text-[23px]">
                Rp {{ number_format($price, 0, ',', '.') }}
            </p>
        </div>

        <div class="mt-auto pt-2.5 sm:pt-3 lg:pt-4">
            <x-ui.button
                :href="$detailUrl"
                variant="outline"
                class="h-9 w-full rounded-lg px-3 text-[10px] font-semibold sm:h-9 sm:text-[11px] lg:h-10 lg:text-xs"
            >
                View Detail
            </x-ui.button>
        </div>
    </div>
</div>