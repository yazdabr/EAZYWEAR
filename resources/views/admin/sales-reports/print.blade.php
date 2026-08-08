<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <title>Sales Report</title>

    @vite([
        'resources/css/app.css'
    ])

    <style>

        @media print {

            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

        }

    </style>

</head>

<body class="bg-slate-100 text-slate-900">

    <div class="mx-auto max-w-7xl p-8">

        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between">

            <div>

                <h1 class="text-3xl font-bold">

                    Sales Report

                </h1>

                <p class="mt-2 text-sm text-slate-500">

                    Sales performance and transaction report.

                </p>

            </div>

            <button

                onclick="window.print()"

                class="no-print rounded-xl bg-[#AE7C18] px-5 py-3 text-sm font-semibold text-white">

                Print Report

            </button>

        </div>


        {{-- Summary --}}
        <div class="mb-8 grid grid-cols-4 gap-4">

            <div class="rounded-2xl border border-slate-200 bg-white p-5">

                <p class="text-sm text-slate-500">

                    Total Revenue

                </p>

                <p class="mt-2 text-xl font-bold">

                    Rp24.580.000

                </p>

            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">

                <p class="text-sm text-slate-500">

                    Transactions

                </p>

                <p class="mt-2 text-xl font-bold">

                    128

                </p>

            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">

                <p class="text-sm text-slate-500">

                    Average Order

                </p>

                <p class="mt-2 text-xl font-bold">

                    Rp192.031

                </p>

            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">

                <p class="text-sm text-slate-500">

                    Products Sold

                </p>

                <p class="mt-2 text-xl font-bold">

                    342

                </p>

            </div>

        </div>


        {{-- Transaction Table --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">

            <div class="border-b border-slate-200 px-6 py-5">

                <h2 class="font-bold">

                    Transaction Report

                </h2>

            </div>

            <table class="min-w-full">

                <thead class="border-b border-slate-200 bg-slate-50">

                    <tr class="text-left text-xs font-semibold uppercase text-slate-500">

                        <th class="px-6 py-4">

                            Invoice

                        </th>

                        <th class="px-6 py-4">

                            Date

                        </th>

                        <th class="px-6 py-4">

                            Customer

                        </th>

                        <th class="px-6 py-4">

                            Payment

                        </th>

                        <th class="px-6 py-4 text-center">

                            Status

                        </th>

                        <th class="px-6 py-4 text-right">

                            Total

                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-slate-200">

                    @foreach($reportTransactions as $transaction)

                        <tr>

                            <td class="px-6 py-4 font-semibold">

                                {{ $transaction['invoice'] }}

                            </td>

                            <td class="px-6 py-4">

                                {{ $transaction['date'] }}

                            </td>

                            <td class="px-6 py-4">

                                {{ $transaction['customer'] }}

                            </td>

                            <td class="px-6 py-4">

                                {{ $transaction['payment'] }}

                            </td>

                            <td class="px-6 py-4 text-center">

                                {{ $transaction['status'] }}

                            </td>

                            <td class="px-6 py-4 text-right font-semibold">

                                {{ $transaction['total'] }}

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        <p class="mt-6 text-xs text-slate-400">

            Generated from Sales Reports.

        </p>

    </div>

    <script>

        window.addEventListener('load', function () {

            window.print();

        });

    </script>

</body>

</html>