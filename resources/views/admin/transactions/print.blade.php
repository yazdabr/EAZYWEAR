<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $transaction->invoice_number }}</title>
    @vite(['resources/css/app.css'])

    <style>
        @media print {
            @page {
                margin: 0;
                size: 21.59cm 13.97cm; /* Kustom ukuran baru 21.59 cm x 13.97 cm */
            }

            html, body {
                width: 21.59cm;
                height: 13.97cm;
                margin: 0 !important;
                padding: 0 !important;
                background-color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                overflow: hidden; /* Memastikan tidak ada overflow ke page 2 */
            }

            .no-print {
                display: none !important;
            }

            .print-container {
                box-shadow: none !important;
                border: none !important;
                width: 21.59cm !important;
                height: 13.97cm !important;
                max-width: 21.59cm !important;
                padding: 6mm 8mm !important;
                margin: 0 !important;
                border-radius: 0 !important;
                box-sizing: border-box;
            }
        }
        /* PURE BLACK INVOICE PREVIEW */
        .print-container,
        .print-container * {
            color: #000000 !important;
            border-color: #000000 !important;
            text-shadow: none !important;
            box-shadow: none !important;
        }

        .print-container,
        .print-container * {
            background-color: #ffffff !important;
            background-image: none !important;
        }

        .print-container > div.absolute {
            background: #000000 !important;
        }

        .print-container img {
            filter: none !important;
        }
        /* PURE BLACK PRINT */
        @media print {
            .print-container,
            .print-container * {
                color: #000000 !important;
                border-color: #000000 !important;
                text-shadow: none !important;
                box-shadow: none !important;
            }

            .print-container,
            .print-container * {
                background-color: #ffffff !important;
                background-image: none !important;
            }

            html,
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }

            .print-container > div.absolute {
                display: none !important;
            }

            .print-container img {
                filter: none !important;
            }
        }
    </style>
</head>

<body class="bg-slate-100 font-sans antialiased min-h-screen py-6 px-4">

    {{-- Floating Print Bar (Hidden on Print) --}}
    <div class="no-print mx-auto max-w-5xl mb-4 flex flex-wrap items-center justify-between gap-4 bg-white px-6 py-3 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-3">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-800">Pratinjau Invoice (21.59 x 13.97 cm)</p>
                <p class="text-xs text-slate-500">Didesain khusus muat 1 halaman cetak</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button 
                onclick="window.print()" 
                class="inline-flex items-center gap-2 rounded-xl bg-[#AE7C18] px-5 py-2 text-sm font-semibold text-white shadow-md shadow-[#AE7C18]/20 transition-all hover:bg-[#96690F] active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Invoice
            </button>
            <a
                href="{{ route('admin.transactions.excel', $transaction->invoice_number) }}"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-700 px-5 py-2 text-sm font-semibold text-white shadow-md transition-all hover:bg-emerald-800 active:scale-95"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v3h16v-3"></path>
                </svg>
                Download Excel
            </a>
        </div>
    </div>

    {{-- Main Invoice Card (Ukuran disesuaikan ke max-w-[21.59cm] dan min-h-[13.97cm]) --}}
    <div class="print-container relative overflow-hidden mx-auto max-w-[21.59cm] min-h-[13.97cm] bg-white p-5 rounded-2xl shadow-xl border border-slate-200/80 flex flex-col justify-between">
        
        {{-- Top Accent Bar --}}
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#AE7C18] via-[#D4A038] to-[#AE7C18]"></div>

        <div>
            {{-- Header Section --}}
            <div class="flex justify-between items-start pb-2 border-b border-slate-300">
                <div class="flex items-baseline gap-2">
                    <h1 class="text-xl font-black tracking-tight text-slate-900">INVOICE</h1>
                    <span class="text-xs font-bold text-[#AE7C18] tracking-wider uppercase">
                        #{{ $transaction->invoice_number }}
                    </span>
                </div>

                <div class="text-right flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-900 tracking-tight">Eazywear</span>
                    <span class="text-[10px] text-slate-700 font-medium">| +62 857 5443 1105</span>
                </div>
            </div>

            {{-- Info Meta & Pelanggan --}}
            <div class="mt-2 grid grid-cols-12 gap-2 text-xs">
                {{-- Ditagihkan Kepada --}}
                <div class="col-span-7 bg-slate-50/80 rounded-xl p-2 border border-slate-200 flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-0.5">
                            <span class="text-[9.5px] font-bold uppercase tracking-wider text-slate-700">Ditagihkan Kepada:</span>
                            <span class="text-[9.5px] font-bold text-slate-800">Pengiriman: {{ $transaction->shipping_method ?? '-' }}</span>
                        </div>
                        <p class="font-bold text-slate-900 leading-tight">
                            {{ $transaction->shipping_name ?? $transaction->customer?->name ?? '-' }}
                            <span class="font-medium text-slate-700">({{ $transaction->shipping_phone ?? $transaction->customer?->phone ?? '-' }})</span>
                        </p>
                        <p class="text-[9.5px] text-slate-800 leading-snug mt-0.5 whitespace-normal break-words">
                            {{ $transaction->shipping_address ?? '-' }}, 
                            {{ collect([
                                $transaction->shipping_district,
                                $transaction->shipping_city,
                                $transaction->shipping_province,
                                $transaction->shipping_postal_code
                            ])->filter()->implode(', ') }}
                        </p>
                    </div>

                    @if(
                        $transaction->shipping_method === 'Ambil di Tempat' &&
                        $transaction->pickup_date &&
                        $transaction->pickup_time_start &&
                        $transaction->pickup_time_end
                    )
                        <div class="mt-1 flex justify-between items-center text-[9.5px] pt-1 border-t border-slate-200">
                            <span class="font-bold text-slate-900">Jadwal Ambil:</span>
                            <span class="text-slate-900 font-semibold">
                                {{ \Carbon\Carbon::parse($transaction->pickup_date)->locale('id')->translatedFormat('D, d M Y') }}
                                ({{ \Carbon\Carbon::parse($transaction->pickup_time_start)->format('H:i') }}–{{ \Carbon\Carbon::parse($transaction->pickup_time_end)->format('H:i') }} WITA)
                            </span>
                        </div>
                    @endif
                </div>

                {{-- Detail Transaksi --}}
                <div class="col-span-5 bg-slate-50/80 rounded-xl p-2 border border-slate-200 flex flex-col justify-between text-[10.5px]">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-700 font-medium">Tgl Invoice:</span>
                        <span class="font-bold text-slate-900">
                            {{ $transaction->transaction_date?->copy()->setTimezone('Asia/Makassar')->format('d/m/Y H:i') ?? '-' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center mt-0.5">
                        <span class="text-slate-700 font-medium">No. Transaksi:</span>
                        <span class="font-bold text-slate-900">{{ $transaction->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between items-center mt-0.5">
                        <span class="text-slate-700 font-medium">Pembayaran:</span>
                        <span class="font-bold text-slate-900">{{ $transaction->payment_method ?? '-' }}</span>
                    </div>
                </div>
            </div>

            {{-- Tabel Produk --}}
            <div class="mt-2 overflow-hidden rounded-xl border border-slate-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-300 text-[9.5px] font-bold uppercase tracking-wider text-slate-800 bg-slate-100">
                            <th class="py-1 px-2">Detail Produk</th>
                            <th class="py-1 px-2 text-center">Ukuran</th>
                            <th class="py-1 px-2 text-center">Qty</th>
                            <th class="py-1 px-2 text-right">Harga Satuan</th>
                            <th class="py-1 px-2 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 text-xs">
                        @forelse($transaction->items as $item)
                            @php
                                $variant = $item->productVariant;
                                $product = $variant?->product;
                            @endphp
                            <tr>
                                <td class="px-2 py-1">
                                    <p class="font-bold text-slate-900 leading-tight">
                                        {{ $product?->name ?? '-' }}
                                    </p>
                                    <div class="flex flex-wrap gap-x-2 text-[9.5px] text-slate-700 font-medium">
                                        @if($item->custom_name)
                                            <span class="text-slate-900 font-semibold">Nama: {{ mb_strtoupper($item->custom_name, 'UTF-8') }}</span>
                                        @endif
                                        @if($item->custom_number)
                                            <span class="text-slate-900 font-semibold">No: {{ $item->custom_number }}</span>
                                        @endif
                                        @if($item->is_longsleeve)
                                            <span class="text-slate-900 font-semibold">Longsleeve (+{{ number_format((int) $item->longsleeve_price, 0, ',', '.') }})</span>
                                        @endif
                                        @if($item->is_patch)
                                            <span class="text-slate-900 font-semibold">Patch (+{{ number_format((int) $item->patch_price, 0, ',', '.') }})</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-2 py-1 text-center">
                                    <span class="inline-block rounded bg-slate-200 px-1.5 py-0.5 text-[9.5px] font-bold text-slate-900">
                                        {{ $variant?->size?->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-2 py-1 text-center font-bold text-slate-900">
                                    {{ $item->qty }}
                                </td>
                                <td class="px-2 py-1 text-right text-slate-900 font-medium">
                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                </td>
                                <td class="px-2 py-1 text-right font-bold text-slate-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-3 text-center text-slate-500 text-xs">
                                    Tidak ada produk dalam transaksi.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Footer & Total Section --}}
        <div class="mt-2 pt-1.5 border-t border-slate-300 flex justify-between items-end gap-4">
            {{-- Syarat, Catatan, & Logo Eazywear --}}
            <div class="w-7/12 flex flex-col justify-end space-y-0.5">
                {{-- Logo Eazywear --}}
                <img src="{{ asset('images/shipping/logoeazywear.svg') }}?v=20261016" alt="Eazywear Logo" class="h-8 w-auto object-contain self-start mb-0.5">

                <div class="text-[9.5px] text-slate-800 flex items-center gap-1.5">
                    <span class="font-bold text-slate-900 whitespace-nowrap">Ketentuan Retur & Garansi:</span>
                    <span class="text-slate-700 leading-tight">Maks. 3 hari setelah diterima & wajib video unboxing.</span>
                </div>
                <p class="text-[9px] text-slate-600 italic">
                    Terima kasih telah berbelanja di Eazywear!
                </p>
            </div>

            {{-- Price Breakdown --}}
            <div class="w-5/12 max-w-[200px] space-y-0.5 text-xs">
                <div class="flex justify-between text-[10.5px] text-slate-700 font-medium">
                    <span>Subtotal</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-[10.5px] text-slate-700 font-medium">
                    <span>Ongkir</span>
                    <span class="font-bold text-slate-900">Rp {{ number_format($transaction->shipping, 0, ',', '.') }}</span>
                </div>
                @if($transaction->discount > 0)
                <div class="flex justify-between text-[10.5px] text-slate-700 font-medium">
                    <span>Diskon</span>
                    <span class="font-bold text-emerald-700">-Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex items-baseline justify-between border-t border-slate-300 pt-1 mt-0.5">
                    <span class="text-xs font-bold text-slate-900">Total Bayar</span>
                    <span class="text-sm font-black text-[#895e0c]">
                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

    </div>

</body>

</html>