@extends('layouts.website')

@section('title', 'Shopping Cart')

@section('content')
<section class="bg-gray-50 py-8 sm:py-14 lg:py-20">
    <x-ui.container>
        <div class="mb-6 sm:mb-8">
            <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-[#AE7C18] sm:text-xs">
                SHOPPING CART
            </p>
            <h1 class="mt-1.5 text-2xl font-bold text-slate-900 sm:text-4xl">
                Your Shopping Cart
            </h1>
            <p class="mt-2 text-xs text-gray-500 sm:mt-3 sm:text-base">
                Review your items before proceeding to checkout.
            </p>
        </div>

        @if($cart->isEmpty())
            <div class="rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-sm sm:rounded-3xl sm:py-14">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#AE7C18]/15 sm:h-16 sm:w-16">
                    <x-heroicon-o-shopping-cart class="h-7 w-7 text-[#AE7C18] sm:h-8 sm:w-8"/>
                </div>
                <h2 class="mt-4 text-lg font-bold text-slate-900 sm:mt-5 sm:text-xl">
                    Your cart is empty
                </h2>
                <p class="mx-auto mt-2 max-w-md text-xs leading-relaxed text-gray-500 sm:text-sm sm:leading-6">
                    Choose the products you want to buy and add them to your cart.
                </p>
                <a href="{{ route('catalog') }}" class="mt-5 inline-flex items-center justify-center rounded-full bg-slate-900 px-6 py-3 text-xs font-semibold text-white transition hover:bg-slate-800 sm:mt-6 sm:text-sm">
                    Explore Catalog
                </a>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-3 lg:gap-8">
                <div class="space-y-4 lg:col-span-2">
                    @foreach($cart as $item)
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:rounded-3xl sm:p-5">
                            <div class="flex gap-3.5 sm:gap-4">
                                <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-gray-100 sm:h-28 sm:w-28 sm:rounded-2xl">
                                    <img src="{{ $item['image'] }}" alt="{{ $item['product_name'] }}" class="h-full w-full object-cover">
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <div>
                                            <p class="text-[10px] font-semibold uppercase tracking-wider text-[#AE7C18] sm:text-xs">
                                                {{ $item['color_name'] ?: 'Product' }}
                                            </p>
                                            <h2 class="mt-0.5 text-sm font-bold text-slate-900 sm:mt-1 sm:text-lg">
                                                {{ $item['product_name'] }}
                                            </h2>
                                            <p class="text-xs text-gray-500 sm:text-sm">
                                                Size: {{ $item['size_name'] }}
                                            </p>
                                            @if(!empty($item['custom_name']))
                                                <div class="mt-2">
                                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 sm:text-xs">
                                                        Name On Jersey
                                                    </p>
                                                    <p class="mt-0.5 text-sm font-bold uppercase tracking-wide text-slate-900 sm:text-base">
                                                        {{ $item['custom_name'] }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>

                                        <form method="POST" action="{{ route('cart.remove', $item['variant_id']) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500 sm:p-2" aria-label="Remove product">
                                                <x-heroicon-o-trash class="h-4 w-4 sm:h-5 sm:w-5"/>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm font-bold text-[#AE7C18] sm:text-lg">
                                                Rp {{ number_format($item['price'], 0, ',', '.') }}
                                            </p>
                                        </div>

                                        <div class="flex items-center justify-between gap-3 sm:justify-end">
                                            <form method="POST" action="{{ route('cart.update', $item['variant_id']) }}" class="flex items-center gap-1.5 sm:gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" name="qty" value="{{ max(1, $item['qty'] - 1) }}" class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-600 transition hover:border-[#AE7C18] hover:text-[#AE7C18] sm:h-9 sm:w-9" @disabled($item['qty'] <= 1)>
                                                    −
                                                </button>
                                                <span class="flex h-8 min-w-8 items-center justify-center rounded-full bg-gray-100 px-2.5 text-xs font-semibold text-slate-800 sm:h-9 sm:min-w-10 sm:px-3 sm:text-sm">
                                                    {{ $item['qty'] }}
                                                </span>
                                                <button type="submit" name="qty" value="{{ min($item['stock'], $item['qty'] + 1) }}" class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-600 transition hover:border-[#AE7C18] hover:text-[#AE7C18] sm:h-9 sm:w-9" @disabled($item['qty'] >= $item['stock'])>
                                                    +
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="mt-2.5 flex items-center justify-between border-t border-gray-100 pt-2.5 sm:mt-3 sm:pt-3">
                                        <span class="text-xs text-gray-500 sm:text-sm">Subtotal</span>
                                        <span class="text-xs font-bold text-slate-900 sm:text-base">
                                            Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="flex items-center justify-between gap-2 pt-2">
                        <a href="{{ route('catalog') }}" class="inline-flex shrink-0 items-center justify-center rounded-full border border-gray-300 px-3.5 py-2.5 text-xs font-semibold text-gray-700 transition hover:border-[#AE7C18] hover:text-[#AE7C18] sm:px-5 sm:py-3 sm:text-sm">
                            Continue Shopping
                        </a>
                        <form method="POST" action="{{ route('cart.clear') }}" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center rounded-full px-3.5 py-2.5 text-xs font-semibold text-red-500 transition hover:bg-red-50 sm:px-5 sm:py-3 sm:text-sm">
                                Clear Cart
                            </button>
                        </form>
                    </div>
                </div>

                <aside class="h-fit rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:rounded-3xl sm:p-6 lg:sticky lg:top-28">
                    <h2 class="text-lg font-bold text-slate-900 sm:text-xl">
                        Order Summary
                    </h2>

                    <div class="mt-4 space-y-3 sm:mt-6 sm:space-y-4">
                        <div class="flex items-center justify-between text-xs text-gray-600 sm:text-sm">
                            <span>
                                {{ $totalItems }} items
                            </span>
                            <span>
                                Rp {{ number_format($subtotal, 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="border-t border-gray-100 pt-3 sm:pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-900 sm:text-sm">
                                    Subtotal
                                </span>
                                <span class="text-lg font-bold text-[#AE7C18] sm:text-xl">
                                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <a href="{{ route('checkout.index') }}" class="mt-3 inline-flex w-full items-center justify-center rounded-full bg-[#AE7C18] px-6 py-3.5 text-xs font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-800 sm:mt-4 sm:py-4 sm:text-sm">
                            Proceed to Checkout
                        </a>
                    </div>
                </aside>
            </div>
        @endif
    </x-ui.container>
</section>
@endsection