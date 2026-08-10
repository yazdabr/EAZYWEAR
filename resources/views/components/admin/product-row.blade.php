@php
$product = [
    'image' => asset('images/products/1.png'),
    'name' => 'Apex Pro Kit',
    'sku' => 'PRD-001',
    'category' => 'Jersey Sepak Bola',
    'description' => 'Jersey kustom premium terbuat dari kain dry-fit yang bernapas dengan desain tanpa batas.',
    'price' => 149000,
    'stock' => 128,
    'status' => 'Aktif',
    'updated' => '2 Jam Lalu',
];
@endphp
<tr class="transition duration-200 hover:bg-slate-50">


    {{-- Produk --}}
    <td class="px-6 py-5">
        <div class="flex items-center gap-4">
            <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="h-16 w-16 rounded-xl border border-slate-200 object-cover">
            <div>
                <h3 class="font-semibold text-slate-900">{{ $product['name'] }}</h3>
                <p class="mt-1 text-sm text-slate-500">Jersey Kustom Premium</p>
            </div>
        </div>
    </td>

    {{-- SKU --}}
    <td class="px-6 py-5">
        <div>
            <p class="font-medium text-slate-900">{{ $product['sku'] }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $product['category'] }}</p>
        </div>
    </td>

    {{-- Harga --}}
    <td
        class="px-6 py-5 text-center">

        <span
            class="font-bold text-[#AE7C18]">

            Rp {{ number_format($product['price'], 0, ',', '.') }}

        </span>

    </td>

    {{-- Stok --}}
    <td class="px-6 py-5 text-center">
        <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">{{ $product['stock'] }}</span>
    </td>

    {{-- Status --}}
    <td
        class="px-6 py-5 text-center">

        <x-admin.badge-status
            status="{{ $product['status'] }}" />

    </td>

    {{-- Diperbarui --}}
    <td
        class="px-6 py-5 text-center">

        <span
            class="text-sm text-slate-500">

            {{ $product['updated'] }}

        </span>

    </td>

    {{-- Aksi --}}
    <td class="px-6 py-5 text-center">

        <div
            x-data="{ open: false }"
            class="relative inline-block text-left">

            {{-- Tombol Aksi --}}
            <button
                type="button"
                @click="open = !open"
                title="Aksi"

                class="rounded-lg p-2 transition-all duration-200 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"

                :class="open ? 'bg-slate-100' : ''">

                <x-heroicon-o-ellipsis-horizontal
                    class="h-5 w-5 text-slate-500" />

            </button>


            {{-- Dropdown Aksi --}}
            <div
                x-show="open"

                @click.outside="open = false"

                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"

                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 -translate-y-1"

                class="absolute right-0 top-full z-[80] mt-2 w-44 origin-top-right overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-xl shadow-slate-900/10"

                style="display:none;">


                {{-- ================= LIHAT ================= --}}
                <button
                    type="button"

                    @click="
                        open = false;

                        $dispatch('open-view-product', {

                            image: @js($product['image']),

                            name: @js($product['name']),

                            category: @js($product['category']),

                            description: @js($product['description']),

                            sku: @js($product['sku']),

                            price: @js($product['price']),

                            stock: @js($product['stock']),

                            status: @js($product['status']),

                            updated: @js($product['updated'])

                        });
                    "

                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50">

                    <x-heroicon-o-eye
                        class="h-4 w-4 shrink-0 text-slate-500" />

                    <span>
                        Lihat
                    </span>

                </button>


                {{-- ================= UBAH ================= --}}
                <button
                    type="button"

                    @click="
                        open = false;

                        $dispatch('open-edit-product', {

                            name: @js($product['name']),

                            category: @js($product['category']),

                            sku: @js($product['sku']),

                            description: @js($product['description']),

                            price: @js($product['price']),

                            stock: @js($product['stock']),

                            status: @js($product['status']),

                            image: @js($product['image'])

                        });
                    "

                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50">

                    <x-heroicon-o-pencil-square
                        class="h-4 w-4 shrink-0 text-slate-500" />

                    <span>
                        Ubah
                    </span>

                </button>


                {{-- ================= HAPUS ================= --}}
                <button
                    type="button"

                    @click="
                        open = false;

                        $dispatch('open-delete-product', {

                            id: @js($product['sku']),

                            name: @js($product['name'])

                        });
                    "

                    class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50">

                    <x-heroicon-o-trash
                        class="h-4 w-4 shrink-0" />

                    <span>
                        Hapus
                    </span>

                </button>


            </div>

        </div>

    </td>

</tr>