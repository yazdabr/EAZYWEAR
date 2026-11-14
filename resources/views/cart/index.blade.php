@extends('layouts.website')

@section('title', 'Shopping Cart')

@section('content')
<section
    class="py-8 sm:py-14 lg:py-20"
    x-data="cartCheckoutConfirm()"
    data-checkout-url="{{ route('checkout.index') }}"
>
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
                <a href="{{ route('shop') }}" class="mt-5 inline-flex items-center justify-center rounded-full bg-slate-900 px-6 py-3 text-xs font-semibold text-white transition hover:bg-slate-800 sm:mt-6 sm:text-sm">
                    Explore Shop
                </a>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-3 lg:gap-8">
                @foreach($cart as $cartKey => $item)
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:rounded-3xl sm:p-5 lg:col-span-2">
                        <div class="flex gap-3.5 sm:gap-4">
                            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-gray-100 sm:h-28 sm:w-28 sm:rounded-2xl">
                                <img src="{{ $item['image'] }}" alt="{{ $item['product_name'] }}" class="h-full w-full object-cover">
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <h2 class="text-sm font-bold text-slate-900 sm:text-lg">{{ $item['product_name'] }}</h2>
                                        <p class="text-xs text-gray-500 sm:text-sm">Size: {{ $item['size_name'] }}</p>
                                        @if(!empty($item['is_longsleeve']))
                                            <div class="mt-2">
                                                <span class="inline-flex items-center rounded-full border border-[#AE7C18]/30 bg-[#AE7C18]/10 px-2.5 py-1 text-[10px] font-semibold text-[#AE7C18] sm:text-xs">
                                                    Longsleeve
                                                </span>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Biaya tambahan:
                                                    Rp {{ number_format((int) ($item['longsleeve_price'] ?? 0), 0, ',', '.') }}
                                                </p>
                                            </div>
                                        @endif
                                        @if(!empty($item['is_patch']))
                                            <div class="mt-2">
                                                <span class="inline-flex items-center rounded-full border border-[#AE7C18]/30 bg-[#AE7C18]/10 px-2.5 py-1 text-[10px] font-semibold text-[#AE7C18] sm:text-xs">
                                                    Patch
                                                </span>
                                                <p class="mt-1 text-xs text-gray-500">
                                                    Biaya tambahan:
                                                    Rp {{ number_format((int) ($item['patch_price'] ?? 0), 0, ',', '.') }}
                                                </p>
                                            </div>
                                        @endif
                                        @if(!empty($item['custom_name']) || !empty($item['custom_number']))
                                            <div class="mt-2 space-y-1.5">
                                                @if(!empty($item['custom_name']))
                                                    <div>
                                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 sm:text-xs">Name On Jersey</p>
                                                        <p class="mt-0.5 text-sm font-bold uppercase tracking-wide text-slate-900 sm:text-base">{{ $item['custom_name'] }}</p>
                                                    </div>
                                                @endif
                                                @if(!empty($item['custom_number']))
                                                    <div>
                                                        <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 sm:text-xs">Back Number</p>
                                                        <p class="mt-0.5 text-sm font-bold tracking-wide text-slate-900 sm:text-base">{{ $item['custom_number'] }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                    <form method="POST" action="{{ route('cart.remove', $cartKey) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-full p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-500 sm:p-2" aria-label="Remove product">
                                            <x-heroicon-o-trash class="h-4 w-4 sm:h-5 sm:w-5"/>
                                        </button>
                                    </form>
                                </div>
                                <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="text-sm font-bold text-[#AE7C18] sm:text-lg">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                    <form method="POST" action="{{ route('cart.update', $cartKey) }}" class="flex items-center gap-1.5 sm:gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" name="qty" value="{{ max(1, $item['qty'] - 1) }}" class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-600 transition hover:border-[#AE7C18] hover:text-[#AE7C18] sm:h-9 sm:w-9" @disabled($item['qty'] <= 1)>−</button>
                                        <span class="flex h-8 min-w-8 items-center justify-center rounded-full bg-gray-100 px-2.5 text-xs font-semibold text-slate-800 sm:h-9 sm:min-w-10 sm:px-3 sm:text-sm">{{ $item['qty'] }}</span>
                                        <button type="submit" name="qty" value="{{ min($item['stock'], $item['qty'] + 1) }}" class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-200 text-gray-600 transition hover:border-[#AE7C18] hover:text-[#AE7C18] sm:h-9 sm:w-9" @disabled($item['qty'] >= $item['stock'])>+</button>
                                    </form>
                                </div>
                                <div class="mt-2.5 flex items-center justify-between border-t border-gray-100 pt-2.5 sm:mt-3 sm:pt-3">
                                    <span class="text-xs text-gray-500 sm:text-sm">Subtotal</span>
                                    <span class="text-xs font-bold text-slate-900 sm:text-base">Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <aside class="h-full rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:rounded-3xl sm:p-6 lg:sticky lg:top-28 lg:col-start-3 lg:row-start-1">
                    <h2 class="text-lg font-bold text-slate-900 sm:text-xl">Order Summary</h2>
                    <div class="mt-4 space-y-3 sm:mt-6 sm:space-y-4">
                        <div class="flex items-center justify-between text-xs text-gray-600 sm:text-sm">
                            <span>{{ $totalItems }} items</span>
                            <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="border-t border-gray-100 pt-3 sm:pt-4">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-slate-900 sm:text-sm">Subtotal</span>
                                <span class="text-lg font-bold text-[#AE7C18] sm:text-xl">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <button
                            type="button"
                            x-ref="checkoutTrigger"
                            @click="openConfirmation()"
                            aria-haspopup="dialog"
                            aria-controls="checkout-confirm-dialog"
                            class="mt-3 inline-flex w-full items-center justify-center rounded-full bg-[#AE7C18] px-6 py-3.5 text-xs font-semibold text-white shadow-lg shadow-slate-900/20 transition hover:bg-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#AE7C18] focus-visible:ring-offset-2 active:scale-[0.99] sm:mt-4 sm:py-4 sm:text-sm"
                        >
                            Proceed to Checkout
                        </button>
                    </div>
                </aside>

                <div class="flex items-center justify-between gap-2 pt-2 lg:col-span-2">
                    <a href="{{ route('shop') }}" class="inline-flex shrink-0 items-center justify-center rounded-full border border-gray-300 px-3.5 py-2.5 text-xs font-semibold text-gray-700 transition hover:border-[#AE7C18] hover:text-[#AE7C18] sm:px-5 sm:py-3 sm:text-sm">Continue Shopping</a>
                    <form method="POST" action="{{ route('cart.clear') }}" class="shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center justify-center rounded-full px-3.5 py-2.5 text-xs font-semibold text-red-500 transition hover:bg-red-50 sm:px-5 sm:py-3 sm:text-sm">Clear Cart</button>
                    </form>
                </div>
            </div>
        @endif
    </x-ui.container>

    {{-- Popup konfirmasi produk & ukuran sebelum checkout --}}
    <div
        id="checkout-confirm-dialog"
        x-cloak
        x-show="confirmOpen"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/70 px-4 py-4 backdrop-blur-sm sm:py-6"
        role="dialog"
        aria-modal="true"
        aria-labelledby="checkout-confirm-title"
        aria-describedby="checkout-confirm-description checkout-confirm-warning"
        @keydown.escape.window="closeConfirmation()"
        @click.self="closeConfirmation()"
    >
        <div
            x-ref="confirmDialog"
            x-show="confirmOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-5 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-y-0 scale-100 opacity-100"
            x-transition:leave-end="translate-y-5 scale-95 opacity-0"
            @click.stop
            @keydown.tab="trapFocus($event)"
            class="relative max-h-[90dvh] w-full max-w-sm overflow-y-auto rounded-2xl bg-white shadow-2xl sm:max-w-md sm:rounded-3xl"
        >
            <button
                type="button"
                @click="closeConfirmation()"
                class="absolute right-3 top-2.5 z-10 inline-flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition duration-200 hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/30 sm:right-4 sm:top-4 sm:h-9 sm:w-9"
                aria-label="Tutup konfirmasi checkout"
                title="Tutup"
            >
                <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round"
                     aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18"/>
                </svg>
            </button>

            <div class="px-4 pb-5 pt-6 text-center sm:px-8 sm:py-8">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#AE7C18]/10 text-[#AE7C18] ring-4 ring-[#AE7C18]/5 sm:h-16 sm:w-16 sm:ring-8">
                    <svg class="h-6 w-6 sm:h-8 sm:w-8"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                         aria-hidden="true">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 16v-4M12 8h.01"/>
                    </svg>
                </div>

                <h2 id="checkout-confirm-title" class="mt-4 text-lg font-bold leading-snug text-slate-900 sm:mt-5 sm:text-2xl">
                    Pesanan Sudah Sesuai?
                </h2>

                <p id="checkout-confirm-description" class="mt-1.5 text-xs leading-5 text-slate-500 sm:mt-2 sm:text-sm sm:leading-6">
                    Periksa kembali produk dan ukuran pilihan Anda.
                </p>
                <p id="checkout-confirm-warning" class="text-xs font-semibold leading-5 text-amber-900 sm:text-sm sm:leading-6">
                    Ukuran yang sudah dibeli tidak dapat ditukar.
                </p>

                <div class="mt-4 grid grid-cols-2 gap-2 sm:mt-6 sm:gap-3">
                    <button
                        type="button"
                        @click="closeConfirmation()"
                        class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-2 text-center text-[11px] font-semibold leading-tight text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#AE7C18]/40 active:scale-[0.98] sm:h-12 sm:px-2 sm:text-[13px]"
                        aria-label="Periksa kembali produk dan ukuran di keranjang"
                    >
                        Periksa Kembali
                    </button>

                    <button
                        type="button"
                        x-ref="confirmCheckoutButton"
                        @click="proceedToCheckout()"
                        class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-[#AE7C18] px-2 text-center text-[11px] font-semibold leading-tight text-white shadow-lg shadow-amber-900/10 transition hover:bg-[#8F6514] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#AE7C18]/40 focus-visible:ring-offset-2 active:scale-[0.98] sm:h-12 sm:px-2 sm:text-[13px]"
                        aria-label="Ya, lanjutkan ke halaman checkout"
                    >
                        Ya, Lanjutkan Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    function cartCheckoutConfirm() {
        return {
            checkoutUrl: @js(route('checkout.index')),
            confirmOpen: false,
            bodyOverflow: '',
            previouslyFocused: null,

            openConfirmation() {
                if (this.confirmOpen) return;

                this.previouslyFocused = document.activeElement;
                this.bodyOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
                this.confirmOpen = true;

                this.$nextTick(() => {
                    this.$refs.confirmCheckoutButton?.focus();
                });
            },

            closeConfirmation() {
                if (!this.confirmOpen) return;

                this.confirmOpen = false;
                document.body.style.overflow = this.bodyOverflow || '';

                this.$nextTick(() => {
                    const target = this.previouslyFocused && this.previouslyFocused !== document.body
                        ? this.previouslyFocused
                        : this.$refs.checkoutTrigger;

                    target?.focus?.();
                });
            },

            proceedToCheckout() {
                if (!this.confirmOpen) return;

                this.confirmOpen = false;
                document.body.style.overflow = this.bodyOverflow || '';

                window.location.assign(this.checkoutUrl);
            },

            trapFocus(event) {
                if (!this.confirmOpen) return;

                const focusable = Array.from(
                    this.$refs.confirmDialog.querySelectorAll(
                        'button:not([disabled]), [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    )
                ).filter((element) => element.offsetParent !== null);

                if (focusable.length === 0) return;

                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            },
        };
    }
</script>
@endpush
