@props([
    'category'
])

<tr class="transition duration-200 hover:bg-slate-50">

    {{-- Checkbox --}}
    <td class="px-6 py-5">

        <input
            type="checkbox"
            class="h-4 w-4 rounded border-slate-300 text-[#AE7C18] focus:ring-[#AE7C18]">

    </td>

    {{-- Category --}}
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

    {{-- Products --}}
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

    {{-- Created --}}
    <td class="px-6 py-5 text-center">

        <span class="text-sm text-slate-500">

            {{ $category['created'] }}

        </span>

    </td>

    {{-- Action --}}
    <td class="px-6 py-5 text-center">

        <div
            x-data="{open:false}"
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

                        open=false;

                        $dispatch('open-view-category',{

                            name:@js($category['name']),

                            slug:@js($category['slug']),

                            description:@js($category['description']),

                            products:@js($category['products']),

                            status:@js($category['status']),

                            created:@js($category['created'])

                        });

                    "

                    class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50">

                    <x-heroicon-o-eye
                        class="h-4 w-4"/>

                    View

                </button>

                <button

                    @click="

                        open=false;

                        $dispatch('open-edit-category',{

                            name:'{{ $category['name'] }}',

                            slug:'{{ $category['slug'] }}',

                            description:'{{ $category['description'] }}',

                            status:'{{ $category['status'] }}'

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

                        $dispatch('open-delete-category',{

                            id:@js($category['slug']),

                            name:@js($category['name'])

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