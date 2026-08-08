@extends('admin.layouts.app')

@section('title', 'Sizes')
@section('page-title', 'Sizes')

@section('content')

@php
$sizes = [
    [
        'size' => 'XS',
        'description' => 'Extra Small',
    ],
    [
        'size' => 'S',
        'description' => 'Small',
    ],
    [
        'size' => 'M',
        'description' => 'Medium',
    ],
    [
        'size' => 'L',
        'description' => 'Large',
    ],
];
@endphp

<div x-data="{
        open: false,
        mode: 'create',
        editingIndex: null,
        form: {
            size: '',
            description: ''
        },
        resetForm() {
            this.form = {
                size: '',
                description: ''
            };
            this.mode = 'create';
            this.editingIndex = null;
        },
        editSize(size, index) {
            this.mode = 'edit';
            this.editingIndex = index;
            this.form = {
                size: size.size,
                description: size.description
            };
            this.open = true;
        },
    }" x-on:open-edit-size.window="editSize($event.detail.size, $event.detail.index)" class="space-y-6 sm:space-y-8">

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">

        {{-- ================= HEADER ================= --}}
        <div class="flex flex-col gap-4 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-5">
            <div class="flex items-center gap-3 sm:gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 sm:text-xl">Standard Sizes</h2>
                    <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Manage global sizing metrics</p>
                </div>
            </div>

            <button @click="
                    if(open && mode==='create'){
                        open=false;
                        resetForm();
                    }else{
                        resetForm();
                        open=true;
                    }
                " class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] sm:px-5 sm:py-3 sm:text-base">
                <x-heroicon-o-plus class="h-5 w-5"/>
                <span x-text="open ? 'Close Form' : 'Add Size'"></span>
            </button>
        </div>

        {{-- ================= FORM DRAWER / COLLAPSE ================= --}}
        <div x-show="open" x-collapse class="border-b border-slate-200 bg-slate-50" style="display:none;">
            <div class="p-4 sm:p-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
                    <div class="grid gap-4 sm:gap-5 lg:grid-cols-2">
                        {{-- Size --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Size Name</label>
                            <input x-model="form.size" type="text" placeholder="XS" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:py-3 sm:text-base">
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700">Description</label>
                            <input x-model="form.description" type="text" placeholder="Extra Small" class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm transition focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/10 sm:py-3 sm:text-base">
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col-reverse gap-2.5 sm:mt-6 sm:flex-row sm:justify-end sm:gap-3">
                        <button @click="open=false; resetForm();" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100 sm:px-5 sm:py-3 sm:text-base">
                            Cancel
                        </button>

                        <button @click="
                                const currentMode = mode;
                                open = false;
                                resetForm();
                                setTimeout(() => {
                                    $dispatch('toast',{
                                        type: currentMode === 'create' ? 'success' : 'info',
                                        title: currentMode === 'create' ? 'Size Added' : 'Size Updated',
                                        message: currentMode === 'create' ? 'New size has been created successfully.' : 'Size updated successfully.'
                                    });
                                },300);
                            " class="rounded-xl bg-[#AE7C18] px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition hover:bg-[#96690F] sm:px-6 sm:py-3 sm:text-base">
                            <span x-text="mode==='create' ? 'Save Size' : 'Update Size'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= TABLE ================= --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[500px]">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3.5 sm:px-6 sm:py-4">Size</th>
                        <th class="px-4 py-3.5 sm:px-6 sm:py-4">Description</th>
                        <th class="px-4 py-3.5 text-center sm:px-6 sm:py-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($sizes as $size)
                        <tr class="transition hover:bg-slate-50">
                            <td class="px-4 py-4 sm:px-6 sm:py-5">
                                <span class="font-semibold text-slate-900 text-sm sm:text-base">
                                    {{ $size['size'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4 sm:px-6 sm:py-5">
                                <span class="text-slate-600 text-sm sm:text-base">
                                    {{ $size['description'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center sm:px-6 sm:py-5">
                                <div x-data="{open:false}" class="relative inline-block">
                                    <button @click="open=!open" class="rounded-lg p-2 transition hover:bg-slate-100">
                                        <x-heroicon-o-ellipsis-horizontal class="h-5 w-5 text-slate-500"/>
                                    </button>

                                    <div x-show="open" @click.outside="open = false" x-transition class="absolute right-0 z-50 mt-2 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-2 shadow-xl" style="display:none;">
                                        {{-- Edit --}}
                                        <button @click="
                                                open = false;
                                                $dispatch('open-edit-size', {
                                                    size: @js($size),
                                                    index: {{ $loop->index }}
                                                });
                                            " class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-slate-700 transition hover:bg-slate-50">
                                            <x-heroicon-o-pencil-square class="h-4 w-4"/>
                                            Edit
                                        </button>

                                        {{-- Delete --}}
                                        <button @click="
                                                open = false;
                                                $dispatch('open-delete-size', @js($size));
                                            " class="flex w-full items-center gap-3 px-4 py-2 text-left text-sm text-red-600 transition hover:bg-red-50">
                                            <x-heroicon-o-trash class="h-4 w-4"/>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- ================= FOOTER ================= --}}
        <div class="flex flex-col items-center justify-between gap-2.5 border-t border-slate-200 bg-slate-50 px-4 py-3.5 text-center sm:flex-row sm:px-6 sm:py-4 sm:text-left">
            <p class="text-xs text-slate-500 sm:text-sm">
                These sizes are available globally for all products.
            </p>
            <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-600">
                {{ count($sizes) }} Sizes
            </span>
        </div>

    </div>
</div>

@endsection

@include('admin.sizes.partials.delete-size')