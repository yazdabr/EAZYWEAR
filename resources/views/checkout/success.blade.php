@extends('layouts.website')
@section('title', 'Pesanan Berhasil - Eazywear Indonesia')
@section('content')
<section class="min-h-[70vh] bg-gray-50 py-6 sm:py-16 lg:py-20">
    <x-ui.container>
        <div class="mx-auto max-w-3xl px-1 sm:px-0">
            <div class="text-center">
                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 sm:h-20 sm:w-20">
                    <x-heroicon-o-check class="h-6 w-6 text-emerald-600 sm:h-10 sm:w-10"/>
                </div>
                <p class="mt-3 text-[10px] font-semibold uppercase tracking-[0.2em] text-[#AE7C18] sm:mt-6 sm:text-xs">PESANAN DIBUAT</p>
                <h1 class="mt-1 text-xl font-bold text-slate-900 sm:mt-2 sm:text-4xl">Pesanan Berhasil Dibuat</h1>
                <p class="mx-auto mt-1.5 max-w-xl text-xs leading-relaxed text-gray-500 sm:mt-4 sm:text-base sm:leading-6">Terima kasih telah melakukan pemesanan di Eazywear. Silakan simpan nomor invoice berikut sebagai referensi pesanan Anda.</p>
            </div>
            <div class="mt-5 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm sm:mt-8 sm:rounded-3xl">
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
                                <div class="mt-1.5 sm:mt-3">
                                    <p class="text-xs font-bold text-slate-900 sm:text-sm">Pengiriman: {{ $transaction->shipping_method }}</p>
                                    @if($transaction->shipping_method === 'Ambil di Tempat' && $transaction->pickup_date && $transaction->pickup_time_start && $transaction->pickup_time_end)
                                        <p class="mt-0.5 text-xs font-semibold text-slate-600 sm:text-sm">{{ \Carbon\Carbon::parse($transaction->pickup_date)->locale('id')->translatedFormat('l, d F Y') }}, {{ \Carbon\Carbon::parse($transaction->pickup_time_start)->format('H:i') }}–{{ \Carbon\Carbon::parse($transaction->pickup_time_end)->format('H:i') }} WITA</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="py-4 sm:py-6">
                        <h2 class="text-sm font-bold text-slate-900 sm:text-lg">Detail Pesanan</h2>
                        <div class="mt-3 space-y-3 sm:mt-5 sm:space-y-5">
                            @foreach($transaction->items as $item)
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
                                        @if($item->is_longsleeve)
                                            <p class="mt-1 text-[11px] font-bold tracking-wide text-[#AE7C18] sm:text-sm">
                                                Longsleeve
                                                <span class="font-medium text-gray-500">
                                                    (+ Rp {{ number_format((int) $item->longsleeve_price, 0, ',', '.') }})
                                                </span>
                                            </p>
                                        @endif
                                        @if($item->is_patch)
                                            <p class="mt-1 text-[11px] font-bold tracking-wide text-[#AE7C18] sm:text-sm">
                                                Patch
                                                <span class="font-medium text-gray-500">
                                                    (+ Rp {{ number_format((int) $item->patch_price, 0, ',', '.') }})
                                                </span>
                                            </p>
                                        @endif
                                    </div>
                                    <p class="shrink-0 font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
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
                                <span class="font-bold text-slate-900">{{ $transaction->payment_method === 'VA' ? 'Virtual Account' : $transaction->payment_method }}</span>
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
            @if($transaction->payment_method === 'VA')
                <div class="mt-4 overflow-hidden rounded-xl border border-blue-200 bg-white shadow-sm sm:mt-6 sm:rounded-3xl">
                    <div class="bg-blue-50 px-4 py-3.5 sm:px-8 sm:py-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 sm:h-10 sm:w-10">
                                <x-heroicon-o-building-library class="h-4 w-4 text-blue-700 sm:h-5 sm:w-5"/>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-blue-700 sm:text-sm">Pembayaran Virtual Account</p>
                                <p class="mt-0.5 text-[10px] text-blue-600 sm:text-xs">Gunakan informasi berikut untuk melakukan pembayaran.</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3.5 px-4 py-4 sm:space-y-4 sm:px-8 sm:py-6">
                        @if($transaction->va_number)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 sm:text-sm">Nomor Virtual Account</p>
                                <div class="mt-1.5 flex items-center justify-between gap-2.5 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 sm:px-4 sm:py-3">
                                    <p id="va-number" class="break-all text-base font-extrabold tracking-wide text-slate-900 sm:text-2xl">{{ $transaction->va_number }}</p>
                                    <button type="button" id="copy-va-button" class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-blue-600 bg-white px-2.5 py-1.5 text-[11px] font-bold text-blue-600 transition hover:bg-blue-50 sm:gap-1.5 sm:px-3 sm:py-2 sm:text-xs">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2M10 20h8a2 2 0 002-2v-8a2 2 0 00-2-2v8a2 2 0 002 2z"/>
                                        </svg>
                                        <span>Salin</span>
                                    </button>
                                </div>
                            </div>
                            @if($transaction->va_bank)
                                <div class="flex items-center justify-between gap-4 text-xs sm:text-sm">
                                    <span class="text-gray-500">Bank</span>
                                    <span class="font-bold text-slate-900">{{ $transaction->va_bank }}</span>
                                </div>
                            @endif
                            @if($transaction->va_expired_at)
                                <div class="flex items-center justify-between gap-4 text-xs sm:text-sm">
                                    <span class="text-gray-500">Batas Pembayaran</span>
                                    <span class="text-right font-bold text-slate-900">{{ $transaction->va_expired_at->copy()->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y, H:i') . ' WITA' }}</span>
                                </div>
                                <div id="payment-countdown" data-expires-at="{{ $transaction->va_expired_at->timestamp * 1000 }}" class="rounded-xl border border-red-200 bg-red-50 p-3 sm:px-4 sm:py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-xs font-semibold text-red-700 sm:text-sm">Waktu pembayaran tersisa</span>
                                        <span id="payment-countdown-timer" class="text-base font-extrabold tabular-nums text-red-700 sm:text-xl">00:00</span>
                                    </div>
                                    <p id="payment-countdown-message" class="mt-0.5 text-[11px] text-red-600 sm:mt-1 sm:text-xs">Segera selesaikan pembayaran Anda.</p>
                                </div>
                            @endif
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-2.5 sm:p-3">
                                <p class="text-[11px] leading-relaxed text-amber-800 sm:text-sm">Pastikan nominal pembayaran sesuai dengan total pesanan Anda.</p>
                            </div>
                        @else
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 sm:p-4">
                                <p class="text-xs font-semibold text-amber-900 sm:text-sm">Nomor Virtual Account belum tersedia</p>
                                <p class="mt-1 text-[11px] leading-relaxed text-amber-800 sm:text-sm sm:leading-5">Pesanan sudah tercatat, tetapi nomor VA belum berhasil dibuat. Silakan tunggu informasi pembayaran berikutnya.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @elseif($transaction->payment_method === 'QRIS')
                <div class="mt-4 overflow-hidden rounded-xl border border-emerald-200 bg-white shadow-sm sm:mt-6 sm:rounded-3xl">
                    <div class="bg-emerald-50 px-4 py-3.5 sm:px-8 sm:py-6">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:h-10 sm:w-10">
                                <x-heroicon-o-qr-code class="h-4 w-4 text-emerald-700 sm:h-5 sm:w-5"/>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-emerald-700 sm:text-sm">Pembayaran QRIS</p>
                                <p class="mt-0.5 text-[10px] text-emerald-600 sm:text-xs">Scan QRIS berikut menggunakan aplikasi pembayaran yang mendukung QRIS.</p>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4 px-4 py-5 sm:space-y-5 sm:px-8 sm:py-7">
                        @if($qrisQrCode)
                            <div class="flex w-full justify-center">
                                <div class="w-full max-w-[320px] rounded-2xl border border-gray-200 bg-white p-3 shadow-sm sm:max-w-[360px] sm:p-4">
                                    <div class="mx-auto flex w-full items-center justify-center [&>svg]:block [&>svg]:h-auto [&>svg]:w-full">
                                        {!! $qrisQrCode !!}
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 sm:text-sm">Total pembayaran</p>
                                <p class="mt-0.5 text-xl font-extrabold text-[#AE7C18] sm:text-2xl">Rp {{ number_format($transaction->total, 0, ',', '.') }}</p>
                            </div>
                            @if($transaction->qris_reference_no)
                                <div class="flex items-center justify-between gap-4 text-xs sm:text-sm">
                                    <span class="text-gray-500">Referensi QRIS</span>
                                    <span class="break-all text-right font-bold text-slate-900">{{ $transaction->qris_reference_no }}</span>
                                </div>
                            @endif
                            @if($transaction->qris_expired_at)
                                <div class="flex items-center justify-between gap-4 text-xs sm:text-sm">
                                    <span class="text-gray-500">Batas Pembayaran</span>
                                    <span class="text-right font-bold text-slate-900">{{ $transaction->qris_expired_at->copy()->setTimezone('Asia/Makassar')->locale('id')->translatedFormat('d M Y, H:i') . ' WITA' }}</span>
                                </div>
                                <div id="qris-payment-countdown" data-expires-at="{{ $transaction->qris_expired_at->timestamp * 1000 }}" class="rounded-xl border border-red-200 bg-red-50 p-3 sm:px-4 sm:py-3">
                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-xs font-semibold text-red-700 sm:text-sm">Waktu pembayaran tersisa</span>
                                        <span id="qris-payment-countdown-timer" class="text-base font-extrabold tabular-nums text-red-700 sm:text-xl">00:00</span>
                                    </div>
                                    <p id="qris-payment-countdown-message" class="mt-0.5 text-[11px] text-red-600 sm:mt-1 sm:text-xs">Segera selesaikan pembayaran Anda.</p>
                                </div>
                            @endif
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 sm:p-4">
                                <p class="text-xs leading-relaxed text-amber-800 sm:text-sm sm:leading-6">Silakan tunggu konfirmasi pembayaran dari sistem.</p>
                            </div>
                        @else
                            <div class="rounded-xl border border-amber-200 bg-amber-50 p-3 sm:p-4">
                                <p class="text-xs font-semibold text-amber-900 sm:text-sm">QRIS belum tersedia</p>
                                <p class="mt-1 text-[11px] leading-relaxed text-amber-800 sm:text-sm sm:leading-5">Pesanan sudah tercatat, tetapi QRIS belum berhasil dibuat. Silakan tunggu informasi pembayaran berikutnya.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3.5 sm:mt-6 sm:rounded-2xl sm:p-5">
                <div class="flex items-start gap-2.5 sm:gap-3">
                    <x-heroicon-o-information-circle class="mt-0.5 h-4 w-4 shrink-0 text-amber-600 sm:h-5 sm:w-5"/>
                    <div>
                        <p class="text-[11px] leading-relaxed text-amber-800 sm:text-sm sm:leading-6">
                            @if($transaction->status === 'PAID')
                                Pembayaran Anda telah berhasil dikonfirmasi.
                            @else
                                Pesanan Anda tercatat dengan status
                                <strong>{{ $transaction->status }}</strong>.
                                @if($transaction->payment_method === 'VA')
                                    Nomor Virtual Account dapat digunakan untuk menyelesaikan pembayaran Anda.
                                @elseif($transaction->payment_method === 'QRIS')
                                    Silakan selesaikan pembayaran menggunakan QRIS di atas.
                                @endif
                            @endif
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 flex flex-col gap-2 sm:mt-8 sm:flex-row sm:justify-center sm:gap-3">
                <a href="{{ route('catalog') }}" class="inline-flex h-10 items-center justify-center rounded-full border border-gray-200 bg-white px-5 text-xs font-semibold text-gray-700 transition hover:border-[#AE7C18] hover:text-[#AE7C18] sm:h-12 sm:px-6 sm:text-sm">Belanja Lagi</a>
                <a href="{{ route('home') }}" class="inline-flex h-10 items-center justify-center rounded-full bg-[#AE7C18] px-5 text-xs font-semibold text-white shadow-md shadow-[#AE7C18]/20 transition hover:bg-[#8F6514] sm:h-12 sm:px-6 sm:text-sm">Kembali ke Beranda</a>
            </div>
            <div id="payment-loading-overlay" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-white/85 px-4 backdrop-blur-md">
                <svg class="h-12 w-12 animate-spin text-[#AE7C18]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-20" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-90" fill="currentColor" d="M12 3a9 9 0 0 1 9 9h-3a6 6 0 0 0-6-6V3Z"></path>
                </svg>
            </div>
            <div id="payment-success-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/70 px-4 backdrop-blur-md">
                <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl sm:rounded-3xl">
                    <div class="px-5 py-6 text-center sm:px-8 sm:py-8">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 sm:h-20 sm:w-20">
                            <x-heroicon-o-check class="h-8 w-8 text-emerald-600 sm:h-10 sm:w-10"/>
                        </div>
                        <p class="mt-5 text-[10px] font-bold uppercase tracking-[0.2em] text-emerald-600 sm:text-xs">PEMBAYARAN BERHASIL</p>
                        <h2 class="mt-1.5 text-xl font-extrabold text-slate-900 sm:text-2xl">Pembayaran Anda Berhasil</h2>
                        <p class="mt-2 text-xs leading-relaxed text-gray-500 sm:text-sm sm:leading-6">Pembayaran telah dikonfirmasi. Gunakan invoice berikut untuk mengecek pesanan Anda.</p>
                        <div class="mt-5 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3.5 text-left sm:mt-6 sm:px-5 sm:py-4">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-400 sm:text-xs">Invoice</span>
                                <span id="payment-success-invoice" class="break-all text-right text-xs font-extrabold text-slate-900 sm:text-sm">{{ $transaction->invoice_number }}</span>
                            </div>
                            <div class="mt-2.5 flex items-start justify-between gap-3">
                                <span class="shrink-0 text-[10px] font-semibold uppercase tracking-wider text-gray-400 sm:text-xs">Email</span>
                                <span id="payment-success-email" class="break-all text-right text-xs font-semibold text-slate-700 sm:text-sm">{{ $transaction->shipping_email }}</span>
                            </div>
                        </div>
                        <div class="mt-5 flex flex-col gap-2.5 sm:mt-6">
                            <a id="payment-success-tracking-button" href="{{ route('orders.tracking', ['invoice_number' => $transaction->invoice_number, 'email' => $transaction->shipping_email]) }}" class="inline-flex h-11 items-center justify-center rounded-full bg-[#AE7C18] px-5 text-xs font-bold text-white shadow-md shadow-[#AE7C18]/20 transition hover:bg-[#8F6514] sm:h-12 sm:text-sm">Cek Pesanan</a>
                            <button type="button" id="payment-success-close" class="inline-flex h-10 items-center justify-center rounded-full border border-gray-200 bg-white px-5 text-xs font-semibold text-gray-600 transition hover:border-gray-300 hover:bg-gray-50 sm:h-11 sm:text-sm">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-ui.container>
</section>
@endsection

<script>
document.addEventListener('DOMContentLoaded', () => {
    const startCountdown = ({ countdownId, timerId, messageId }) => {
        const countdown = document.getElementById(countdownId);
        const timer = document.getElementById(timerId);
        const message = document.getElementById(messageId);
        if (!countdown || !timer || !message) {
            return;
        }
        const expiresAt = Number(countdown.dataset.expiresAt);
        if (!Number.isFinite(expiresAt)) {
            return;
        }
        let interval;
        const updateCountdown = () => {
            const remaining = Math.max(0, expiresAt - Date.now());
            const totalSeconds = Math.floor(remaining / 1000);
            const minutes = Math.floor(totalSeconds / 60);
            const seconds = totalSeconds % 60;
            timer.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            if (remaining <= 0) {
                timer.textContent = '00:00';
                message.textContent = 'Waktu pembayaran telah berakhir.';
                countdown.classList.remove('border-red-200', 'bg-red-50');
                countdown.classList.add('border-gray-200', 'bg-gray-100');
                clearInterval(interval);
            }
        };
        updateCountdown();
        interval = setInterval(updateCountdown, 1000);
    };

    startCountdown({
        countdownId: 'payment-countdown',
        timerId: 'payment-countdown-timer',
        messageId: 'payment-countdown-message',
    });

    startCountdown({
        countdownId: 'qris-payment-countdown',
        timerId: 'qris-payment-countdown-timer',
        messageId: 'qris-payment-countdown-message',
    });

    const copyButton = document.getElementById('copy-va-button');
    const vaNumber = document.getElementById('va-number');

    if (copyButton && vaNumber) {
        copyButton.addEventListener('click', async () => {
            await navigator.clipboard.writeText(vaNumber.innerText.trim());
            copyButton.querySelector('span').textContent = 'Tersalin';
            setTimeout(() => {
                copyButton.querySelector('span').textContent = 'Salin';
            }, 2000);
        });
    }

    const paymentModal = document.getElementById('payment-success-modal');
    const paymentLoadingOverlay = document.getElementById('payment-loading-overlay');
    const closePaymentModal = document.getElementById('payment-success-close');
    const trackingButton = document.getElementById('payment-success-tracking-button');
    const invoice = @json($transaction->invoice_number);
    const email = @json($transaction->shipping_email);
    const paymentMethod = @json($transaction->payment_method);

    const hidePaymentCountdowns = () => {
        const countdowns = [
            document.getElementById('payment-countdown'),
            document.getElementById('qris-payment-countdown'),
        ];
        countdowns.forEach((countdown) => {
            countdown?.classList.add('hidden');
        });
    };

    const paymentStatusUrl = @json(route('checkout.payment-status'));

    if (paymentModal && paymentStatusUrl && ['VA', 'QRIS'].includes(paymentMethod)) {
        const popupStorageKey = `eazywear-payment-success-${invoice}`;

        const showPaymentSuccess = () => {
            hidePaymentCountdowns();
            document.body.classList.add('overflow-hidden');

            paymentLoadingOverlay?.classList.remove('hidden');
            paymentLoadingOverlay?.classList.add('flex');

            setTimeout(() => {
                paymentLoadingOverlay?.classList.add('hidden');
                paymentLoadingOverlay?.classList.remove('flex');

                paymentModal.classList.remove('hidden');
                paymentModal.classList.add('flex');

                if (trackingButton) {
                    trackingButton.href = `{{ route('orders.tracking') }}?invoice_number=${encodeURIComponent(invoice)}&email=${encodeURIComponent(email)}`;
                }
            }, 1500);
        };

        const hidePaymentSuccess = () => {
            paymentModal.classList.add('hidden');
            paymentModal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        };

        closePaymentModal?.addEventListener('click', hidePaymentSuccess);

        paymentModal.addEventListener('click', (event) => {
            if (event.target === paymentModal) {
                hidePaymentSuccess();
            }
        });

        const checkPaymentStatus = async () => {
            try {
                const response = await fetch(paymentStatusUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    return;
                }

                const data = await response.json();

                if (data.success && data.paid === true) {
                    clearInterval(paymentStatusInterval);
                    hidePaymentCountdowns();

                    if (!sessionStorage.getItem(popupStorageKey)) {
                        sessionStorage.setItem(popupStorageKey, 'shown');
                        showPaymentSuccess();
                    }
                }
            } catch (error) {
                console.error('Gagal mengecek status pembayaran.', error);
            }
        };

        const initialPaid = @json(filled($transaction->paid_at));

        if (initialPaid && !sessionStorage.getItem(popupStorageKey)) {
            sessionStorage.setItem(popupStorageKey, 'shown');
            showPaymentSuccess();
        }

        const paymentStatusInterval = setInterval(checkPaymentStatus, 2000);
        checkPaymentStatus();

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                checkPaymentStatus();
            }
        });

        window.addEventListener('focus', checkPaymentStatus);
    }
});
</script>
