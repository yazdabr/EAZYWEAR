@props([
    'category'
])

<tr class="transition duration-200 hover:bg-slate-50">

    {{-- Kategori --}}
    <td class="px-6 py-5">

        <div>

            <h3 class="font-semibold text-slate-900">

                {{ $category['name'] }}

            </h3>

            <p class="mt-1 text-sm text-slate-500">

                {{ $category['description'] }}

            </p>

        </div>

    </td>

    {{-- Slug --}}
    <td class="px-6 py-5">

        <span class="text-slate-600">

            {{ $category['slug'] }}

        </span>

    </td>

    {{-- Produk --}}
    <td class="px-6 py-5 text-center">

        <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1 text-sm font-semibold">

            {{ $category['products'] }}

        </span>

    </td>

    {{-- Status --}}
    <td class="px-6 py-5 text-center">

        <x-admin.badge-status
            status="{{ $category['status'] }}" />

    </td>

    {{-- Dibuat --}}
    <td class="px-6 py-5 text-center">

        <span class="text-sm text-slate-500">

            {{ $category['created'] }}

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

                        $dispatch('open-view-category', {

                            name: @js($category['name']),

                            slug: @js($category['slug']),

                            description: @js($category['description']),

                            products: @js($category['products']),

                            status: @js($category['status']),

                            created: @js($category['created'])

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

                        $dispatch('open-edit-category', {

                            name: @js($category['name']),

                            slug: @js($category['slug']),

                            description: @js($category['description']),

                            status: @js($category['status'])

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

                        $dispatch('open-delete-category', {

                            id: @js($category['slug']),

                            name: @js($category['name'])

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