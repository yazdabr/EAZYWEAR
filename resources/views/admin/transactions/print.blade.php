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
                size: A4 portrait;
            }

            body {
                background-color: #ffffff !important;
                padding: 0 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .no-print {
                display: none !important;
            }

            .print-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 20mm 15mm !important;
                margin: 0 !important;
                border-radius: 0 !important;
            }

            .page-break-inside-avoid {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body class="bg-slate-100 font-sans antialiased min-h-screen py-6 px-4 sm:px-6 lg:py-10">

    {{-- Floating Print Bar (Hidden on Print) --}}
    <div class="no-print mx-auto max-w-4xl mb-6 flex flex-wrap items-center justify-between gap-4 bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-3">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
            <div>
                <p class="text-sm font-semibold text-slate-800">Pratinjau Invoice Pembayaran</p>
                <p class="text-xs text-slate-500">Siap dicetak atau disimpan ke PDF</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button 
                onclick="window.print()" 
                class="inline-flex items-center gap-2 rounded-xl bg-[#AE7C18] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#AE7C18]/20 transition-all hover:bg-[#96690F] active:scale-95 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Invoice
            </button>
        </div>
    </div>

    {{-- Main Invoice Card --}}
    <div class="print-container relative overflow-hidden mx-auto max-w-4xl bg-white p-8 sm:p-12 rounded-3xl shadow-xl border border-slate-200/80">
        
        {{-- Top Accent Bar --}}
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-[#AE7C18] via-[#D4A038] to-[#AE7C18]"></div>

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row justify-between items-start gap-6 pb-8 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-black tracking-tight text-slate-900">Invoice</h1>
                    @php
                        $statusLabel = match (strtoupper($transaction->status ?? '')) {
                            'PAID' => 'DIBAYAR',
                            'COMPLETED' => 'SELESAI',
                            'CANCELLED' => 'DIBATALKAN',
                            'PENDING' => 'PENDING',
                            default => $transaction->status ?? '-',
                        };
                    @endphp

                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-600"></span>
                        {{ $statusLabel }}
                    </span>
                </div>
                <p class="mt-2 text-sm font-bold tracking-wider text-[#AE7C18] uppercase">
                    #{{ $transaction->invoice_number }}
                </p>
            </div>

            <div class="text-left sm:text-right">
                <div class="inline-flex items-center gap-2 mb-1">
                    <span class="h-6 w-6 rounded-lg bg-[#AE7C18] flex items-center justify-center text-white font-bold text-xs">J</span>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Jersey Store</h2>
                </div>
                <p class="text-xs text-slate-500">Jl. Ahmad Yani No. 123, Banjarmasin</p>
                <p class="text-xs text-slate-500">Kalsel, Indonesia 70234</p>
                <p class="text-xs text-slate-500 mt-1">support@jerseystore.com • 0811-5000-123</p>
            </div>
        </div>

        {{-- Meta & Billed To Section --}}
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-slate-50/80 rounded-2xl p-5 border border-slate-100">
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Ditagihkan Kepada</p>
                <h3 class="text-base font-bold text-slate-900">
                    {{ $transaction->customer?->name ?? '-' }}
                </h3>

                <p class="mt-1 text-xs text-slate-600">
                    {{ $transaction->customer?->phone ?? '-' }}
                </p>

                <p class="text-xs text-slate-600">
                    {{ $transaction->customer?->email ?? '-' }}
                </p>

                <p class="mt-2 border-t border-slate-200/60 pt-2 text-xs text-slate-500">
                    {{ $transaction->customer?->address ?? '-' }}
                </p>
            </div>

            <div class="bg-slate-50/80 rounded-2xl p-5 border border-slate-100 flex flex-col justify-between">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Tanggal Invoice</p>
                        <p class="text-xs font-bold text-slate-900">
                            {{ $transaction->transaction_date?->format('d M Y') ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">No. Transaksi</p>
                        <p class="text-xs font-bold text-slate-900">
                            {{ $transaction->invoice_number }}
                        </p>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-200/60 flex justify-between items-center">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Metode Pembayaran</p>
                        <p class="mt-0.5 text-xs font-bold text-slate-900">
                            {{ $transaction->payment_method ?? '-' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="mt-8 overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/50">
                        <th class="py-3 px-3 rounded-l-xl">Detail Produk</th>
                        <th class="py-3 px-3 text-center">Ukuran</th>
                        <th class="py-3 px-3 text-center">Qty</th>
                        <th class="py-3 px-3 text-right">Harga Satuan</th>
                        <th class="py-3 px-3 text-right rounded-r-xl">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs sm:text-sm">
                    @forelse($transaction->items as $item)
                        @php
                            $variant = $item->productVariant;
                            $product = $variant?->product;
                        @endphp

                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-3 py-4">
                                <div>
                                    <p class="font-bold text-slate-900">
                                        {{ $product?->name ?? '-' }}
                                    </p>
                                </div>
                            </td>

                            <td class="px-3 py-4 text-center">
                                <span class="inline-block rounded bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">
                                    {{ $variant?->size?->name ?? '-' }}
                                </span>
                            </td>

                            <td class="px-3 py-4 text-center font-semibold text-slate-700">
                                {{ $item->qty }}
                            </td>

                            <td class="px-3 py-4 text-right text-slate-600">
                                Rp {{ number_format($item->price, 0, ',', '.') }}
                            </td>

                            <td class="px-3 py-4 text-right font-bold text-slate-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-slate-400">
                                Tidak ada produk dalam transaksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Summary & Notes Section --}}
        <div class="mt-8 flex flex-col sm:flex-row justify-between items-start gap-8 border-t border-slate-100 pt-8 page-break-inside-avoid">
            {{-- Terms & Notes --}}
            <div class="w-full sm:w-1/2 space-y-3">
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                    <p class="text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-[#AE7C18]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Syarat & Ketentuan:
                    </p>
                    <ul class="text-[11px] text-slate-500 leading-relaxed list-disc list-inside space-y-0.5">
                        <li>Pengembalian barang berlaku maks. 3 hari setelah diterima.</li>
                        <li>Wajib menyertakan video unboxing untuk klaim cacat produksi.</li>
                    </ul>
                </div>
            </div>

            {{-- Price Breakdown --}}
            <div class="w-full sm:w-72 space-y-2.5">
                <div class="flex justify-between text-xs text-slate-600">
                    <span>Subtotal Produk</span>
                    <span class="font-semibold text-slate-900">
                        Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}
                    </span>
                </div>

                <div class="flex justify-between text-xs text-slate-600">
                    <span>Ongkos Kirim</span>
                    <span class="font-semibold text-slate-900">
                        Rp {{ number_format($transaction->shipping, 0, ',', '.') }}
                    </span>
                </div>

                <div class="flex justify-between text-xs text-slate-600">
                    <span>Diskon</span>
                    <span class="font-semibold text-emerald-600">
                        -Rp {{ number_format($transaction->discount, 0, ',', '.') }}
                    </span>
                </div>

                <div class="flex items-baseline justify-between border-t border-slate-200 pt-3">
                    <span class="text-sm font-bold text-slate-900">
                        Total Bayar
                    </span>
                    <span class="text-xl font-black text-[#AE7C18]">
                        Rp {{ number_format($transaction->total, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Footer Note --}}
        <div class="mt-12 pt-6 border-t border-slate-100 text-center page-break-inside-avoid">
            <p class="text-xs font-bold text-slate-800">Terima kasih telah berbelanja di Jersey Store!</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Invoice ini diterbitkan secara otomatis dan sah tanpa tanda tangan basah.</p>
        </div>

    </div>

</body>

</html>