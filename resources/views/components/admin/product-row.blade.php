@php

$product = [

    'image' => asset('images/products/1.png'),

    'name' => 'Apex Pro Kit',

    'sku' => 'PRD-001',

    'category' => 'Football Jersey',

    'description' => 'Premium custom jersey made with breathable dry-fit fabric and unlimited custom design.',

    'price' => 149000,

    'stock' => 128,

    'status' => 'Active',

    'updated' => '2 Hours Ago',

];

@endphp

<tr
    class="transition duration-200 hover:bg-slate-50">

    {{-- Checkbox --}}
    <td
        class="px-6 py-5">

        <input
            type="checkbox"
            class="h-4 w-4 rounded border-slate-300 text-[#AE7C18] focus:ring-[#AE7C18]">

    </td>

    {{-- Product --}}
    <td
        class="px-6 py-5">

        <div
            class="flex items-center gap-4">

            <img
                src="{{ $product['image'] }}"
                alt="{{ $product['name'] }}"
                class="h-16 w-16 rounded-xl border border-slate-200 object-cover">

            <div>

                <h3
                    class="font-semibold text-slate-900">

                    {{ $product['name'] }}

                </h3>

                <p
                    class="mt-1 text-sm text-slate-500">

                    Premium Custom Jersey

                </p>

            </div>

        </div>

    </td>

    {{-- SKU --}}
    <td
        class="px-6 py-5">

        <div>

            <p
                class="font-medium text-slate-900">

                {{ $product['sku'] }}

            </p>

            <p
                class="mt-1 text-sm text-slate-500">

                {{ $product['category'] }}

            </p>

        </div>

    </td>

    {{-- Price --}}
    <td
        class="px-6 py-5 text-center">

        <span
            class="font-bold text-[#AE7C18]">

            Rp {{ number_format($product['price'], 0, ',', '.') }}

        </span>

    </td>

    {{-- Stock --}}
    <td
        class="px-6 py-5 text-center">

        <span
            class="inline-flex rounded-lg bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">

            {{ $product['stock'] }}

        </span>

    </td>

    {{-- Status --}}
    <td
        class="px-6 py-5 text-center">

        <x-admin.badge-status
            status="{{ $product['status'] }}" />

    </td>

    {{-- Updated --}}
    <td
        class="px-6 py-5 text-center">

        <span
            class="text-sm text-slate-500">

            {{ $product['updated'] }}

        </span>

    </td>

    {{-- Action --}}
    <td
        class="px-6 py-5 text-center">

        <div
            x-data="{ open:false }"
            class="relative inline-block">

            <button
                @click="open=!open"
                class="rounded-lg p-2 transition hover:bg-slate-100">

                <x-heroicon-o-ellipsis-horizontal
                    class="h-5 w-5 text-slate-500"/>

            </button>

            <div
                x-show="open"
                @click.outside="open=false"
                x-transition
                class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl"
                style="display:none;">

                <button

                    @click="
                        open = false;

                        $dispatch('open-view-product',{

                            image:'{{ $product['image'] }}',

                            name:'{{ $product['name'] }}',

                            category:'{{ $product['category'] }}',

                            description:'{{ $product['description'] }}',

                            sku:'{{ $product['sku'] }}',

                            price:'{{ $product['price'] }}',

                            stock:'{{ $product['stock'] }}',

                            updated:'{{ $product['updated'] }}'

                        });

                    "

                    class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50">

                    <x-heroicon-o-eye
                        class="h-4 w-4"/>

                    View

                </button>

                <button

                    @click="
                        open = false;

                        $dispatch('open-edit-product',{

                            name: '{{ $product['name'] }}',

                            category: '{{ $product['category'] }}',

                            sku: '{{ $product['sku'] }}',

                            description: '{{ $product['description'] }}',

                            price: '{{ $product['price'] }}',

                            stock: '{{ $product['stock'] }}',

                            image: '{{ $product['image'] }}'

                        });
                    "

                    class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50">

                    <x-heroicon-o-pencil-square
                        class="h-4 w-4"/>

                    Edit

                </button>

                <button

                    @click="
                        open=false;

                        $dispatch('open-delete-product',{

                            id:'{{ $product['sku'] }}',

                            name:'{{ $product['name'] }}'

                        });
                    "

                    class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-red-600 transition hover:bg-red-50">

                    <x-heroicon-o-trash
                        class="h-4 w-4"/>

                    Delete

                </button>

            </div>

        </div>

    </td>

</tr>