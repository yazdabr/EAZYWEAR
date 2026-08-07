<div
    x-data="{

        open:false,

        category:{

            name:'',

            slug:'',

            description:'',

            products:'',

            status:'',

            created:''

        },

        openView(category){

            this.category = category;

            this.open = true;

        }

    }"

    x-on:open-view-category.window="openView($event.detail)"

    x-effect="document.body.classList.toggle('overflow-hidden', open)">

    {{-- Overlay --}}
    <div

        x-show="open"

        x-transition.opacity

        @click="open=false"

        class="fixed inset-0 z-[90] bg-black/40 backdrop-blur-sm"

        style="display:none;">

    </div>

    {{-- Drawer --}}
    <div

        x-show="open"

        x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"

        x-transition:enter-start="translate-x-full"

        x-transition:enter-end="translate-x-0"

        x-transition:leave="transition transform duration-300 ease-in-out"

        x-transition:leave-start="translate-x-0"

        x-transition:leave-end="translate-x-full"

        class="fixed right-0 top-0 z-[100] flex h-screen w-full max-w-[520px] flex-col bg-white shadow-2xl"

        style="display:none;">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-200 px-7 py-6">

            <div class="flex items-center gap-3">

                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100">

                    <x-heroicon-o-eye class="h-5 w-5 text-sky-600"/>

                </div>

                <div>

                    <h2 class="text-xl font-bold text-slate-900">

                        Category Details

                    </h2>

                    <p class="mt-1 text-sm text-slate-500">

                        View category information.

                    </p>

                </div>

            </div>

            <button

                @click="open=false"

                class="rounded-lg p-2 transition hover:bg-slate-100">

                <x-heroicon-o-x-mark class="h-6 w-6"/>

            </button>

        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto bg-slate-100 p-6">

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <div class="mb-8">

                    <span class="inline-flex rounded-full bg-[#AE7C18]/10 px-4 py-1 text-sm font-semibold text-[#AE7C18]">

                        Category

                    </span>

                    <h2
                        class="mt-4 text-2xl font-bold text-slate-900"

                        x-text="category.name">

                    </h2>

                    <p
                        class="mt-2 text-slate-500"

                        x-text="category.description">

                    </p>

                </div>

                <div class="space-y-5">

                    <div class="flex items-center justify-between">

                        <span class="text-slate-500">

                            Slug

                        </span>

                        <span
                            class="font-medium text-slate-900"

                            x-text="category.slug">

                        </span>

                    </div>

                    <div class="flex items-center justify-between">

                        <span class="text-slate-500">

                            Products

                        </span>

                        <span
                            class="font-semibold text-slate-900"

                            x-text="category.products">

                        </span>

                    </div>

                    <div class="flex items-center justify-between">

                        <span class="text-slate-500">

                            Status

                        </span>

                        <span

                            class="rounded-full px-3 py-1 text-sm font-semibold"

                            :class="category.status==='Active'
                                ? 'bg-emerald-100 text-emerald-700'
                                : 'bg-red-100 text-red-700'"

                            x-text="category.status">

                        </span>

                    </div>

                    <div class="flex items-center justify-between">

                        <span class="text-slate-500">

                            Created

                        </span>

                        <span
                            class="font-medium text-slate-900"

                            x-text="category.created">

                        </span>

                    </div>

                </div>

            </div>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between border-t border-slate-200 bg-white px-6 py-5">

            <button

                @click="open=false"

                class="rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 transition hover:bg-slate-100">

                Close

            </button>

            <button

                @click="

                    open=false;

                    setTimeout(()=>{

                        $dispatch('open-edit-category',category);

                    },250);

                "

                class="rounded-xl bg-[#AE7C18] px-6 py-3 font-semibold text-white transition hover:bg-[#96690F]">

                Edit Category

            </button>

        </div>

    </div>

</div>