<div
    x-data="{

        open:false,

        product:{

            image:'',

            name:'',

            category:'',

            description:'',

            sku:'',

            price:'',

            stock:'',

            status: 'Aktif',

            updated:''

        },

        openView(product) {

            this.product = {
                ...product,

                status: product.status || 'Aktif'
            };

            this.open = true;

        }

    }"

    x-on:open-view-product.window="openView($event.detail)">

    {{-- ================= OVERLAY ================= --}}
    <div

        x-show="open"

        x-transition.opacity

        @click="open=false"

        class="fixed inset-0 z-[190] bg-black/40 backdrop-blur-sm"

        style="display:none;">

    </div>

    {{-- ================= DRAWER ================= --}}
    <div

        x-show="open"

        x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"

        x-transition:enter-start="translate-x-full"

        x-transition:enter-end="translate-x-0"

        x-transition:leave="transition transform duration-250 ease-in-out"

        x-transition:leave-start="translate-x-0"

        x-transition:leave-end="translate-x-full"

        class="fixed right-0 top-0 z-[200] flex h-screen w-full max-w-[520px] flex-col bg-white shadow-2xl"

        style="display:none;">

        {{-- HEADER --}}
        <div
            class="flex items-center justify-between border-b border-slate-200 px-7 py-6">

            <div class="flex items-center gap-3">

                <div
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100">

                    <x-heroicon-o-eye
                        class="h-5 w-5 text-sky-600"/>

                </div>

                <div>

                    <h2
                        class="text-xl font-bold text-slate-900">

                        Detail Produk

                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500">

                        Lihat informasi produk.

                    </p>

                </div>

            </div>

            <button

                @click="open=false"

                class="rounded-lg p-2 transition hover:bg-slate-100">

                <x-heroicon-o-x-mark
                    class="h-6 w-6"/>

            </button>

        </div>

        {{-- BODY --}}
        <div
            class="flex-1 overflow-y-auto bg-slate-100 p-6">

            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                {{-- Image --}}
                <img

                    :src="product.image"

                    class="aspect-square w-full rounded-2xl object-cover">

                {{-- Title --}}
                <div class="mt-6">

                    <p

                        class="text-sm font-semibold uppercase tracking-wider text-[#AE7C18]"

                        x-text="product.category">

                    </p>

                    <h2

                        class="mt-2 text-2xl font-bold text-slate-900"

                        x-text="product.name">

                    </h2>

                </div>

                {{-- Description --}}
                <div class="mt-8">

                    <h4
                        class="mb-2 text-sm font-semibold text-slate-700">

                        Deskripsi

                    </h4>

                    <p

                        class="leading-7 text-slate-600"

                        x-text="product.description">

                    </p>

                </div>

                {{-- Information --}}
                <div
                    class="mt-8 space-y-4">

                    {{-- Harga --}}
                    <div
                        class="flex justify-between">

                        <span
                            class="text-slate-500">

                            Harga

                        </span>

                        <span
                            class="font-bold text-[#AE7C18]"
                            x-text="'Rp ' + Number(product.price).toLocaleString('id-ID')">

                        </span>

                    </div>


                    {{-- Stok --}}
                    <div
                        class="flex justify-between">

                        <span
                            class="text-slate-500">

                            Stok

                        </span>

                        <span
                            class="font-semibold"
                            x-text="product.stock">

                        </span>

                    </div>


                    {{-- Status --}}
                    <div
                        class="flex items-center justify-between">

                        <span
                            class="text-slate-500">

                            Status

                        </span>

                        <span
                            class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold"

                            :class="product.status === 'Aktif'
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-red-100 text-red-700'">

                            <span
                                class="h-1.5 w-1.5 rounded-full"

                                :class="product.status === 'Aktif'
                                    ? 'bg-emerald-500'
                                    : 'bg-red-500'">
                            </span>

                            <span x-text="product.status"></span>

                        </span>

                    </div>


                    {{-- SKU --}}
                    <div
                        class="flex justify-between">

                        <span
                            class="text-slate-500">

                            SKU

                        </span>

                        <span
                            class="font-medium"
                            x-text="product.sku">

                        </span>

                    </div>


                    {{-- Terakhir Diperbarui --}}
                    <div
                        class="flex justify-between">

                        <span
                            class="text-slate-500">

                            Terakhir Diperbarui

                        </span>

                        <span
                            class="font-medium"
                            x-text="product.updated">

                        </span>

                    </div>

                </div>

            </div>

        </div>

        {{-- FOOTER --}}
        <div
            class="border-t border-slate-200 bg-white px-6 py-5">

            <div
                class="flex gap-3">

                <button

                    @click="open=false"

                    class="flex-1 rounded-xl border border-slate-300 py-3 font-medium text-slate-700 transition hover:bg-slate-100">

                    Tutup

                </button>

                <button

                    @click="

                        open = false;

                        setTimeout(() => {

                            window.dispatchEvent(
                                new CustomEvent('open-edit-product',{
                                    detail: product
                                })
                            );

                        }, 250);

                    "

                    class="flex-1 rounded-xl bg-[#AE7C18] py-3 font-semibold text-white transition hover:bg-[#96690F]">

                    Edit Produk

                </button>

            </div>

        </div>

    </div>

</div>