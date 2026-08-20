<div
    x-data="sizeDelete()"
    x-on:open-delete-size.window="openDelete($event.detail)"
>
    {{-- Overlay --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition-opacity duration-300 ease-out"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-250 ease-in"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="close()"
        class="fixed inset-0 z-[200] bg-black/50 backdrop-blur-sm"
    ></div>

    {{-- Modal --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition duration-300 ease-out"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition duration-200 ease-in"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        class="fixed inset-0 z-[201] flex items-center justify-center p-4 sm:p-5"
    >
        <div
            @click.stop
            class="w-full max-w-sm sm:max-w-md rounded-2xl sm:rounded-3xl bg-white p-6 sm:p-8 shadow-2xl"
        >
            {{-- Icon --}}
            <div class="mx-auto flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-full bg-red-100">
                <x-heroicon-o-trash class="h-8 w-8 sm:h-10 sm:w-10 text-red-600"/>
            </div>

            {{-- Judul --}}
            <h2 class="mt-4 sm:mt-6 text-center text-xl sm:text-2xl font-bold text-slate-900">
                Hapus Ukuran?
            </h2>

            {{-- Deskripsi --}}
            <p class="mt-2 sm:mt-3 text-center text-sm sm:text-base text-slate-500">
                Apakah Anda yakin ingin menghapus ukuran<br class="hidden sm:block" />
                <span
                    class="font-semibold text-slate-800"
                    x-text="size.name"
                ></span>?
            </p>

            <p class="mt-1 sm:mt-2 text-center text-xs sm:text-sm text-red-500">
                Tindakan ini tidak dapat dibatalkan.
            </p>

            {{-- Tombol --}}
            <div class="mt-6 sm:mt-8 flex gap-2 sm:gap-3">
                <button
                    type="button"
                    @click="close()"
                    :disabled="loading"
                    class="flex-1 rounded-xl border border-slate-300 px-4 py-2 sm:px-5 sm:py-3 text-sm sm:text-base font-medium text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Batal
                </button>

                <button
                    type="button"
                    @click="deleteSize()"
                    :disabled="loading"
                    class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-red-600 px-4 py-2 sm:px-5 sm:py-3 text-sm sm:text-base font-semibold text-white shadow-lg shadow-red-500/20 transition-all duration-300 hover:bg-red-700 hover:shadow-xl hover:shadow-red-500/30 active:scale-95 disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <svg
                        x-show="loading"
                        x-cloak
                        class="h-4 w-4 animate-spin"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>

                    <span x-text="loading ? 'Menghapus...' : 'Hapus'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function sizeDelete() {
    return {
        open: false,
        loading: false,

        size: {
            id: null,
            name: ''
        },

        openDelete(data) {
            this.size = {
                id: data?.id ?? null,
                name: data?.name ?? ''
            };

            this.loading = false;
            this.open = true;
        },

        close() {
            if (this.loading) {
                return;
            }

            this.open = false;
        },

        async deleteSize() {
            if (!this.size.id || this.loading) {
                return;
            }

            this.loading = true;

            try {
                const response = await fetch(
                    '{{ url('/admin/sizes') }}/' + this.size.id,
                    {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document
                                .querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

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

                if (response.status === 422) {
                    this.open = false;

                    window.dispatchEvent(
                        new CustomEvent('toast', {
                            detail: {
                                type: 'error',
                                title: 'Gagal Menghapus',
                                message: data.message ||
                                    'Ukuran tidak dapat dihapus karena masih digunakan oleh produk.'
                            }
                        })
                    );

                    return;
                }

                if (!response.ok) {
                    if (response.status === 419) {
                        throw new Error(
                            'Sesi halaman telah berakhir. Silakan refresh halaman.'
                        );
                    }

                    if (response.status === 404) {
                        throw new Error(
                            'Ukuran yang ingin dihapus tidak ditemukan.'
                        );
                    }

                    throw new Error(
                        data.message || 'Ukuran gagal dihapus.'
                    );
                }

                if (!contentType.includes('application/json')) {
                    throw new Error(
                        'Server mengembalikan response yang tidak sesuai.'
                    );
                }

                this.open = false;

                window.dispatchEvent(
                    new CustomEvent('toast', {
                        detail: {
                            type: 'success',
                            title: 'Ukuran Dihapus',
                            message: data.message || 'Ukuran berhasil dihapus.'
                        }
                    })
                );

                window.dispatchEvent(
                    new CustomEvent('size-deleted', {
                        detail: {
                            id: this.size.id
                        }
                    })
                );

                this.size = {
                    id: null,
                    name: ''
                };

                setTimeout(() => {
                    window.location.reload();
                }, 600);

            } catch (error) {
                console.error('Size Delete Error:', error);

                window.dispatchEvent(
                    new CustomEvent('toast', {
                        detail: {
                            type: 'error',
                            title: 'Gagal Menghapus',
                            message: error.message ||
                                'Terjadi kesalahan saat menghapus ukuran.'
                        }
                    })
                );
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush