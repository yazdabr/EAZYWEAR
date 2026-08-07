<div
    x-data="{

        open:false,

        mode:'create',

        form:{

            name:'',

            slug:'',

            description:'',

            status:'Active'

        },

        resetForm(){

            this.form={

                name:'',

                slug:'',

                description:'',

                status:'Active'

            };

        },

        openCreate(){

            this.mode='create';

            this.resetForm();

            this.open=true;

        },

        openEdit(category){

            this.mode='edit';

            this.form={

                ...category

            };

            this.open=true;

        },

    }"

    x-effect="document.body.classList.toggle('overflow-hidden', open)"

    @keydown.escape.window="open=false"

    x-on:open-create-category.window="openCreate()"
    
    x-on:open-edit-category.window="openEdit($event.detail)">

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

                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#AE7C18]/10">

                    <x-heroicon-o-squares-2x2 class="h-5 w-5 text-[#AE7C18]" />

                </div>

                <div>

                    <h2 class="text-xl font-bold text-slate-900">

                        <span x-text="mode==='create' ? 'Add Category' : 'Edit Category'"></span>

                    </h2>

                    <p class="mt-1 text-sm text-slate-500">

                        Organize your product categories.

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
        <div class="flex-1 space-y-6 overflow-y-auto bg-slate-100 p-6">

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <div class="mb-6 flex items-start gap-4">

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#AE7C18]/10">

                        <x-heroicon-o-folder class="h-5 w-5 text-[#AE7C18]" />

                    </div>

                    <div>

                        <h3 class="font-semibold text-slate-900">

                            General Information

                        </h3>

                        <p class="mt-1 text-sm text-slate-500">

                            Basic information about this category.

                        </p>

                    </div>

                </div>

                {{-- Name --}}
                <div>

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Category Name

                    </label>

                    <x-admin.input
                        x-model="form.name"
                        placeholder="e.g Football Jersey"/>

                </div>

                {{-- Slug --}}
                <div class="mt-6">

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Slug

                    </label>

                    <x-admin.input
                        x-model="form.slug"
                        placeholder="football-jersey"/>

                </div>

                {{-- Description --}}
                <div class="mt-6">

                    <label class="mb-2 block text-sm font-medium text-slate-700">

                        Description

                    </label>

                    <x-admin.textarea
                        rows="4"
                        x-model="form.description"
                        placeholder="Category description..."/>

                </div>

                {{-- Status --}}
                <div class="mt-6">

                    <label class="mb-3 block text-sm font-medium text-slate-700">

                        Status

                    </label>

                    <div class="flex gap-3">

                        <button

                            @click="form.status='Active'"

                            type="button"

                            class="flex-1 rounded-xl border px-4 py-3 font-medium transition-all duration-200"

                            :class="form.status==='Active'
                                ? 'border-emerald-600 bg-emerald-600 text-white shadow-lg shadow-emerald-500/20'
                                : 'border-slate-300 bg-white text-slate-700 hover:border-emerald-300 hover:bg-emerald-50'">

                            Active

                        </button>

                        <button

                            @click="form.status='Inactive'"

                            type="button"

                            class="flex-1 rounded-xl border px-4 py-3 transition"

                            :class="form.status==='Inactive'
                                ? 'border-red-500 bg-red-500 text-white'
                                : 'border-slate-300 bg-white'">

                            Inactive

                        </button>

                    </div>

                </div>

            </div>

        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-between border-t border-slate-200 bg-white px-6 py-5">

            <button

                @click="open=false"

                class="rounded-xl border border-slate-300 px-5 py-3 font-medium text-slate-700 transition hover:bg-slate-100">

                Cancel

            </button>

            <button

                @click="

                    open=false;

                    setTimeout(()=>{

                        $dispatch('toast',{

                            type: mode==='create' ? 'success' : 'info',

                            title: mode==='create'
                                ? 'Category Created'
                                : 'Category Updated',

                            message: mode==='create'
                                ? 'Category created successfully.'
                                : 'Category updated successfully.'

                        });

                    },500);

                "

                class="rounded-xl bg-[#AE7C18] px-6 py-3 font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] hover:shadow-xl">

                <span
                    x-text="mode==='create'
                        ? 'Save Category'
                        : 'Update Category'">

                </span>

            </button>

        </div>

    </div>

</div>