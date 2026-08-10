@php

$products = [

    [
        'name' => 'Apex Pro Kit',
        'category' => 'Jersey Sepak Bola',
        'price' => 'Rp 149.000',
        'sold' => 154,
        'progress' => 92,
        'image' => asset('images/products/1.png'),
        'badge' => 'Terlaris',
    ],

    [
        'name' => 'Zenith Elite',
        'category' => 'Jersey Basket',
        'price' => 'Rp 189.000',
        'sold' => 132,
        'progress' => 80,
        'image' => asset('images/products/2.png'),
        'badge' => null,
    ],

    [
        'name' => 'Storm Runner',
        'category' => 'Jersey Lari',
        'price' => 'Rp 169.000',
        'sold' => 118,
        'progress' => 70,
        'image' => asset('images/products/4.png'),
        'badge' => null,
    ],

    [
        'name' => 'Champion Series',
        'category' => 'Jersey Voli',
        'price' => 'Rp 149.000',
        'sold' => 95,
        'progress' => 58,
        'image' => asset('images/products/5.png'),
        'badge' => null,
    ],

];

@endphp

<div
    class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

    <div
        class="border-b border-slate-100 p-6">

        <h3
            class="text-xl font-bold text-slate-900">

            Produk Terlaris

        </h3>

        <p
            class="mt-1 text-sm text-slate-500">

            Paling laris bulan ini

        </p>

    </div>

    <div
        class="max-h-[430px] space-y-5 overflow-y-auto p-6">

        @foreach($products as $product)

            <div
                class="group rounded-2xl border border-slate-100 p-4 transition hover:border-[#AE7C18] hover:shadow-md">

                <div
                    class="flex gap-4">

                    {{-- Gambar --}}
                    <img
                        src="{{ $product['image'] }}"
                        class="h-16 w-16 rounded-xl object-cover">

                    {{-- Konten --}}
                    <div class="min-w-0 flex-1">

                        <div class="flex items-start justify-between">

                            <div>

                                <h4
                                    class="truncate font-semibold text-slate-900">

                                    {{ $product['name'] }}

                                </h4>

                                <p
                                    class="text-xs text-slate-500">

                                    {{ $product['category'] }}

                                </p>

                            </div>

                            @if($product['badge'])

                                <span
                                    class="rounded-full bg-[#AE7C18]/10 px-2 py-1 text-[10px] font-semibold text-[#AE7C18]">

                                    {{ $product['badge'] }}

                                </span>

                            @endif

                        </div>

                        <div
                            class="mt-3 flex items-center justify-between">

                            <span
                                class="font-bold text-[#AE7C18]">

                                {{ $product['price'] }}

                            </span>

                            <span
                                class="text-xs text-slate-400">

                                {{ $product['sold'] }} Terjual

                            </span>

                        </div>

                        {{-- Progres --}}
                        <div
                            class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">

                            <div
                                class="h-full rounded-full bg-[#AE7C18]"
                                style="width: {{ $product['progress'] }}%">

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        @endforeach

    </div>

</div>