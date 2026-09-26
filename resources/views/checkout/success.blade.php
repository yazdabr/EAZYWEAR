@extends('layouts.website')
@section('title', 'Pesanan Berhasil - Eazywear Indonesia')
@section('content')
<section class="min-h-[70vh] bg-gray-50 py-6 sm:py-16 lg:py-20">
    <x-ui.container>
        <div class="mx-auto max-w-3xl px-1 sm:px-0">
            {{-- HEADER SUKSES --}}
            <div class="text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 sm:h-20 sm:w-20">
                    <x-heroicon-o-check class="h-6 w-6 text-emerald-600 sm:h-10 sm:w-10"/>
                </div>
                <p class="mt-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mt-6 sm:text-xs">PESANAN DIBUAT</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900 sm:mt-2 sm:text-4xl">Pesanan Berhasil Dibuat</h1>
                <p class="mx-auto mt-1.5 max-w-xl text-xs leading-relaxed text-gray-500 sm:mt-4 sm:text-base sm:leading-6">Terima kasih telah melakukan pemesanan di Eazywear. Silakan simpan nomor invoice berikut sebagai referensi pesanan Anda.</p>
            </div>

            {{-- KARTU PESANAN --}}
            <div class="mt-5 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm sm:mt-8 sm:rounded-3xl">
                {{-- INVOICE --}}
                <div class="bg-slate-50 px-4 py-4 sm:px-8 sm:py-6">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-[#AE7C18] sm:text-xs">Invoice</p>
                            <p class="mt-0.5 text-[10px] text-gray-500 sm:mt-1 sm:text-xs">Simpan nomor invoice ini untuk referensi pesanan Anda.</p>
                            <p class="mt-1.5 break-all text-base font-extrabold tracking-wide text-slate-900 sm:mt-3 sm:text-2xl">{{ $transaction->invoice_number }}</p>
                        </div>
                        <span class="inline-flex shrink-0 rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700 sm:px-4 sm:py-1.5 sm:text-xs">{{ $transaction->status }}</span>
                    </div>
                </div>

                <div class="px-4 sm:px-8">
                    {{-- PELANGGAN & PENGIRIMAN --}}
                    <div class="grid gap-4 border-b border-gray-100 py-4 sm:grid-cols-2 sm:gap-10 sm:py-6">
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 sm:text-xs">Pelanggan</p>
                            <p class="mt-1 text-xs font-bold text-slate-900 sm:mt-1.5 sm:text-base">{{ $transaction->shipping_name }}</p>
                            <p class="mt-0.5 text-xs text-gray-500 sm:mt-1 sm:text-sm">{{ $transaction->shipping_email }}</p>
                            <p class="text-xs text-gray-500 sm:mt-0.5 sm:text-sm">{{ $transaction->shipping_phone }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 sm:text-xs">Alamat Pengiriman</p>
                            <p class="mt-1 text-xs font-bold leading-normal text-slate-900 sm:mt-1.5 sm:text-sm sm:leading-6">{{ $transaction->shipping_address }}</p>
                            <p class="mt-0.5 text-xs leading-normal text-gray-500 sm:mt-1 sm:text-sm sm:leading-6">{{ $transaction->shipping_district }}, {{$transaction->shipping_city }}</p>
                            <p class="text-xs leading-normal text-gray-500 sm:text-sm sm:leading-6">{{ $transaction->shipping_province }} · {{$transaction->shipping_postal_code }}</p>
                            @if($transaction->shipping_method)
                                <p class="mt-1.5 text-xs font-bold text-slate-900 sm:mt-3 sm:text-sm">Pengiriman: {{ $transaction->shipping_method }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- DETAIL PESANAN --}}
                    <div class="py-4 sm:py-6">
                        <h2 class="text-sm font-bold text-slate-900 sm:text-lg">Detail Pesanan</h2>
                        <div class="mt-3 space-y-3 sm:mt-5 sm:space-y-5">
                            @foreach($transaction->items as$item)
                                <div class="flex items-start justify-between gap-3 text-xs sm:gap-4 sm:text-sm">
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-slate-900">{{ $item->productVariant?->product?->name ?? '-' }}</p>
                                        <div class="mt-0.5 flex flex-wrap gap-x-2 text-[11px] text-gray-500 sm:mt-1 sm:gap-x-3 sm:text-xs">
                                            <span>Ukuran: {{ $item->productVariant?->size?->name ?? '-' }}</span>
                                            <span>•</span>
                                            <span>Jumlah: {{ $item->qty }}</span>
                                        </div>
                                        @if(!empty($item->custom_name))
                                            <p class="mt-1 text-[11px] font-bold tracking-wide text-[#AE7C18] sm:text-sm">Nama Jersey: {{ $item->custom_name }}</p>
                                        @endif
                                        @if(!empty($item->custom_number))
                                            <p class="mt-0.5 text-[11px] font-bold tracking-wide text-slate-700 sm:text-sm">Nomor Punggung: {{ $item->custom_number }}</p>
                                        @endif
                                    </div>
                                    <p class="shrink-0 font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- RINGKASAN --}}
                    <div class="border-t border-gray-100 py-4 sm:py-6">
                        <div class="space-y-2 sm:space-y-3">
                            <div class="flex justify-between text-xs text-gray-600 sm:text-sm">
                                <span>Subtotal</span>
                                <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-600 sm:text-sm">
                                <span>Pengiriman</span>
                                <span>Rp {{ number_format($transaction->shipping, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-600 sm:text-sm">
                                <span>Metode Pembayaran</span>
                                <span class="font-bold text-slate-900">
                                    {{ $transaction->payment_method === 'VA' ? 'Virtual Account' :$transaction->payment_method }}
                                </span>
                            </div>
                            <div class="border-t border-gray-100 pt-2.5 sm:pt-4">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-xs font-bold text-slate-900 sm:text-base">Total</span>
                                    <span class="text-base font-extrabold text-[#AE7C18] sm:text-2xl">Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- INFORMASI PEMBAYARAN VA --}}
            @if($transaction->payment_method === 'VA')
                <div class="mt-4 overflow-hidden rounded-xl border border-blue-200 bg-white shadow-sm sm:mt-6 sm:rounded-3xl">
                    <div class="bg-blue-50 px-4 py-3.5 sm:px-8 sm:py-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 sm:h-10 sm:w-10">
                                <x-heroicon-o-building-library class="h-4 w-4 text-blue-700 sm:h-5 sm:w-5"/>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-blue-700 sm:text-sm">
                                    Pembayaran Virtual Account
                                </p>
                                <p class="mt-0.5 text-[10px] text-blue-600 sm:text-xs">
                                    Gunakan informasi berikut untuk melakukan pembayaran.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3.5 px-4 py-4 sm:space-y-4 sm:px-8 sm:py-6">
                        @if($transaction->va_number)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 sm:text-sm">
                                    Nomor Virtual Account
                                </p>

                                <div class="mt-1.5 flex items-center justify-between gap-2.5 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 sm:px-4 sm:py-3">
                                    <p
                                        id="va-number"
                                        class="break-all text-base font-extrabold tracking-wide text-slate-900 sm:text-2xl"
                                    >
                                        {{ $transaction->va_number }}
                                    </p>
                                    <button
                                        type="button"
                                        id="copy-va-button"
                                        class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-blue-600 bg-white px-2.5 py-1.5 text-[11px] font-bold text-blue-600 transition hover:bg-blue-50 sm:gap-1.5 sm:px-3 sm:py-2 sm:text-xs"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-3.5 w-3.5 sm:h-4 sm:w-4"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2M10 20h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                            />
                                        </svg>

                                        <span>
                                            Salin
                                        </span>
                                    </button>
                                </div>
                            </div>

                            @if($transaction->va_bank)
                                <div class="flex items-center justify-between gap-4 text-xs sm:text-sm">
                                    <span class="text-gray-500">Bank</span>
                                    <span class="font-bold text-slate-900">
                                        {{ $transaction->va_bank }}
                                    </span>
                                </div>
                            @endif

                            @if($transaction->va_expired_at)
                                <div class="flex items-center justify-between gap-4 text-xs sm:text-sm">
                                    <span class="text-gray-500">Batas Pembayaran</span>
                                    <span class="text-right font-bold text-slate-900">
                                        {{ $transaction->va_expired_at
                                            ->copy()
                                            ->setTimezone('Asia/Makassar')
                                            ->format('d M Y, h:i A')
                                        }}
                                    </span>
                                </div>

                                <div
                                    id="payment-countdown"
                                    data-expires-at="{{ $transaction->va_expired_at->timestamp * 1000 }}"
                                    class="rounded-xl border border-red-200 bg-red-50 p-3 sm:px-4 sm:py-3"
                                >
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-xs font-semibold text-red-700 sm:text-sm">
                                            Waktu pembayaran tersisa
                                        </span>

                                        <span
                                            id="payment-countdown-timer"
                                            class="text-base font-extrabold tabular-nums text-red-700 sm:text-xl"
                                        >
                                            00:00
                                        </span>
                                    </div>

                                    <p
                                        id="payment-countdown-message"
                                        class="mt-0.5 text-[11px] text-red-600 sm:mt-1 sm:text-xs"
                                    >
                                        Segera selesaikan pembayaran Anda.
                                    </p>
                                </div>
                            @endif

                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-2.5 sm:p-3">
                                <p class="text-[11px] leading-relaxed text-amber-800 sm:text-sm">
                                    Pastikan nominal pembayaran sesuai dengan total pesanan Anda.
                                </p>
                            </div>
                        @else
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 sm:p-4">
                                <p class="text-xs font-semibold text-amber-900 sm:text-sm">
                                    Nomor Virtual Account belum tersedia
                                </p>

                                <p class="mt-1 text-[11px] leading-relaxed text-amber-800 sm:text-sm sm:leading-5">
                                    Pesanan sudah tercatat, tetapi nomor VA belum berhasil dibuat.
                                    Silakan tunggu informasi pembayaran berikutnya.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- NOTIFIKASI PEMBAYARAN --}}
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3.5 sm:mt-6 sm:rounded-2xl sm:p-5">
                <div class="flex items-start gap-2.5 sm:gap-3">
                    <x-heroicon-o-information-circle class="mt-0.5 h-4 w-4 shrink-0 text-amber-600 sm:h-5 sm:w-5"/>
                    <div>
                        <p class="text-[11px] leading-relaxed text-amber-800 sm:text-sm sm:leading-6">
                            Pesanan Anda telah tercatat dengan status
                            <strong>{{ $transaction->status }}</strong>.
                            Nomor Virtual Account akan ditampilkan setelah berhasil dibuat oleh sistem.
                        </p>
                    </div>
                </div>
            </div>

            {{-- TOMBOL --}}
            <div class="mt-5 flex flex-col gap-2 sm:mt-8 sm:flex-row sm:justify-center sm:gap-3">
                <a href="{{ route('catalog') }}" class="inline-flex h-10 items-center justify-center rounded-full border border-gray-200 bg-white px-5 text-xs font-semibold text-gray-700 transition hover:border-[#AE7C18] hover:text-[#AE7C18] sm:h-12 sm:px-6 sm:text-sm">Belanja Lagi</a>
                <a href="{{ route('home') }}" class="inline-flex h-10 items-center justify-center rounded-full bg-[#AE7C18] px-5 text-xs font-semibold text-white shadow-md shadow-[#AE7C18]/20 transition hover:bg-[#8F6514] sm:h-12 sm:px-6 sm:text-sm">Kembali ke Beranda</a>
            </div>
        </div>
    </x-ui.container>
</section>
@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {

    const countdown = document.getElementById('payment-countdown');
    const timer = document.getElementById('payment-countdown-timer');
    const message = document.getElementById('payment-countdown-message');

    if (countdown && timer && message) {

        const expiresAt = Number(
            countdown.dataset.expiresAt
        );

        const updateCountdown = () => {

            const remaining = Math.max(
                0,
                expiresAt - Date.now()
            );

            const totalSeconds = Math.floor(
                remaining / 1000
            );

            const minutes = Math.floor(
                totalSeconds / 60
            );

            const seconds = totalSeconds % 60;

            timer.textContent =
                `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;

            if (remaining <= 0) {

                timer.textContent = '00:00';

                message.textContent =
                    'Waktu pembayaran telah berakhir.';

                countdown.classList.remove(
                    'border-red-200',
                    'bg-red-50'
                );

                countdown.classList.add(
                    'border-gray-200',
                    'bg-gray-100'
                );

                clearInterval(interval);
            }
        };

        updateCountdown();

        const interval = setInterval(
            updateCountdown,
            1000
        );
    }

    const copyButton =
        document.getElementById('copy-va-button');

    const vaNumber =
        document.getElementById('va-number');

    if (copyButton && vaNumber) {

        copyButton.addEventListener(
            'click',
            async () => {

                await navigator.clipboard.writeText(
                    vaNumber.innerText.trim()
                );

                copyButton.querySelector('span').textContent =
                    'Tersalin';

                setTimeout(() => {

                    copyButton.querySelector('span').textContent =
                        'Salin';

                }, 2000);

            }
        );

    }

});
</script>