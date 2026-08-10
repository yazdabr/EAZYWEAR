@vite(['resources/css/app.css'])

<style>
    @page {
        size: A4 portrait;
        margin: 0; /* Menghilangkan header & footer otomatis dari browser (Edge, Chrome, Safari) */
    }

    @media print {
        .no-print {
            display: none !important;
        }

        html, body {
            background: white !important;
            color: #0f172a !important;
            margin: 0 !important;
            padding: 12mm !important; /* Pemindahan margin kertas ke padding body agar konten tidak mepet */
            width: 100% !important;
            max-width: 100% !important;
            overflow: visible !important;
        }

        .print-container {
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
        }

        .print-card {
            width: 100% !important;
            max-width: 100% !important;
            background: white !important;
            box-shadow: none !important;
            border-color: #e2e8f0 !important;
        }

        .print-table-wrapper {
            width: 100% !important;
            max-width: 100% !important;
            overflow: visible !important;
        }

        .print-table-wrapper table {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
        }

        .print-table-wrapper thead {
            display: table-header-group !important;
        }

        .print-table-wrapper tr {
            background: white !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .print-table-wrapper th, 
        .print-table-wrapper td {
            padding: 8px 10px !important; /* Optimasi ukuran sel tabel saat dicetak */
        }

        .print-table-wrapper tr:hover {
            background: white !important;
        }

        .shadow-sm, .shadow-md, .shadow-lg, .shadow-xl, .shadow-2xl {
            box-shadow: none !important;
        }
    }
</style>

{{-- CONTAINER UTAMA --}}
<div class="print-container">

    {{-- INFORMASI TANGGAL CETAK (ATAS KIRI) --}}
    <div class="mb-3 text-xs font-medium text-slate-500 print:text-slate-600">
        Dicetak pada: <span class="font-semibold text-slate-700 print:text-slate-900">{{ date('d M Y, H:i') }}</span>
    </div>

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                Laporan Penjualan
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Ringkasan kinerja penjualan dan detail transaksi sistem.
            </p>
        </div>

        {{-- Tombol Cetak --}}
        <button type="button" onclick="window.print()" class="no-print inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-200 hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18] focus:ring-offset-2 active:scale-[0.98]">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.6 0-1.08-.466-1.12-1.066L5.88 18m11.78 0H6.34m0 0-1.282-4.171a42.12 42.12 0 0 1 12.884 0L16.66 18M12 12V3.75m0 0 2.25 2.25M12 3.75 9.75 6" />
            </svg>
            <span>Cetak Laporan</span>
        </button>
    </div>

    {{-- RINGKASAN / SUMMARY CARDS --}}
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {{-- Total Pendapatan --}}
        <div class="print-card rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                Total Pendapatan
            </p>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                Rp24.580.000
            </p>
        </div>

        {{-- Jumlah Transaksi --}}
        <div class="print-card rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                Jumlah Transaksi
            </p>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                128
            </p>
        </div>

        {{-- Rata-rata Pesanan --}}
        <div class="print-card rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                Rata-rata Pesanan
            </p>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                Rp192.031
            </p>
        </div>

        {{-- Produk Terjual --}}
        <div class="print-card rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                Produk Terjual
            </p>
            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                342
            </p>
        </div>
    </div>

    {{-- TABEL TRANSAKSI --}}
    <div class="print-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm print:border-slate-300 print:rounded-none">
        {{-- Header Tabel --}}
        <div class="border-b border-slate-200 px-6 py-4 print:px-2 print:py-2">
            <h2 class="text-base font-semibold text-slate-900 print:text-sm">
                Daftar Transaksi
            </h2>
        </div>

        {{-- Table Wrapper --}}
        <div class="print-table-wrapper w-full overflow-x-auto print:overflow-visible">
            <table class="w-full table-auto divide-y divide-slate-200 text-left text-sm print:table-fixed print:divide-slate-300 print:text-xs">
                {{-- Table Header --}}
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 print:bg-slate-100 print:text-slate-700">
                    <tr>
                        <th scope="col" class="px-4 py-3 sm:px-6 sm:py-3.5 print:w-[16%] print:px-2 print:py-2">Faktur</th>
                        <th scope="col" class="px-4 py-3 sm:px-6 sm:py-3.5 print:w-[14%] print:px-2 print:py-2">Tanggal</th>
                        <th scope="col" class="px-4 py-3 sm:px-6 sm:py-3.5 print:w-[26%] print:px-2 print:py-2">Pelanggan</th>
                        <th scope="col" class="px-4 py-3 sm:px-6 sm:py-3.5 print:w-[18%] print:px-2 print:py-2">Metode Pembayaran</th>
                        <th scope="col" class="px-4 py-3 text-center sm:px-6 sm:py-3.5 print:w-[10%] print:px-2 print:py-2">Status</th>
                        <th scope="col" class="px-4 py-3 text-right sm:px-6 sm:py-3.5 print:w-[16%] print:px-2 print:py-2">Total</th>
                    </tr>
                </thead>

                {{-- Table Body --}}
                <tbody class="divide-y divide-slate-200 bg-white print:divide-slate-200">
                    @foreach($reportTransactions as $transaction)
                        <tr class="transition hover:bg-slate-50/50 print:break-inside-avoid">
                            {{-- Faktur --}}
                            <td class="px-4 py-3 font-semibold text-slate-900 sm:px-6 sm:py-4 print:px-2 print:py-2 print:font-bold">
                                {{ $transaction['invoice'] }}
                            </td>

                            {{-- Tanggal --}}
                            <td class="px-4 py-3 text-slate-600 sm:px-6 sm:py-4 print:px-2 print:py-2 print:text-slate-800">
                                {{ $transaction['date'] }}
                            </td>

                            {{-- Pelanggan --}}
                            <td class="px-4 py-3 font-medium text-slate-800 sm:px-6 sm:py-4 print:px-2 print:py-2 print:font-normal">
                                {{ $transaction['customer'] }}
                            </td>

                            {{-- Pembayaran --}}
                            <td class="px-4 py-3 text-slate-600 sm:px-6 sm:py-4 print:px-2 print:py-2 print:text-slate-800">
                                {{ $transaction['payment'] }}
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3 text-center sm:px-6 sm:py-4 print:px-2 print:py-2">
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 print:bg-transparent print:p-0 print:font-semibold print:text-slate-900">
                                    {{ $transaction['status'] }}
                                </span>
                            </td>

                            {{-- Total --}}
                            <td class="px-4 py-3 text-right font-semibold text-slate-900 sm:px-6 sm:py-4 print:px-2 print:py-2 print:font-bold">
                                {{ $transaction['total'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="mt-6 flex items-center justify-between border-t border-slate-200 pt-4 text-xs text-slate-400">
        <p>
            Dihasilkan secara otomatis oleh Sistem Laporan Penjualan.
        </p>
    </div>
</div>

{{-- AUTO PRINT --}}
<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>