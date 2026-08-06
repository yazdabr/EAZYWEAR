@php

$products = [

    [
        'title' => 'Apex Pro Kit',
        'series' => 'Pro Series',
        'price' => 'Rp 149.000',
        'image' => 'images/products/1.png',
        'badge' => 'Best Seller',
    ],

    [
        'title' => 'Zenith Elite',
        'series' => 'Elite Performance',
        'price' => 'Rp 189.000',
        'image' => 'images/products/2.png',
        'badge' => null,
    ],

    [
        'title' => 'Velocity Prime',
        'series' => 'Elite Performance',
        'price' => 'Rp 189.000',
        'image' => 'images/products/3.png',
        'badge' => null,
    ],

    [
        'title' => 'Storm Runner',
        'series' => 'Elite Performance',
        'price' => 'Rp 189.000',
        'image' => 'images/products/4.png',
        'badge' => null,
    ],

    [
        'title' => 'Champion Series',
        'series' => 'Pro Series',
        'price' => 'Rp 149.000',
        'image' => 'images/products/5.png',
        'badge' => null,
    ],

    [
        'title' => 'Phoenix Elite',
        'series' => 'Elite Performance',
        'price' => 'Rp 189.000',
        'image' => 'images/products/6.png',
        'badge' => null,
    ],

    [
        'title' => 'Legacy Pro',
        'series' => 'Elite Performance',
        'price' => 'Rp 189.000',
        'image' => 'images/products/7.png',
        'badge' => null,
    ],

    [
        'title' => 'Thunder Max',
        'series' => 'Elite Performance',
        'price' => 'Rp 189.000',
        'image' => 'images/products/8.png',
        'badge' => null,
    ],

];

@endphp

<section class="bg-white py-14">

    <x-ui.container>

        {{-- ================= MOBILE ================= --}}
        <div class="block lg:hidden">

            <div class="grid grid-cols-2 gap-5">

                @foreach($products as $product)

                    <x-ui.reveal
                        :index="floor($loop->index / 2)">

                        <x-catalog.product-card
                            :product="$product"/>

                    </x-ui.reveal>

                @endforeach

            </div>

        </div>

        {{-- ================= DESKTOP ================= --}}
        <div class="hidden lg:block">

            <div
                class="grid grid-cols-3 gap-8 xl:grid-cols-4">

                @foreach($products as $product)

                    <x-ui.reveal
                        :index="$loop->index">

                        <x-catalog.product-card
                            :product="$product"/>

                    </x-ui.reveal>

                @endforeach

            </div>

        </div>

    </x-ui.container>

</section>