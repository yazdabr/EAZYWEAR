@vite(['resources/css/app.css'])

<style>
    @page {
        size: A4 portrait;
        margin: 0;
    }

    @media print {

        .no-print {
            display: none !important;
        }

        html,
        body {
            background: white !important;
            color: #0f172a !important;
            margin: 0 !important;
            padding: 12mm !important;
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
            padding: 7px 6px !important;
            overflow-wrap: anywhere !important;
        }

        .print-table-wrapper tr:hover {
            background: white !important;
        }

        .shadow-sm,
        .shadow-md,
        .shadow-lg,
        .shadow-xl,
        .shadow-2xl {
            box-shadow: none !important;
        }
    }
</style>


<div class="print-container">

    {{-- TANGGAL CETAK --}}
    <div class="mb-3 text-xs font-medium text-slate-500 print:text-slate-600">

        Dicetak pada:

        <span class="font-semibold text-slate-700 print:text-slate-900">
            {{ now()->format('d M Y, H:i') }}
        </span>

    </div>


    {{-- HEADER --}}
    <div class="mb-8 flex flex-col gap-4 border-b border-slate-200 pb-6 sm:flex-row sm:items-center sm:justify-between">

        <div>

            <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                Laporan Produksi
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Ringkasan kegiatan produksi dan jumlah produk berdasarkan periode.
            </p>


            @if($startDate && $endDate)

                <p class="mt-2 text-xs font-semibold text-[#AE7C18]">

                    Periode:

                    {{ $startDate->format('d M Y') }}
                    -
                    {{ $endDate->format('d M Y') }}

                </p>

            @else

                <p class="mt-2 text-xs font-semibold text-[#AE7C18]">
                    Periode: Semua Data
                </p>

            @endif

        </div>


        <button
            type="button"
            onclick="window.print()"
            class="no-print inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-200 hover:bg-[#96690F] active:scale-[0.98]"
        >
            <x-heroicon-o-printer class="h-5 w-5"/>

            <span>
                Cetak Laporan
            </span>
        </button>

    </div>


    {{-- SUMMARY --}}
    <div class="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-4">

        <div class="print-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                Total Produksi
            </p>

            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                {{ number_format($totalProductions, 0, ',', '.') }}
            </p>

        </div>


        <div class="print-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                Total Kuantitas
            </p>

            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                {{ number_format($totalQuantity, 0, ',', '.') }}
            </p>

        </div>


        <div class="print-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                Produksi Selesai
            </p>

            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                {{ number_format($completedProductions, 0, ',', '.') }}
            </p>

        </div>


        <div class="print-card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

            <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                Dalam Proses
            </p>

            <p class="mt-2 text-2xl font-bold tracking-tight text-slate-900">
                {{ number_format($inProgressProductions, 0, ',', '.') }}
            </p>

        </div>

    </div>


    {{-- TABLE --}}
    <div class="print-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm print:rounded-none">

        <div class="border-b border-slate-200 px-6 py-4 print:px-2 print:py-2">

            <h2 class="text-base font-semibold text-slate-900 print:text-sm">
                Daftar Produksi
            </h2>

        </div>


        <div class="print-table-wrapper w-full overflow-x-auto">

            <table class="w-full table-fixed divide-y divide-slate-200 text-left text-xs">

                <thead class="bg-slate-50 text-[10px] font-semibold uppercase tracking-wider text-slate-500 print:bg-slate-100 print:text-slate-700">

                    <tr>

                        <th class="w-[14%] px-3 py-3 print:px-1 print:py-2">
                            Kode
                        </th>

                        <th class="w-[14%] px-3 py-3 print:px-1 print:py-2">
                            Periode
                        </th>

                        <th class="w-[18%] px-3 py-3 print:px-1 print:py-2">
                            Produk
                        </th>

                        <th class="w-[12%] px-3 py-3 print:px-1 print:py-2">
                            Kategori
                        </th>

                        <th class="w-[24%] px-3 py-3 print:px-1 print:py-2">
                            Ukuran
                        </th>

                        <th class="w-[9%] px-3 py-3 text-center print:px-1 print:py-2">
                            Total
                        </th>

                        <th class="w-[9%] px-3 py-3 text-center print:px-1 print:py-2">
                            Status
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-slate-200">

                    @forelse($reportProductions as $production)

                        <tr class="break-inside-avoid">

                            <td class="px-3 py-3 font-semibold text-slate-900 print:px-1 print:py-2">
                                {{ $production['code'] }}
                            </td>

                            <td class="px-3 py-3 text-slate-600 print:px-1 print:py-2">

                                {{ $production['period_start'] }}

                                <br>

                                <span class="text-slate-400">
                                    s/d
                                </span>

                                <br>

                                {{ $production['period_end'] }}

                            </td>

                            <td class="px-3 py-3 font-medium text-slate-800 print:px-1 print:py-2">
                                {{ $production['product_name'] }}
                            </td>

                            <td class="px-3 py-3 text-slate-600 print:px-1 print:py-2">
                                {{ $production['category'] }}
                            </td>

                            <td class="px-3 py-3 text-slate-600 print:px-1 print:py-2">
                                {{ $production['sizes'] }}
                            </td>

                            <td class="px-3 py-3 text-center font-bold text-slate-900 print:px-1 print:py-2">
                                {{ number_format($production['total_quantity'], 0, ',', '.') }}
                            </td>

                            <td class="px-3 py-3 text-center font-semibold text-slate-700 print:px-1 print:py-2">
                                {{ $production['status'] }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="px-4 py-10 text-center text-sm text-slate-400"
                            >
                                Tidak ada data produksi pada periode yang dipilih.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- FOOTER --}}
    <div class="mt-6 flex items-center justify-between border-t border-slate-200 pt-4 text-xs text-slate-400">

        <p>
            Dihasilkan secara otomatis oleh Sistem Laporan Produksi.
        </p>

    </div>

</div>


<script>
    window.addEventListener('load', function () {
        window.print();
    });
</script>