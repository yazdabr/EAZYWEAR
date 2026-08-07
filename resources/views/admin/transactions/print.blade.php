<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice }}</title>
    @vite(['resources/css/app.css'])

    <style>
        @media print {
            /* Menghilangkan header & footer bawaan browser */
            @page {
                margin: 0;
                size: auto;
            }

            body {
                background-color: #ffffff !important;
                padding: 15mm !important; /* Memberi jarak bersih di dalam kertas */
            }

            .no-print {
                display: none !important;
            }

            .print-container {
                box-shadow: none !important;
                border: none !important;
                max-width: 100% !important;
                padding: 0 !important;
            }
        }
    </style>
</head>

<body class="bg-slate-100 font-sans antialiased min-h-screen py-10 px-4 sm:px-6">

    {{-- Floating Print Bar (Hidden on Print) --}}
    <div class="no-print mx-auto max-w-4xl mb-6 flex items-center justify-between bg-white px-6 py-4 rounded-2xl shadow-sm border border-slate-200">
        <div class="flex items-center gap-3">
            <span class="flex h-3 w-3 rounded-full bg-emerald-500"></span>
            <p class="text-sm font-medium text-slate-700">Ready to print or save as PDF</p>
        </div>
        <div class="flex items-center gap-3">
            <button 
                onclick="window.print()" 
                class="inline-flex items-center gap-2 rounded-xl bg-[#AE7C18] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-[#AE7C18]/20 transition-all hover:bg-[#96690F] active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Print Invoice
            </button>
        </div>
    </div>

    {{-- Main Invoice Card --}}
    <div class="print-container mx-auto max-w-4xl bg-white p-8 sm:p-12 rounded-3xl shadow-xl border border-slate-200/80">

        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row justify-between gap-6 pb-8 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-black tracking-tight text-slate-900">INVOICE</h1>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">PAID</span>
                </div>
                <p class="mt-2 text-sm font-semibold tracking-wide text-[#AE7C18]">{{ $invoice }}</p>
            </div>

            <div class="text-left sm:text-right">
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Jersey Store</h2>
                <p class="text-sm text-slate-500 mt-1">Jl. Ahmad Yani No. 123, Banjarmasin</p>
                <p class="text-sm text-slate-500">support@jerseystore.com</p>
            </div>
        </div>

        {{-- Meta & Billed To Section --}}
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-8">
            <div class="bg-slate-50/80 rounded-2xl p-5 border border-slate-100">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Billed To</p>
                <h3 class="text-base font-bold text-slate-900">John Doe</h3>
                <p class="text-sm text-slate-600 mt-1">08123456789</p>
                <p class="text-sm text-slate-600">john@email.com</p>
            </div>

            <div class="bg-slate-50/80 rounded-2xl p-5 border border-slate-100 flex flex-col justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Invoice Date</p>
                    <p class="text-sm font-semibold text-slate-900">07 Aug 2026</p>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-200/60">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Payment Method</p>
                    <p class="text-sm font-semibold text-slate-900">Bank Transfer (BCA)</p>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="mt-10 overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b-2 border-slate-100 text-xs font-bold uppercase tracking-wider text-slate-400">
                        <th class="py-3 px-2">Item Details</th>
                        <th class="py-3 px-2 text-center">Size</th>
                        <th class="py-3 px-2 text-center">Qty</th>
                        <th class="py-3 px-2 text-right">Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    <tr>
                        <td class="py-4 px-2 font-semibold text-slate-900">Apex Jersey</td>
                        <td class="py-4 px-2 text-center font-medium text-slate-600">XL</td>
                        <td class="py-4 px-2 text-center font-medium text-slate-600">2</td>
                        <td class="py-4 px-2 text-right font-medium text-slate-900">Rp298.000</td>
                    </tr>
                    <tr>
                        <td class="py-4 px-2 font-semibold text-slate-900">Elite Jersey</td>
                        <td class="py-4 px-2 text-center font-medium text-slate-600">M</td>
                        <td class="py-4 px-2 text-center font-medium text-slate-600">1</td>
                        <td class="py-4 px-2 text-right font-medium text-slate-900">Rp199.000</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Summary Section --}}
        <div class="mt-8 flex flex-col sm:flex-row justify-between items-start gap-6 border-t border-slate-100 pt-8">
            <div class="text-xs text-slate-400 max-w-xs">
                <p class="font-semibold text-slate-500 mb-1">Terms & Conditions:</p>
                <p>Items purchased can only be exchanged within 3 days after receipt if there is a manufacturing defect.</p>
            </div>

            <div class="w-full sm:w-80 space-y-3">
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Subtotal</span>
                    <span class="font-medium text-slate-900">Rp497.000</span>
                </div>
                <div class="flex justify-between text-sm text-slate-600">
                    <span>Shipping Fee</span>
                    <span class="font-medium text-slate-900">Rp20.000</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-3 text-lg font-bold text-slate-900">
                    <span>Grand Total</span>
                    <span class="text-[#AE7C18]">Rp517.000</span>
                </div>
            </div>
        </div>

        {{-- Footer Note --}}
        <div class="mt-12 pt-8 border-t border-slate-100 text-center">
            <p class="text-sm font-semibold text-slate-800">Thank you for shopping with us!</p>
            <p class="text-xs text-slate-400 mt-1">If you have any questions regarding this invoice, please contact support.</p>
        </div>

    </div>

    <script>
        // window.onload = () => window.print();
    </script>

</body>

</html>