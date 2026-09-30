@extends('layouts.website')
@section('title', 'Checkout - Eazywear Indonesia')
@section('content')
<section class="bg-gray-50 py-5 sm:py-10 lg:py-12">
    <x-ui.container>
        <div class="mb-5 sm:mb-7">
            <a href="{{ route('cart.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500 transition hover:text-[#AE7C18] sm:text-sm">
                <x-heroicon-o-arrow-left class="h-4 w-4"/>
                Kembali ke Keranjang
            </a>
            <div class="mt-3 sm:mt-4">
                <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-[#AE7C18] sm:text-xs">CHECKOUT</p>
                <h1 class="mt-1 text-2xl font-bold leading-tight text-slate-900 sm:text-3xl lg:text-4xl">Lengkapi Pesanan Anda</h1>
                <p class="mt-2 max-w-2xl text-xs leading-5 text-gray-500 sm:text-sm sm:leading-6">Masukkan data penerima, pilih metode pengiriman, dan tentukan metode pembayaran.</p>
            </div>
        </div>

        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-600 sm:text-sm">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-600 sm:text-sm">
                <p class="font-semibold">Periksa kembali data checkout Anda.</p>
                <ul class="mt-1.5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.store') }}" id="checkout-form">
            @csrf
            <div class="flex flex-col gap-5 lg:grid lg:grid-cols-3 lg:items-start lg:gap-6">
                <div class="order-1 space-y-4 lg:order-1 lg:col-span-2">
                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-4">
                            <h2 class="text-base font-bold text-slate-900 sm:text-lg">Data Pemesan</h2>
                            <p class="mt-1 text-[11px] leading-4 text-gray-500 sm:text-xs">Gunakan email dan nomor WhatsApp yang aktif.</p>
                        </div>
                        <div class="grid gap-3.5 sm:grid-cols-2 sm:gap-4">
                            <div class="sm:col-span-2">
                                <label for="name" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" placeholder="Masukkan nama lengkap" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                            </div>
                            <div>
                                <label for="email" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Email <span class="text-red-500">*</span></label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="nama@gmail.com" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                            </div>
                            <div>
                                <label for="phone" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Nomor WhatsApp <span class="text-red-500">*</span></label>
                                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required autocomplete="tel" placeholder="08xxxxxxxxxx" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-4">
                            <h2 class="text-base font-bold text-slate-900 sm:text-lg">Alamat Pengiriman</h2>
                            <p class="mt-1 text-[11px] leading-4 text-gray-500 sm:text-xs">Pastikan alamat pengiriman ditulis dengan lengkap dan benar.</p>
                        </div>
                        <div class="space-y-3.5">
                            <div>
                                <label for="shipping_address" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Alamat Lengkap <span class="text-red-500">*</span></label>
                                <textarea id="shipping_address" name="shipping_address" rows="2" required autocomplete="street-address" placeholder="Nama jalan, nomor rumah, RT/RW, patokan, dan detail lainnya" class="w-full resize-none rounded-lg border border-gray-200 bg-gray-50 px-3 py-2.5 text-xs leading-5 text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:rounded-xl sm:px-4 sm:py-3 sm:text-sm sm:leading-6">{{ old('shipping_address') }}</textarea>
                            </div>

                            <div>
                                <div class="mb-1.5 flex items-center justify-between gap-3">
                                    <label class="block text-xs font-semibold text-slate-700 sm:text-sm" for="shipping-map">Titik Lokasi Penerima</label>
                                    <button type="button" id="use-my-location" class="text-[10px] font-semibold text-[#AE7C18] transition hover:underline disabled:cursor-not-allowed disabled:opacity-60 sm:text-xs">Gunakan Lokasi Saya</button>
                                </div>
                                <div id="shipping-map" class="w-full overflow-hidden rounded-xl border border-gray-200"></div>
                                <p class="mt-1.5 text-[10px] leading-4 text-gray-500 sm:text-xs">Geser pin pada peta untuk menentukan titik lokasi penerima dengan lebih tepat.</p>
                                <input type="hidden" id="shipping_latitude" name="shipping_latitude" value="{{ old('shipping_latitude') }}">
                                <input type="hidden" id="shipping_longitude" name="shipping_longitude" value="{{ old('shipping_longitude') }}">
                            </div>

                            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <label for="shipping_district" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Kecamatan <span class="text-red-500">*</span></label>
                                    <input id="shipping_district" name="shipping_district" type="text" value="{{ old('shipping_district') }}" required placeholder="Kecamatan" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                                </div>
                                <div>
                                    <label for="shipping_city" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Kota / Kab. <span class="text-red-500">*</span></label>
                                    <input id="shipping_city" name="shipping_city" type="text" value="{{ old('shipping_city') }}" required placeholder="Kota / Kabupaten" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                                </div>
                                <div>
                                    <label for="shipping_province" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Provinsi <span class="text-red-500">*</span></label>
                                    <input id="shipping_province" name="shipping_province" type="text" value="{{ old('shipping_province') }}" required placeholder="Provinsi" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                                </div>
                                <div>
                                    <label for="shipping_postal_code" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">Kode Pos <span class="text-red-500">*</span></label>
                                    <input id="shipping_postal_code" name="shipping_postal_code" type="text" value="{{ old('shipping_postal_code') }}" required inputmode="numeric" autocomplete="postal-code" placeholder="70654" class="h-10 w-full rounded-lg border border-gray-200 bg-gray-50 px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:bg-white focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                        <div class="mb-4">
                            <h2 class="text-base font-bold text-slate-900 sm:text-lg">Pengiriman</h2>
                        </div>
                        <div class="space-y-2.5">
                            @foreach($shippingMethods as $method)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 bg-white px-3.5 py-3 transition sm:gap-4 sm:px-4 has-[:checked]:border-[#AE7C18] has-[:checked]:bg-[#AE7C18]/5">
                                    <input
                                        type="radio"
                                        name="shipping_method"
                                        value="{{ $method['value'] }}"
                                        class="h-4 w-4 accent-[#AE7C18]"
                                        @checked(old('shipping_method') === $method['value'])
                                    >
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-semibold text-slate-900 sm:text-sm">{{ $method['name'] }}</p>
                                        <p class="mt-0.5 text-[10px] leading-4 text-gray-500 sm:text-xs">{{ $method['description'] }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div id="pickup-section" class="mt-4 hidden">
                            <div class="rounded-xl border border-[#AE7C18]/20 bg-[#AE7C18]/5 p-3.5 sm:p-4">
                                <div class="mb-3">
                                    <p class="text-xs font-semibold text-slate-900 sm:text-sm">
                                        Jadwal Pengambilan
                                    </p>
                                    <p class="mt-0.5 text-[10px] leading-4 text-gray-500 sm:text-xs">
                                        Tentukan tanggal dan waktu untuk mengambil pesanan Anda.
                                    </p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-3 sm:gap-4">
                                    <div>
                                        <label for="pickup_date" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">
                                            Tanggal <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            id="pickup_date"
                                            name="pickup_date"
                                            type="date"
                                            value="{{ old('pickup_date') }}"
                                            class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm"
                                        >
                                    </div>

                                    <div>
                                        <label for="pickup_time_start" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">
                                            Mulai <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            id="pickup_time_start"
                                            name="pickup_time_start"
                                            type="time"
                                            value="{{ old('pickup_time_start') }}"
                                            class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm"
                                        >
                                    </div>

                                    <div>
                                        <label for="pickup_time_end" class="mb-1.5 block text-xs font-semibold text-slate-700 sm:text-sm">
                                            Selesai <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            id="pickup_time_end"
                                            name="pickup_time_end"
                                            type="time"
                                            value="{{ old('pickup_time_end') }}"
                                            class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-xs text-slate-800 outline-none transition focus:border-[#AE7C18] focus:ring-4 focus:ring-[#AE7C18]/10 sm:h-11 sm:rounded-xl sm:px-4 sm:text-sm"
                                        >
                                    </div>
                                </div>

                                <p id="pickup-validation-error"
                                class="mt-2 hidden rounded-lg bg-red-50 px-3 py-2 text-[10px] leading-4 text-red-600 sm:text-xs">
                                </p>
                            </div>
                        </div>

                        <div id="shipping-rates-section" class="mt-4 hidden">
                            <div class="mb-2.5 flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-slate-900 sm:text-sm">Pilih Layanan Pengiriman</p>
                                    <p class="mt-0.5 text-[10px] leading-4 text-gray-500 sm:text-xs">Biaya dan estimasi berdasarkan alamat tujuan.</p>
                                </div>
                                <span id="shipping-rates-status" class="text-[10px] text-gray-400 sm:text-xs"></span>
                            </div>
                            <div id="shipping-rates-list" class="space-y-2.5"></div>
                            <p id="shipping-rates-error" class="mt-2 hidden rounded-lg bg-red-50 px-3 py-2 text-[10px] leading-4 text-red-600 sm:text-xs"></p>
                        </div>

                        <input type="hidden" name="courier_code" id="courier_code" value="{{ old('courier_code') }}">
                        <input type="hidden" name="courier_service_code" id="courier_service_code" value="{{ old('courier_service_code') }}">

                        <div class="mt-3 rounded-lg bg-gray-50 px-3 py-2.5">
                            <p id="shipping-rates-hint" class="text-[10px] leading-4 text-gray-500 sm:text-xs sm:leading-5">Masukkan kode pos tujuan untuk melihat pilihan layanan pengiriman.</p>
                        </div>
                    </div>
                </div>

                <div class="contents lg:flex lg:flex-col lg:gap-5 lg:order-2 lg:col-span-1">
                    <div class="order-2 lg:order-none">
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-semibold uppercase tracking-wider text-[#AE7C18] sm:text-xs">ORDER</p>
                                    <h2 class="mt-0.5 text-base font-bold text-slate-900 sm:text-lg">Ringkasan</h2>
                                </div>
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-[10px] font-semibold text-gray-600 sm:text-xs">{{ $totalItems }} item</span>
                            </div>
                            <div class="mt-4 space-y-3">
                                @foreach($cart as $item)
                                    <div class="flex gap-3">
                                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-gray-100 sm:h-14 sm:w-14">
                                            <img src="{{ $item['image'] }}" alt="{{ $item['product_name'] }}" class="h-full w-full object-cover">
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="line-clamp-2 text-[11px] font-semibold leading-4 text-slate-900 sm:text-xs">{{ $item['product_name'] }}</p>
                                            <div class="mt-0.5 flex flex-wrap gap-x-2 text-[10px] text-gray-500 sm:text-[11px]">
                                                <span>Size: {{ $item['size_name'] }}</span>
                                                <span>× {{ $item['qty'] }}</span>
                                            </div>
                                            @if(!empty($item['custom_name']))
                                                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700 sm:text-[11px]">Nama Jersey: {{ $item['custom_name'] }}</p>
                                            @endif
                                            @if(!empty($item['custom_number']))
                                                <p class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-700 sm:text-[11px]">Nomor Punggung: {{ $item['custom_number'] }}</p>
                                            @endif
                                            <p class="mt-0.5 text-xs font-semibold text-[#AE7C18] sm:text-sm">Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="my-4 border-t border-gray-100"></div>
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between text-xs text-gray-600 sm:text-sm">
                                    <span>Subtotal</span>
                                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between text-xs text-gray-600 sm:text-sm">
                                    <span>Pengiriman</span>
                                    <span id="shipping-cost" class="text-[10px] text-gray-400 sm:text-xs">Akan dihitung</span>
                                </div>
                            </div>

                            <div class="my-4 border-t border-gray-100"></div>
                            <div class="flex items-end justify-between gap-3">
                                <div>
                                    <p class="text-xs font-semibold text-slate-900 sm:text-sm">Total</p>
                                    <p id="total-note" class="mt-0.5 text-[10px] text-gray-400 sm:text-xs">Belum termasuk ongkir</p>
                                </div>
                                <p id="total-amount" class="text-xl font-bold text-[#AE7C18] sm:text-2xl">Rp {{ number_format($subtotal, 0, ',', '.') }}</p>
                            </div>

                            <a href="{{ route('cart.index') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-full border border-gray-200 px-4 py-2.5 text-xs font-semibold text-gray-600 transition hover:border-[#AE7C18] hover:text-[#AE7C18]">Ubah Keranjang</a>
                        </div>
                    </div>

                    <div class="order-3 space-y-4 lg:order-none">
                        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                            <div class="mb-4">
                                <h2 class="text-base font-bold text-slate-900 sm:text-lg">Pembayaran</h2>
                                <p class="mt-1 text-[11px] text-gray-500 sm:text-xs">Pilih metode pembayaran yang akan digunakan.</p>
                            </div>
                            <div class="space-y-2.5">
                                @foreach($paymentMethods as $method)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-3.5 py-3 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5 has-[:checked]:border-[#AE7C18] has-[:checked]:bg-[#AE7C18]/5 sm:gap-4 sm:px-4">
                                        <input type="radio" name="payment_method" value="{{ $method['value'] }}" @checked($loop->first) class="h-4 w-4 accent-[#AE7C18]">
                                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#AE7C18]/10">
                                            @if($method['value'] === 'QRIS')
                                                <x-heroicon-o-qr-code class="h-4 w-4 text-[#AE7C18]"/>
                                            @else
                                                <x-heroicon-o-building-library class="h-4 w-4 text-[#AE7C18]"/>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-semibold text-slate-900 sm:text-sm">{{ $method['name'] }}</p>
                                            <p class="mt-0.5 text-[10px] leading-4 text-gray-500 sm:text-xs">{{ $method['description'] }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" id="checkout-submit" class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-full bg-[#AE7C18] px-6 text-xs font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#8F6514] active:scale-[0.99] sm:h-12 sm:text-sm">
                            Buat Pesanan
                            <x-heroicon-o-arrow-right class="h-4 w-4 sm:h-5 sm:w-5"/>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </x-ui.container>
</section>
@endsection

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
    #shipping-map {
        height: 360px;
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        z-index: 1;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mapElement = document.getElementById('shipping-map');
        if (!mapElement || typeof L === 'undefined') {
            return;
        }

        const latitudeInput = document.getElementById('shipping_latitude');
        const longitudeInput = document.getElementById('shipping_longitude');
        const locationButton = document.getElementById('use-my-location');

        if (!latitudeInput || !longitudeInput) {
            return;
        }

        const defaultLatitude = -3.3194;
        const defaultLongitude = 114.5908;
        const oldLatitude = parseFloat(latitudeInput.value);
        const oldLongitude = parseFloat(longitudeInput.value);
        const hasOldLocation = Number.isFinite(oldLatitude) && Number.isFinite(oldLongitude);
        const initialLatitude = hasOldLocation ? oldLatitude : defaultLatitude;
        const initialLongitude = hasOldLocation ? oldLongitude : defaultLongitude;

        const map = L.map(mapElement).setView([initialLatitude, initialLongitude], hasOldLocation ? 16 : 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const marker = L.marker([initialLatitude, initialLongitude], {
            draggable: true
        }).addTo(map);

        function setLocation(latitude, longitude) {
            latitudeInput.value = Number(latitude).toFixed(7);
            longitudeInput.value = Number(longitude).toFixed(7);
            marker.setLatLng([latitude, longitude]);
            map.setView([latitude, longitude], 16);
        }

        marker.on('dragend', function () {
            const position = marker.getLatLng();
            setLocation(position.lat, position.lng);
        });

        if (hasOldLocation) {
            setLocation(oldLatitude, oldLongitude);
        }

        if (locationButton) {
            locationButton.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung lokasi perangkat.');
                    return;
                }

                locationButton.disabled = true;
                locationButton.textContent = 'Mencari lokasi...';

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        setLocation(position.coords.latitude, position.coords.longitude);
                        locationButton.disabled = false;
                        locationButton.textContent = 'Gunakan Lokasi Saya';
                    },
                    function () {
                        alert('Lokasi tidak dapat diakses. Pastikan izin lokasi browser telah diberikan.');
                        locationButton.disabled = false;
                        locationButton.textContent = 'Gunakan Lokasi Saya';
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }

        setTimeout(function () {
            map.invalidateSize();
        }, 200);
    });

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('checkout-form');
        const postalInput = document.getElementById('shipping_postal_code');
        const latitudeInput = document.getElementById('shipping_latitude');
        const longitudeInput = document.getElementById('shipping_longitude');
        const ratesSection = document.getElementById('shipping-rates-section');
        const ratesList = document.getElementById('shipping-rates-list');
        const ratesStatus = document.getElementById('shipping-rates-status');
        const ratesError = document.getElementById('shipping-rates-error');
        const ratesHint = document.getElementById('shipping-rates-hint');
        const pickupSection = document.getElementById('pickup-section');
        const pickupDateInput = document.getElementById('pickup_date');
        const pickupTimeStartInput = document.getElementById('pickup_time_start');
        const pickupTimeEndInput = document.getElementById('pickup_time_end');
        const pickupValidationError = document.getElementById('pickup-validation-error');
        const shippingCost = document.getElementById('shipping-cost');
        const totalAmount = document.getElementById('total-amount');
        const totalNote = document.getElementById('total-note');
        const courierCodeInput = document.getElementById('courier_code');
        const courierServiceCodeInput = document.getElementById('courier_service_code');

        if (
            !form ||
            !postalInput ||
            !ratesSection ||
            !ratesList ||
            !shippingCost ||
            !totalAmount ||
            !totalNote ||
            !courierCodeInput ||
            !courierServiceCodeInput ||
            !pickupSection ||
            !pickupDateInput ||
            !pickupTimeStartInput ||
            !pickupTimeEndInput
        ) {
            return;
        }

        const subtotal = {{ json_encode((float) $subtotal) }};
        let debounceTimer = null;
        let requestSequence = 0;
        let selectedRate = null;

        function formatRupiah(value) {
            return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
        }

        function resetShippingSelection() {
            selectedRate = null;
            courierCodeInput.value = '';
            courierServiceCodeInput.value = '';
            shippingCost.textContent = 'Akan dihitung';
            shippingCost.className = 'text-[10px] text-gray-400 sm:text-xs';
            totalAmount.textContent = formatRupiah(subtotal);
            totalNote.textContent = 'Belum termasuk ongkir';
        }


        function showRatesError(message) {
            ratesError.textContent = message;
            ratesError.classList.remove('hidden');
            ratesStatus.textContent = '';
        }

        function hideRatesError() {
            ratesError.textContent = '';
            ratesError.classList.add('hidden');
        }

        function selectRate(rate, labelElement) {
            selectedRate = rate;
            courierCodeInput.value = rate.courier_code || '';
            courierServiceCodeInput.value = rate.service_code || '';
            shippingCost.textContent = formatRupiah(rate.price);
            shippingCost.className = 'text-[10px] font-semibold text-[#AE7C18] sm:text-xs';
            totalAmount.textContent = formatRupiah(subtotal + Number(rate.price || 0));
            totalNote.textContent = 'Termasuk ongkir';

            ratesList.querySelectorAll('[data-shipping-rate]').forEach(function (element) {
                element.classList.remove('border-[#AE7C18]', 'bg-[#AE7C18]/5');
                element.classList.add('border-gray-200');
            });

            labelElement.classList.remove('border-gray-200');
            labelElement.classList.add('border-[#AE7C18]', 'bg-[#AE7C18]/5');
        }

        function updateShippingMethodUI() {
            const shippingMethod = form.querySelector(
                'input[name="shipping_method"]:checked'
            )?.value;

            const isPickup = shippingMethod === 'Ambil di Tempat';

            pickupSection.classList.toggle('hidden', !isPickup);

            if (isPickup) {
                requestSequence++;

                ratesSection.classList.add('hidden');
                ratesList.innerHTML = '';
                hideRatesError();
                ratesStatus.textContent = '';

                courierCodeInput.value = '';
                courierServiceCodeInput.value = '';
                selectedRate = null;

                shippingCost.textContent = 'Rp 0';
                totalAmount.textContent = formatRupiah(subtotal);
                totalNote.textContent = 'Pengambilan di tempat';
                ratesHint.textContent =
                    'Pesanan akan diambil langsung di lokasi pickup.';
            } else {
                shippingCost.textContent = 'Akan dihitung';
                totalAmount.textContent = formatRupiah(subtotal);
                totalNote.textContent = 'Belum termasuk ongkir';

                ratesHint.textContent =
                    'Masukkan kode pos tujuan untuk melihat pilihan layanan pengiriman.';

                if (/^\d{5,10}$/.test(postalInput.value.trim())) {
                    loadRates();
                }
            }
        }

        function renderRates(rates) {
            ratesList.innerHTML = '';
            resetShippingSelection();

            if (!Array.isArray(rates) || rates.length === 0) {
                ratesSection.classList.remove('hidden');
                ratesStatus.textContent = '';
                showRatesError('Belum ada layanan pengiriman yang tersedia untuk alamat tersebut.');
                ratesHint.textContent = 'Coba periksa kembali kode pos atau titik lokasi penerima.';
                return;
            }

            ratesSection.classList.remove('hidden');
            hideRatesError();
            ratesStatus.textContent = rates.length + ' layanan tersedia';
            ratesHint.textContent = 'Pilih salah satu layanan pengiriman yang tersedia.';

            rates.forEach(function (rate) {
                const label = document.createElement('label');
                label.setAttribute('data-shipping-rate', 'true');
                label.className = 'flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-3.5 py-3 transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5 sm:gap-4 sm:px-4';

                const radio = document.createElement('input');
                radio.type = 'radio';
                radio.name = 'shipping_rate_selection';
                radio.value = (rate.courier_code || '') + ':' + (rate.service_code || '');
                radio.className = 'h-4 w-4 shrink-0 accent-[#AE7C18]';

                const content = document.createElement('div');
                content.className = 'min-w-0 flex-1';

                const topRow = document.createElement('div');
                topRow.className = 'flex items-start justify-between gap-3';

                const serviceWrapper = document.createElement('div');
                serviceWrapper.className = 'flex min-w-0 items-center gap-3';

                const logoWrapper = document.createElement('div');
                logoWrapper.className = 'flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-100 bg-white';

                const logo = document.createElement('img');
                logo.className = 'h-full w-full object-contain p-1.5';
                logo.alt = rate.courier_name || rate.courier_code || 'Kurir';

                const courierLogos = {
                    jnt: '{{ asset('images/shipping/jnt.png') }}',
                    lion: '{{ asset('images/shipping/lion-parcel.png') }}'
                };

                if (courierLogos[rate.courier_code]) {
                    logo.src = courierLogos[rate.courier_code];

                    logo.onerror = function () {
                        logoWrapper.innerHTML = '';

                        const fallback = document.createElement('span');
                        fallback.className = 'text-[10px] font-bold text-gray-500';
                        fallback.textContent = (rate.courier_name || rate.courier_code || 'Kurir')
                            .substring(0, 3)
                            .toUpperCase();

                        logoWrapper.appendChild(fallback);
                    };

                    logoWrapper.appendChild(logo);
                } else {
                    const fallback = document.createElement('span');
                    fallback.className = 'text-[10px] font-bold text-gray-500';
                    fallback.textContent = (rate.courier_name || rate.courier_code || 'Kurir')
                        .substring(0, 3)
                        .toUpperCase();

                    logoWrapper.appendChild(fallback);
                }

                const serviceContent = document.createElement('div');
                serviceContent.className = 'min-w-0';

                const courierName = document.createElement('p');
                courierName.className = 'text-xs font-semibold text-slate-900 sm:text-sm';
                courierName.textContent = rate.courier_name || rate.courier_code || 'Kurir';

                const serviceName = document.createElement('p');
                serviceName.className = 'mt-0.5 text-[10px] font-medium text-gray-500 sm:text-xs';
                serviceName.textContent = rate.service_name || rate.service_code || 'Layanan';

                serviceContent.appendChild(courierName);
                serviceContent.appendChild(serviceName);

                serviceWrapper.appendChild(logoWrapper);
                serviceWrapper.appendChild(serviceContent);

                const price = document.createElement('p');
                price.className = 'shrink-0 text-xs font-bold text-[#AE7C18] sm:text-sm';
                price.textContent = formatRupiah(rate.price);

                topRow.appendChild(serviceWrapper);
                topRow.appendChild(price);

                const bottomRow = document.createElement('div');
                bottomRow.className = 'mt-1.5 flex flex-wrap gap-x-3 text-[10px] text-gray-400 sm:text-xs';

                if (rate.duration) {
                    const duration = document.createElement('span');
                    duration.textContent = 'Estimasi ' + rate.duration;
                    bottomRow.appendChild(duration);
                }

                if (rate.service_type) {
                    const serviceType = document.createElement('span');
                    serviceType.textContent = rate.service_type;
                    bottomRow.appendChild(serviceType);
                }

                content.appendChild(topRow);
                content.appendChild(bottomRow);
                label.appendChild(radio);
                label.appendChild(content);

                radio.addEventListener('change', function () {
                    if (radio.checked) {
                        selectRate(rate, label);
                    }
                });

                ratesList.appendChild(label);
            });

            const oldCourierCode = courierCodeInput.value;
            const oldServiceCode = courierServiceCodeInput.value;

            if (oldCourierCode && oldServiceCode) {
                const restoredRate = rates.find(function (rate) {
                    return rate.courier_code === oldCourierCode && rate.service_code === oldServiceCode;
                });

                if (restoredRate) {
                    const radios = ratesList.querySelectorAll('input[name="shipping_rate_selection"]');

                    rates.forEach(function (rate, index) {
                        if (rate.courier_code === oldCourierCode && rate.service_code === oldServiceCode && radios[index]) {
                            radios[index].checked = true;
                            selectRate(restoredRate, radios[index].closest('label'));
                        }
                    });
                }
            }
        }

        async function loadRates() {
            const shippingMethod = form.querySelector(
                'input[name="shipping_method"]:checked'
            )?.value;

            if (shippingMethod !== 'Kurir') {
                return;
            }
            const postalCode = postalInput.value.trim();

            if (!/^\d{5,10}$/.test(postalCode)) {
                ratesSection.classList.add('hidden');
                ratesList.innerHTML = '';
                hideRatesError();
                ratesStatus.textContent = '';
                ratesHint.textContent = 'Masukkan kode pos tujuan untuk melihat pilihan layanan pengiriman.';
                resetShippingSelection();
                return;
            }

            const currentRequest = ++requestSequence;
            ratesSection.classList.remove('hidden');
            ratesList.innerHTML = '';
            hideRatesError();
            ratesStatus.textContent = 'Menghitung...';
            ratesHint.textContent = 'Sedang mengambil pilihan layanan pengiriman.';
            resetShippingSelection();

            try {
                const response = await fetch('{{ route('checkout.shipping-rates') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        shipping_postal_code: postalCode,
                        shipping_latitude: latitudeInput?.value || null,
                        shipping_longitude: longitudeInput?.value || null
                    })
                });

                const data = await response.json();

                if (currentRequest !== requestSequence) {
                    return;
                }

                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Gagal mengambil pilihan pengiriman.');
                }

                renderRates(data.data || []);
            } catch (error) {
                if (currentRequest !== requestSequence) {
                    return;
                }

                ratesSection.classList.remove('hidden');
                ratesList.innerHTML = '';
                resetShippingSelection();
                ratesStatus.textContent = '';

                showRatesError(error.message || 'Gagal mengambil pilihan pengiriman. Silakan coba lagi.');
                ratesHint.textContent = 'Periksa kembali kode pos dan alamat tujuan.';
            }
        }

        form.querySelectorAll('input[name="shipping_method"]').forEach(function (input) {
            input.addEventListener('change', function () {
                updateShippingMethodUI();
            });
        });

        postalInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(loadRates, 700);
        });

        postalInput.addEventListener('change', function () {
            clearTimeout(debounceTimer);
            loadRates();
        });

        if (latitudeInput) {
            latitudeInput.addEventListener('change', function () {
            });
        }

        if (longitudeInput) {
            longitudeInput.addEventListener('change', function () {
            });
        }

        form.addEventListener('submit', function (event) {
            const shippingMethod = form.querySelector(
                'input[name="shipping_method"]:checked'
            )?.value;

            if (
                shippingMethod === 'Kurir' &&
                (!courierCodeInput.value || !courierServiceCodeInput.value)
            ) {
                event.preventDefault();

                ratesSection.classList.remove('hidden');

                showRatesError('Silakan pilih layanan pengiriman terlebih dahulu.');
                ratesHint.textContent = 'Pilih salah satu layanan pengiriman sebelum membuat pesanan.';

                ratesSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                return;
            }

            if (shippingMethod === 'Ambil di Tempat') {
                const missingPickupField =
                    !pickupDateInput.value ||
                    !pickupTimeStartInput.value ||
                    !pickupTimeEndInput.value;

                if (missingPickupField) {
                    event.preventDefault();

                    pickupValidationError.textContent =
                        'Silakan lengkapi tanggal dan waktu pengambilan terlebih dahulu.';

                    pickupValidationError.classList.remove('hidden');

                    pickupSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });

                    return;
                }

                pickupValidationError.textContent = '';
                pickupValidationError.classList.add('hidden');

                courierCodeInput.value = '';
                courierServiceCodeInput.value = '';
            }
        });

        updateShippingMethodUI();
    });
</script>
@endpush