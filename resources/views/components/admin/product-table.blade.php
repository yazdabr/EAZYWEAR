@props([
    'products',
])

<div class="flex flex-col gap-3 border-b border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
    <div class="text-sm text-slate-500">
        Total:
        <span class="font-semibold text-slate-800">
            {{ $products->total() }} Produk
        </span>
    </div>
</div>

{{-- TAMPILAN DESKTOP --}}
<div class="hidden w-full overflow-x-auto md:block">
    <table class="w-full min-w-[850px] border-collapse text-left">
        <thead class="border-b border-slate-200 bg-slate-50">
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
        <tbody class="divide-y divide-slate-100 bg-white" x-data x-on:product-deleted.window="const row = document.querySelector('[data-product-id=\'' + $event.detail.id + '\']'); if (row) { row.remove(); }">
            @forelse($products as $product)
                <x-admin.product-row :product="$product" />
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <x-heroicon-o-cube class="h-10 w-10 text-slate-300"/>
                            <p class="mt-3 font-medium text-slate-600">Belum ada produk</p>
                            <p class="mt-1 text-sm text-slate-400">Data produk akan muncul di sini.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- TAMPILAN MOBILE --}}
<div class="divide-y divide-slate-100 bg-white md:hidden" x-data x-on:product-deleted.window="const card = document.querySelector('[data-product-card-id=\'' + $event.detail.id + '\']'); if (card) { card.remove(); }">
    @forelse($products as $product)
        @php
            // Gambar Produk
            $thumbnail = $product->images->where('is_thumbnail', true)->first();
            $firstImage = $product->images->first();
            $imagePath = $thumbnail?->image ?? $firstImage?->image;

            if ($imagePath) {
                $image = (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://'))
                    ? $imagePath
                    : ((str_starts_with($imagePath, 'images/') || str_starts_with($imagePath, 'storage/'))
                        ? asset($imagePath)
                        : asset('storage/' . $imagePath));
            } else {
                $image = asset('images/products/1.png');
            }

            // Nama Kategori
            $category = $product->category;
            if ($category instanceof \App\Models\Category) {
                $categoryName = $category->name;
            } elseif (is_array($category)) {
                $categoryName = $category['name'] ?? '-';
            } elseif (is_string($category)) {
                $decodedCategory = json_decode($category, true);
                $categoryName = (json_last_error() === JSON_ERROR_NONE && is_array($decodedCategory))
                    ? ($decodedCategory['name'] ?? '-')
                    : $category;
            } else {
                $categoryName = '-';
            }

            // Pemrosesan Varian & Size IDs (Disamakan persis dengan product-row)
            $variantsData = [];
            $sizeIds = [];

            foreach ($product->variants as $variant) {
                if (!$variant->size_id) {
                    continue;
                }

                $sizeId = (string) $variant->size_id;
                $sizeIds[] = (int) $variant->size_id;

                $variantsData[$sizeId] = [
                    'id' => $variant->id,
                    'size_id' => (int) $variant->size_id,
                    'name' => $variant->size?->name ?? '',
                    'price' => (int) ($variant->price ?? 0),
                    'stock' => (int) ($variant->inventory?->stock ?? 0),
                ];
            }

            $sizeIds = array_values(array_unique($sizeIds));

            $firstVariant = $product->variants->first();
            $price = $firstVariant?->price ?? 0;
            $stock = $product->variants->sum(fn($v) => (int)($v->inventory?->stock ?? $v->stock ?? 0));
            $status = $product->status ? 'Aktif' : 'Tidak Aktif';
            $updated = $product->updated_at ? $product->updated_at->diffForHumans() : '-';

            // Payload Diselaraskan
            $editData = [
                'id' => $product->id,
                'name' => $product->name,
                'category_id' => $product->category_id,
                'product_code' => $product->product_code,
                'description' => $product->description,
                'material' => $product->material,
                'price' => (int) $price,
                'stock' => (int) $stock,
                'status' => (bool) $product->status,
                'image' => $image,
                'size_ids' => $sizeIds,
                'variants' => $variantsData,
            ];

            $viewData = [
                'id' => $product->id,
                'image' => $image,
                'name' => $product->name,
                'category' => $categoryName,
                'category_id' => $product->category_id,
                'product_code' => $product->product_code,
                'description' => $product->description,
                'material' => $product->material,
                'price' => (int) $price,
                'stock' => (int) $stock,
                'status' => $status,
                'updated' => $updated,
                'size_ids' => $sizeIds,
                'variants' => $variantsData,
            ];
        @endphp
        
        <div data-product-card-id="{{ $product->id }}" class="p-4 space-y-3">
            <div class="flex items-center justify-between gap-3">
                <div class="flex min-w-0 flex-1 items-center gap-3">
                    <img src="{{ $image }}" alt="{{ $product->name }}" class="h-12 w-12 shrink-0 rounded-lg border border-slate-100 object-cover">
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-sm font-bold text-slate-900 sm:text-base" title="{{ $product->name }}">
                            {{ $product->name }}
                        </h3>
                        <p class="mt-0.5 text-xs font-mono text-slate-400">
                            {{ $product->product_code ?: '-' }}
                        </p>
                    </div>
                </div>
                <div class="shrink-0 whitespace-nowrap">
                    <x-admin.badge-status :status="$status"/>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3 border-t border-slate-50 pt-3">
                <div class="min-w-0">
                    <span class="block text-[10px] font-semibold uppercase text-slate-400">Kategori</span>
                    <span class="mt-0.5 block truncate text-xs font-medium text-slate-700">{{ $categoryName }}</span>
                </div>
                <div class="text-center">
                    <span class="block text-[10px] font-semibold uppercase text-slate-400">Stok</span>
                    <span class="mt-0.5 block text-xs font-semibold {{ $stock > 5 ? 'text-slate-700' : 'text-amber-600' }}">{{ $stock }}</span>
                </div>
                <div class="text-right">
                    <span class="block text-[10px] font-semibold uppercase text-slate-400">Harga</span>
                    <span class="mt-0.5 block truncate text-xs font-bold text-[#AE7C18]">Rp {{ number_format($price, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="flex justify-between border-t border-slate-50 pt-2">
                <span class="text-[11px] text-slate-400">Diperbarui</span>
                <span class="text-[11px] font-medium text-slate-500">{{ $updated }}</span>
            </div>
            
            {{-- TOMBOL AKSI MOBILE --}}
            <div class="flex items-center justify-between gap-2 border-t border-slate-100 pt-3">
                <div class="flex items-center gap-2">
                    {{-- TOMBOL LIHAT --}}
                    <button type="button" @click="window.dispatchEvent(new CustomEvent('open-view-product', { detail: @js($viewData) }));" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 active:bg-slate-100">
                        <x-heroicon-o-eye class="h-3.5 w-3.5 text-slate-500"/>
                        <span>Lihat</span>
                    </button>
                    
                    {{-- TOMBOL UBAH --}}
                    <button type="button" @click="window.dispatchEvent(new CustomEvent('open-edit-product', { detail: @js($editData) }));" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50 active:bg-slate-100">
                        <x-heroicon-o-pencil-square class="h-3.5 w-3.5 text-slate-500"/>
                        <span>Ubah</span>
                    </button>
                </div>
                
                {{-- TOMBOL HAPUS --}}
                <button type="button" @click="window.dispatchEvent(new CustomEvent('open-delete-product', { detail: { id: @js($product->id), name: @js($product->name) } }));" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50/50 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-100/50 active:bg-red-100">
                    <x-heroicon-o-trash class="h-3.5 w-3.5"/>
                    <span>Hapus</span>
                </button>
            </div>
        </div>
    @empty
        <div class="px-4 py-10 text-center">
            <x-heroicon-o-cube class="mx-auto h-9 w-9 text-slate-300"/>
            <p class="mt-2 text-sm font-medium text-slate-600">Belum ada produk</p>
            <p class="mt-0.5 text-xs text-slate-400">Data produk akan muncul di sini.</p>
        </div>
    @endforelse
</div>

<div class="border-t border-slate-200 bg-slate-50 px-4 py-3.5 sm:px-6">
    <x-admin.table-pagination :paginator="$products"/>
</div>