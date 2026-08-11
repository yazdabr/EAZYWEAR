@props([
    'products',
])

{{-- ================= HEADER ================= --}}
<div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:p-5 sm:flex-row sm:items-center sm:justify-between">
    <div class="text-sm text-slate-500">
        Total:
        <span class="font-semibold text-slate-800">
            {{ $products->total() }} Produk
        </span>
    </div>
</div>

{{-- ================= TABLE ================= --}}
<div class="w-full overflow-x-auto">
    <table class="w-full min-w-[850px] text-left border-collapse">
        {{-- ================= HEAD ================= --}}
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-slate-500">Produk</th>
                <th class="px-4 py-3.5 text-xs font-semibold uppercase tracking-wider text-slate-500">SKU & Kategori</th>
                <th class="px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Harga</th>
                <th class="px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Stok</th>
                <th class="px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                <th class="px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Diperbarui</th>
                <th class="px-4 py-3.5 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Aksi</th>
            </tr>
        </thead>

        {{-- ================= BODY ================= --}}
        <tbody
            class="divide-y divide-slate-100 bg-white"
            x-data
            x-on:product-deleted.window="
                const row = document.querySelector('[data-product-id=\'' + $event.detail.id + '\']');
                if (row) {
                    row.remove();
                }
            "
        >
            @forelse($products as $product)
                <x-admin.product-row :product="$product" />
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <x-heroicon-o-cube class="h-10 w-10 text-slate-300" />
                            <p class="mt-3 font-medium text-slate-600">Belum ada produk</p>
                            <p class="mt-1 text-sm text-slate-400">Data produk akan muncul di sini.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- ================= FOOTER ================= --}}
<div class="border-t border-slate-200 bg-slate-50 px-4 py-3.5 sm:px-6">
    <x-admin.table-pagination :paginator="$products" />
</div>