@props(['category'])

<tr class="transition duration-200 hover:bg-slate-50">
    {{-- Kategori --}}
    <td class="px-6 py-5">
        <div>
            <h3 class="font-semibold text-slate-900">
                {{ $category->name }}
            </h3>
            <p class="mt-1 text-sm text-slate-500">
                {{ $category->description ?: '-' }}
            </p>
        </div>
    </td>

    {{-- Slug --}}
    <td class="px-6 py-5">
        <span class="text-slate-600">
            {{ $category->slug }}
        </span>
    </td>

    {{-- Produk --}}
    <td class="px-6 py-5 text-center">
        <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1 text-sm font-semibold">
            {{ $category->products_count ?? $category->products()->count() }}
        </span>
    </td>

    {{-- Status --}}
    <td class="px-6 py-5 text-center">
        <x-admin.badge-status
            status="{{ $category->status ? 'Aktif' : 'Tidak Aktif' }}"/>
    </td>

    {{-- Dibuat --}}
    <td class="px-6 py-5 text-center">
        <span class="text-sm text-slate-500">
            {{ $category->created_at?->format('d M Y') ?? '-' }}
        </span>
    </td>

    {{-- Aksi --}}
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
                        const menuHeight = 150; // Perkiraan tinggi dropdown menu kategori
                        const spaceBelow = window.innerHeight - rect.bottom;
                        
                        this.dropUp = spaceBelow < menuHeight && rect.top > menuHeight;
                        
                        if (this.dropUp) {
                            this.topPos = rect.top - menuHeight - 6;
                        } else {
                            this.topPos = rect.bottom + 6;
                        }
                        
                        // Ratakan sisi kanan menu dengan sisi kanan tombol
                        this.leftPos = rect.right - 176; // 176px adalah lebar w-44
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

                <x-heroicon-o-ellipsis-horizontal class="h-5 w-5 text-slate-500"/>
            </button>

            {{-- Menggunakan x-teleport agar menu dirender langsung di <body> --}}
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

                    {{-- LIHAT --}}
                    <button
                        type="button"
                        @click="
                            open = false;
                            $dispatch('open-view-category',{
                                id: @js($category->id),
                                name: @js($category->name),
                                slug: @js($category->slug),
                                description: @js($category->description),
                                products: @js($category->products_count ?? $category->products()->count()),
                                status: @js($category->status ? 'Aktif' : 'Tidak Aktif'),
                                status_value: @js((bool)$category->status),
                                created: @js($category->created_at?->format('d M Y'))
                            });
                        "
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50">

                        <x-heroicon-o-eye class="h-4 w-4 shrink-0 text-slate-500"/>
                        <span>Lihat</span>
                    </button>

                    {{-- UBAH --}}
                    <button
                        type="button"
                        @click="
                            open = false;
                            $dispatch('open-edit-category',{
                                id: @js($category->id),
                                name: @js($category->name),
                                slug: @js($category->slug),
                                description: @js($category->description),
                                status: @js((bool)$category->status),
                                image: @js($category->image)
                            });
                        "
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50">

                        <x-heroicon-o-pencil-square class="h-4 w-4 shrink-0 text-slate-500"/>
                        <span>Ubah</span>
                    </button>

                    {{-- HAPUS --}}
                    <button
                        type="button"
                        @click="
                            open = false;
                            window.dispatchEvent(
                                new CustomEvent('open-delete-category', {
                                    detail: {
                                        id: @js($category->id),
                                        name: @js($category->name)
                                    }
                                })
                            );
                        "
                        class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50">
                        <x-heroicon-o-trash class="h-4 w-4 shrink-0"/>
                        <span>Hapus</span>
                    </button>
                </div>
            </template>
        </div>
    </td>
</tr>