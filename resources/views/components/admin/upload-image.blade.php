@props([
    'name' => 'image',
])

<div
    x-data="{
        preview: null,
        fileName: '',
        error: '',

        init() {
            window.addEventListener('product-gallery-update', (event) => {
                const images = event.detail.images || [];

                if (images.length > 0) {
                    this.preview = images[0];
                    this.error = '';
                }
            });
        },

        handleFile(event) {
            const file = event.target.files[0];

            this.error = '';

            if (!file) {
                this.preview = null;
                this.fileName = '';
                return;
            }

            const maxSize = 10 * 1024 * 1024;

            if (file.size > maxSize) {
                this.error = 'Ukuran foto terlalu besar. Maksimal upload adalah 10 MB.';
                this.preview = null;
                this.fileName = '';
                event.target.value = '';

                window.dispatchEvent(
                    new CustomEvent('toast', {
                        detail: {
                            type: 'error',
                            title: 'Upload Gagal',
                            message: 'Ukuran foto terlalu besar. Maksimal upload adalah 10 MB.'
                        }
                    })
                );

                return;
            }

            this.fileName = file.name;

            const reader = new FileReader();

            reader.onload = (e) => {
                this.preview = e.target.result;
            };

            reader.onerror = () => {
                this.error = 'Gagal membaca file gambar.';
                this.preview = null;
                this.fileName = '';
                event.target.value = '';
            };

            reader.readAsDataURL(file);
        },

        removeImage() {
            this.preview = null;
            this.fileName = '';
            this.error = '';

            const input = this.$refs.fileInput;

            if (input) {
                input.value = '';
            }
        }
    }"
    class="w-full"
>
    {{-- Upload Area --}}
    <div
        x-show="!preview"
        class="relative flex min-h-[220px] cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center transition hover:border-[#AE7C18] hover:bg-[#AE7C18]/5"
    >
        <input
            x-ref="fileInput"
            type="file"
            name="{{ $name }}"
            accept="image/jpeg,image/png,image/webp"
            class="absolute inset-0 z-10 h-full w-full cursor-pointer opacity-0"
            @change="handleFile($event)"
        >

        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#AE7C18]/10">
            <x-heroicon-o-cloud-arrow-up class="h-7 w-7 text-[#AE7C18]" />
        </div>

        <p class="text-sm font-semibold text-slate-700">
            Klik untuk memilih gambar
        </p>

        <p class="mt-1 text-xs text-slate-400">
            PNG, JPG, atau WEBP
        </p>

        <p class="mt-1 text-xs text-slate-400">
            Maksimal 10 MB
        </p>
    </div>

    {{-- Error Upload --}}
    <template x-if="error">
        <div class="mt-3 flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
            <x-heroicon-o-exclamation-circle class="mt-0.5 h-5 w-5 shrink-0" />

            <span x-text="error"></span>
        </div>
    </template>

    {{-- Preview --}}
    <div
        x-show="preview"
        x-cloak
        class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-50"
    >
        <img
            :src="preview"
            alt="Preview Produk"
            class="h-64 w-full object-cover"
        >

        {{-- Overlay --}}
        <div class="absolute inset-x-0 bottom-0 flex items-center justify-between bg-black/60 px-4 py-3">
            <div class="min-w-0">
                <p
                    x-text="fileName || 'Gambar Produk'"
                    class="truncate text-sm font-medium text-white"
                ></p>

                <p class="text-xs text-white/70">
                    Gambar utama produk
                </p>
            </div>

            <button
                type="button"
                @click="removeImage()"
                class="ml-3 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/20 text-white transition hover:bg-red-500"
            >
                <x-heroicon-o-trash class="h-4 w-4" />
            </button>
        </div>
    </div>
</div>