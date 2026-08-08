<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:p-5 sm:flex-row sm:items-center sm:justify-between">
        
        <div class="text-sm text-slate-500">
            Total :
            <span class="font-semibold text-slate-800">
                128 Products
            </span>
        </div>

    </div>

    {{-- ================= TABLE ================= --}}
    <div class="w-full overflow-x-auto">

        {{-- min-w-[850px] memastikan isi tabel tidak tertekan rapat di mobile --}}
        <table class="w-full min-w-[850px] text-left border-collapse">

            {{-- ================= HEAD ================= --}}
            <thead class="bg-slate-50 border-b border-slate-200">

                <tr>

                    <th class="w-10 px-4 py-3.5 text-center">
                        <input type="checkbox" class="rounded border-slate-300 text-[#C4902C] focus:ring-[#C4902C]">
                    </th>

                    <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Product
                    </th>

                    <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        SKU & Category
                    </th>

                    <th class="px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Price
                    </th>

                    <th class="px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Stock
                    </th>

                    <th class="px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Status
                    </th>

                    <th class="px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Updated
                    </th>

                    <th class="px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                        Action
                    </th>

                </tr>

            </thead>

            {{-- ================= BODY ================= --}}
            <tbody class="divide-y divide-slate-100 bg-white">

                <x-admin.product-row />

                <x-admin.product-row />

                <x-admin.product-row />

            </tbody>

        </table>

    </div>

    {{-- ================= FOOTER ================= --}}
    <div class="border-t border-slate-200 bg-slate-50 px-4 py-3.5 sm:px-6">

        <x-admin.table-pagination />

    </div>

</div>