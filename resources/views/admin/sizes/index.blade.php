@extends('admin.layouts.app')

@section('title', 'Ukuran')
@section('page-title', 'Ukuran')

@section('content')
<div
    x-data="sizeForm()"
    x-effect="document.body.classList.toggle('overflow-hidden', open)"
    @keydown.escape.window="closeDrawer()"
    x-on:open-create-size.window="openCreate()"
    x-on:open-edit-size.window="openEdit($event.detail)"
    class="space-y-5 sm:space-y-6 md:space-y-8"
>
    {{-- Header Section --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="shrink-0">
            <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl md:text-3xl">Ukuran</h1>
            <p class="mt-0.5 text-xs text-slate-500 sm:mt-1 sm:text-sm">Kelola ukuran yang tersedia untuk produk Anda.</p>
        </div>

        {{-- Tombol Tambah Desktop --}}
        <div class="hidden sm:flex sm:w-auto">
            <button
                type="button"
                @click="$dispatch('open-create-size')"
                class="inline-flex h-[46px] sm:h-[50px] w-full items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-[#AE7C18] px-5 text-sm font-semibold text-white shadow-lg shadow-[#AE7C18]/20 transition-all duration-300 hover:bg-[#96690F] focus:outline-none focus:ring-2 focus:ring-[#AE7C18] focus:ring-offset-2 active:scale-[0.98] sm:w-auto"
            >
                <x-heroicon-o-plus class="h-5 w-5"/>
                <span>Tambah Ukuran</span>
            </button>
        </div>
    </div>

    {{-- Main Container --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm sm:rounded-3xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-4 sm:px-6 sm:py-5">
            <div>
                <h2 class="text-base font-bold text-slate-900 sm:text-lg">Daftar Ukuran</h2>
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">Ukuran dapat digunakan produk.</p>
            </div>
            <div>
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 sm:py-1.5">
                    {{ $sizes->total() }} Ukuran
                </span>
            </div>
        </div>

        {{-- TAMPILAN DESKTOP: Tabel --}}
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full min-w-[650px] text-left">
                <thead class="border-b border-slate-200 bg-slate-50">
                    <tr class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Ukuran</th>
                        <th class="px-6 py-4 text-center">Produk</th>
                        <th class="px-6 py-4">Dibuat</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sizes as $size)
                        <tr data-size-id="{{ $size->id }}" class="transition duration-200 hover:bg-slate-50">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $size->name }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">ID #{{ $size->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="inline-flex rounded-lg bg-slate-100 px-3 py-1.5 text-sm font-semibold text-slate-700">
                                    {{ $size->product_variants_count }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <span class="text-sm text-slate-500">
                                    {{ $size->created_at?->format('d M Y') ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-5 text-center"
                                x-data="{ 
                                    open: false, 
                                    dropUp: false,
                                    toggleDropdown(event) {
                                        this.open = !this.open;
                                        if (this.open) {
                                            let rect = event.currentTarget.getBoundingClientRect();
                                            let windowHeight = window.innerHeight;
                                            this.dropUp = (windowHeight - rect.bottom) < 220;
                                        }
                                    }
                                }"
                                @resize.window="open = false"
                            >
                                <div class="relative inline-block text-left">
                                    <button
                                        type="button"
                                        @click="toggleDropdown($event)"
                                        title="Aksi"
                                        class="rounded-lg p-2 transition-all duration-200 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20"
                                        :class="open ? 'bg-slate-100' : ''"
                                    >
                                        <x-heroicon-o-ellipsis-horizontal class="h-5 w-5 text-slate-500"/>
                                    </button>

                                    <div
                                        x-show="open"
                                        x-cloak
                                        @click.outside="open = false"
                                        x-transition:enter="transition ease-out duration-150"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-100"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        :class="dropUp ? 'bottom-full mb-2 origin-bottom-right' : 'top-full mt-2 origin-top-right'"
                                        class="absolute right-0 z-[999] w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1.5 shadow-2xl shadow-slate-900/20"
                                        style="display:none;"
                                    >
                                        <button
                                            type="button"
                                            @click="
                                                open = false;
                                                window.dispatchEvent(
                                                    new CustomEvent('open-edit-size', {
                                                        detail: { id: @js($size->id), name: @js($size->name) }
                                                    })
                                                );
                                            "
                                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                        >
                                            <x-heroicon-o-pencil-square class="h-4 w-4 shrink-0 text-slate-500"/>
                                            <span>Ubah</span>
                                        </button>

                                        <button
                                            type="button"
                                            @click="
                                                open = false;
                                                window.dispatchEvent(
                                                    new CustomEvent('open-delete-size', {
                                                        detail: { id: @js($size->id), name: @js($size->name) }
                                                    })
                                                );
                                            "
                                            class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50"
                                        >
                                            <x-heroicon-o-trash class="h-4 w-4 shrink-0"/>
                                            <span>Hapus</span>
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center">
                                    <x-heroicon-o-arrows-up-down class="h-10 w-10 text-slate-300"/>
                                    <p class="mt-3 font-medium text-slate-600">Belum ada ukuran</p>
                                    <p class="mt-1 text-sm text-slate-400">Data ukuran akan muncul di sini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TAMPILAN MOBILE: Card List --}}
        <div class="divide-y divide-slate-100 md:hidden">
            @forelse($sizes as $size)
                <div data-size-id="{{ $size->id }}" class="space-y-3 bg-white p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="truncate text-base font-bold text-slate-900">{{ $size->name }}</h3>
                            <p class="mt-0.5 text-xs text-slate-400">ID #{{ $size->id }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                            {{ $size->product_variants_count }} Produk
                        </span>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                        <div>
                            <span class="block text-[10px] font-semibold uppercase tracking-wide text-slate-400">Dibuat</span>
                            <span class="mt-0.5 block text-xs font-medium text-slate-600">
                                {{ $size->created_at?->format('d M Y') ?? '-' }}
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="
                                    window.dispatchEvent(
                                        new CustomEvent('open-edit-size', {
                                            detail: { id: @js($size->id), name: @js($size->name) }
                                        })
                                    );
                                "
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-50 active:bg-slate-100"
                            >
                                <x-heroicon-o-pencil-square class="h-3.5 w-3.5 text-slate-500"/>
                                <span>Ubah</span>
                            </button>

                            <button
                                type="button"
                                @click="
                                    window.dispatchEvent(
                                        new CustomEvent('open-delete-size', {
                                            detail: { id: @js($size->id), name: @js($size->name) }
                                        })
                                    );
                                "
                                class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-red-50/50 px-3 py-2 text-xs font-medium text-red-600 transition hover:bg-red-100/50 active:bg-red-100"
                            >
                                <x-heroicon-o-trash class="h-3.5 w-3.5"/>
                                <span>Hapus</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center">
                    <x-heroicon-o-arrows-up-down class="mx-auto h-9 w-9 text-slate-300"/>
                    <p class="mt-3 text-sm font-medium text-slate-600">Belum ada ukuran</p>
                    <p class="mt-1 text-xs text-slate-400">Data ukuran akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="border-t border-slate-200 bg-slate-50 px-4 py-4 sm:px-6">
            <div class="flex flex-col items-center justify-between gap-3 sm:flex-row">
                <p class="text-xs text-slate-500 sm:text-sm">
                    Menampilkan
                    <span class="font-semibold text-slate-800">{{ $sizes->firstItem() ?? 0 }}</span>
                    sampai
                    <span class="font-semibold text-slate-800">{{ $sizes->lastItem() ?? 0 }}</span>
                    dari
                    <span class="font-semibold text-slate-800">{{ $sizes->total() }}</span>
                    ukuran
                </p>
                <x-admin.pagination :paginator="$sizes" />
            </div>
        </div>
    </div>

    {{-- FLOATING ACTION BUTTON (FAB) MOBILE --}}
    <div class="fixed bottom-6 right-5 z-40 sm:hidden">
        {{-- FLOATING ACTION BUTTON (FAB) KHUSUS MOBILE --}}
        <button
            type="button"
            @click="$dispatch('open-create-size')"
            class="fixed bottom-5 right-5 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-[#AE7C18] text-white shadow-xl shadow-[#AE7C18]/40 transition hover:bg-[#96690F] active:scale-95 lg:hidden"
            aria-label="Tambah Ukuran"
        >
            <x-heroicon-o-plus class="h-6 w-6"/>
        </button>
    </div>
</div>

@include('admin.sizes.partials.create-size')
@include('admin.sizes.partials.delete-size')
@endsection

@push('scripts')
<script>
function sizeForm() {
    return {
        open: false,
        loading: false,
        errors: {},
        mode: 'create',

        form: { id: '', name: '' },

        resetForm() {
            this.form = { id: '', name: '' };
            this.errors = {};
            this.loading = false;
        },

        openCreate() {
            this.mode = 'create';
            this.resetForm();
            this.open = true;
        },

        openEdit(size) {
            this.mode = 'edit';
            this.errors = {};
            this.form = {
                id: size?.id ?? '',
                name: size?.name ?? ''
            };
            this.open = true;
        },

        validateForm() {
            this.errors = {};

            if (!this.form.name?.trim()) {
                this.errors.name = 'Nama ukuran wajib diisi.';
            }

            if (Object.keys(this.errors).length > 0) {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        title: 'Data Belum Lengkap',
                        message: 'Mohon isi nama ukuran terlebih dahulu.'
                    }
                }));
                return false;
            }

            return true;
        },

        async submitForm() {
            if (this.loading || !this.validateForm()) return;

            this.loading = true;
            this.errors = {};

            const url = this.mode === 'create'
                ? '{{ route('admin.sizes.store') }}'
                : '{{ url('/admin/sizes') }}/' + this.form.id;

            const formData = new FormData();
            formData.append('name', this.form.name.trim());

            if (this.mode === 'edit') {
                formData.append('_method', 'PUT');
            }

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const contentType = response.headers.get('content-type') || '';
                const responseText = await response.text();

                let data = {};

                if (contentType.includes('application/json')) {
                    try {
                        data = JSON.parse(responseText);
                    } catch (error) {
                        throw new Error('Response server tidak dapat diproses.');
                    }
                }

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = Object.keys(data.errors).reduce((errors, key) => {
                            errors[key] = Array.isArray(data.errors[key]) ? data.errors[key][0] : data.errors[key];
                            return errors;
                        }, {});

                        const firstError = Object.values(this.errors)[0];
                        throw new Error(firstError || 'Data ukuran tidak valid.');
                    }

                    if (response.status === 419) throw new Error('Sesi halaman telah berakhir. Silakan refresh halaman.');
                    if (response.status === 404) throw new Error('Endpoint ukuran tidak ditemukan.');
                    if (response.status >= 500) throw new Error('Terjadi kesalahan pada server. Silakan coba lagi.');

                    throw new Error(data.message || 'Gagal menyimpan ukuran.');
                }

                if (!contentType.includes('application/json')) {
                    throw new Error('Server mengembalikan response yang tidak sesuai.');
                }

                this.open = false;

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        title: this.mode === 'create' ? 'Ukuran Ditambahkan' : 'Ukuran Diperbarui',
                        message: data.message || (this.mode === 'create' ? 'Ukuran berhasil ditambahkan.' : 'Ukuran berhasil diperbarui.')
                    }
                }));

                window.dispatchEvent(new CustomEvent('size-saved'));

                setTimeout(() => {
                    window.location.reload();
                }, 800);

            } catch (error) {
                console.error('Size Save Error:', error);
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        title: 'Gagal Menyimpan',
                        message: error.message || 'Terjadi kesalahan saat menyimpan ukuran.'
                    }
                }));
            } finally {
                this.loading = false;
            }
        },

        closeDrawer() {
            if (this.loading) return;
            this.open = false;
        }
    };
}
</script>
@endpush