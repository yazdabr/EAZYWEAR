@props([
    'product',
])

@php
    $thumbnail = $product->images->where('is_thumbnail', true)->first();
    $firstImage = $product->images->first();
    $imagePath = $thumbnail?->image ?? $firstImage?->image;

    if ($imagePath) {
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            $image = $imagePath;
        } elseif (str_starts_with($imagePath, 'images/')) {
            $image = asset($imagePath);
        } elseif (str_starts_with($imagePath, 'storage/')) {
            $image = asset($imagePath);
        } else {
            $image = asset('storage/' . $imagePath);
        }
    } else {
        $image = asset('images/products/1.png');
    }

    $category = $product->category;

    if ($category instanceof \App\Models\Category) {
        $categoryName = $category->name;
    } elseif (is_array($category)) {
        $categoryName = $category['name'] ?? '-';
    } elseif (is_string($category)) {
        $decodedCategory = json_decode($category, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedCategory)) {
            $categoryName = $decodedCategory['name'] ?? '-';
        } else {
            $categoryName = $category;
        }
    } else {
        $categoryName = '-';
    }

    $firstVariant = $product->variants->first();

    $price = $firstVariant?->price ?? 0;

    $stock = $product->variants->sum(function ($variant) {
        return $variant->inventory?->stock ?? 0;
    });

    $editPrice = (int) $price;
    $editStock = (int) $stock;

    $status = $product->status ? 'Aktif' : 'Tidak Aktif';
    $updated = $product->updated_at ? $product->updated_at->diffForHumans() : '-';
@endphp

<tr
    data-product-id="{{ $product->id }}"
    class="transition duration-200 hover:bg-slate-50">
    <td class="px-6 py-5">
        <div class="flex items-center gap-4">
            <img src="{{ $image }}" alt="{{ $product->name }}" class="h-16 w-16 rounded-xl border border-slate-200 object-cover">
            <div>
                <h3 class="font-semibold text-slate-900">{{ $product->name }}</h3>
                <p class="mt-1 text-sm text-slate-500">{{ $product->material ?: 'Jersey Kustom Premium' }}</p>
            </div>
        </div>
    </td>

    <td class="px-6 py-5">
        <div>
            <p class="font-medium text-slate-900">{{ $product->product_code ?: '-' }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $categoryName }}</p>
        </div>
    </td>

    <td class="px-6 py-5 text-center">
        <span class="font-bold text-[#AE7C18]">
            Rp {{ number_format($price, 0, ',', '.') }}
        </span>
    </td>

    <td class="px-6 py-5 text-center">
        <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
            {{ $stock }}
        </span>
    </td>

    <td class="px-6 py-5 text-center">
        <x-admin.badge-status status="{{ $status }}" />
    </td>

    <td class="px-6 py-5 text-center">
        <span class="text-sm text-slate-500">
            {{ $updated }}
        </span>
    </td>

    <td class="px-6 py-5 text-center">
        <div 
            x-data="{ 
                open: false,
                topPos: 0,
                leftPos: 0,
                dropUp: false,
                toggle() {
                    if (!this.open) {
                        const rect = this.$refs.btn.getBoundingClientRect();
                        const menuHeight = 150; // Perkiraan tinggi menu dropdown
                        const spaceBelow = window.innerHeight - rect.bottom;
                        
                        this.dropUp = spaceBelow < menuHeight && rect.top > menuHeight;
                        
                        if (this.dropUp) {
                            this.topPos = rect.top - menuHeight - 6;
                        } else {
                            this.topPos = rect.bottom + 6;
                        }
                        
                        // Ratakan sisi kanan menu dengan sisi kanan tombol
                        this.leftPos = rect.right - 176; // 176px adalah w-44
                    }
                    this.open = !this.open;
                }
            }" 
            class="relative inline-block text-left">
            
            <button
                x-ref="btn"
                type="button"
                @click="toggle()"
                title="Aksi"
                class="rounded-lg p-2 transition-all duration-200 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                :class="open ? 'bg-slate-100' : ''">
                <x-heroicon-o-ellipsis-horizontal class="h-5 w-5 text-slate-500" />
            </button>

            {{-- Menggunakan x-teleport agar menu dipindah langsung ke body, bebas dari overflow-hidden --}}
            <template x-teleport="body">
                <div
                    x-show="open"
                    @click.outside="open = false"
                    @scroll.window="open = false"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    :style="`top: ${topPos}px; left: ${leftPos}px;`"
                    class="fixed z-[9999] w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-xl shadow-slate-900/10"
                    style="display:none;">
                    
                    <button
                        type="button"
                        @click="
                            open = false;

                            $dispatch('open-view-product', {
                                id: @js($product->id),
                                image: @js($image),
                                name: @js($product->name),
                                category: @js($categoryName),
                                category_id: @js($product->category_id),
                                product_code: @js($product->product_code),
                                description: @js($product->description),
                                material: @js($product->material),
                                price: @js($price),
                                stock: @js($stock),
                                status: @js($status),
                                updated: @js($updated)
                            });
                        "
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        <x-heroicon-o-eye class="h-4 w-4 shrink-0 text-slate-500" />
                        <span>Lihat</span>
                    </button>

                    {{-- UBAH --}}
                    <button
                        type="button"
                        @click.stop="
                            open = false;
                            window.dispatchEvent(new CustomEvent('open-edit-product', {
                                detail: {
                                    id: @js($product->id),
                                    name: @js($product->name),
                                    category_id: @js($product->category_id),
                                    product_code: @js($product->product_code),
                                    description: @js($product->description),
                                    material: @js($product->material),
                                    price: @js($price),
                                    stock: @js($stock),
                                    status: @js((bool) $product->status),
                                    image: @js($image)
                                }
                            }));
                        "
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        <x-heroicon-o-pencil-square class="h-4 w-4 shrink-0 text-slate-500" />
                        <span>Ubah</span>
                    </button>

                    {{-- HAPUS --}}
                    <button
                        type="button"
                        @click="
                            open = false;
                            window.dispatchEvent(new CustomEvent('open-delete-product', {
                                detail: {
                                    id: @js($product->id),
                                    name: @js($product->name)
                                }
                            }));
                        "
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50">
                        <x-heroicon-o-trash class="h-4 w-4 shrink-0" />
                        <span>Hapus</span>
                    </button>
                </div>
            </template>
        </div>
    </td>
</tr>