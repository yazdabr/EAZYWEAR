    <div
        x-data="sizeForm()"
        x-effect="document.body.classList.toggle('overflow-hidden', open)"
        @keydown.escape.window="closeDrawer()"
        x-on:open-create-size.window="openCreate()"
        x-on:open-edit-size.window="openEdit($event.detail)"
    >
        {{-- OVERLAY --}}
        <div
            x-show="open"
            x-cloak
            x-transition.opacity
            @click="closeDrawer()"
            class="fixed inset-0 z-[190] bg-black/40 backdrop-blur-sm"
            style="display: none;"
        ></div>

        {{-- DRAWER --}}
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition transform duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition transform duration-300 ease-in-out"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="fixed right-0 top-0 z-[200] flex h-screen w-full max-w-[520px] flex-col bg-white shadow-2xl"
            style="display: none;"
        >
            {{-- HEADER --}}
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-7 sm:py-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#AE7C18]/10">
                        <x-heroicon-o-arrows-up-down class="h-5 w-5 text-[#AE7C18]" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900 sm:text-xl">
                            <span x-text="mode === 'create' ? 'Tambah Ukuran' : 'Ubah Ukuran'"></span>
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-500 sm:text-sm">
                            <span x-text="mode === 'create' ? 'Tambahkan ukuran baru ke sistem.' : 'Perbarui informasi ukuran.'"></span>
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="closeDrawer()"
                    :disabled="loading"
                    class="rounded-lg p-2 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <x-heroicon-o-x-mark class="h-6 w-6" />
                </button>
            </div>

            {{-- BODY --}}
            <div class="flex-1 overflow-y-auto bg-slate-100 p-4 sm:p-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    {{-- INFO --}}
                    <div class="mb-6 flex items-start gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#AE7C18]/10">
                            <x-heroicon-o-tag class="h-5 w-5 text-[#AE7C18]" />
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-slate-900">
                                Informasi Ukuran
                            </h3>
                            <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                Masukkan nama ukuran yang tersedia.
                            </p>
                        </div>
                    </div>

                    {{-- NAMA UKURAN --}}
                    <div>
                        <label
                            for="size-name"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Nama Ukuran
                        </label>

                        <input
                            id="size-name"
                            type="text"
                            x-model="form.name"
                            maxlength="20"
                            placeholder="Contoh: S, M, L, XL, XXL"
                            @keydown.enter.prevent="submitForm()"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 placeholder:text-slate-400 shadow-sm transition-all duration-200 focus:border-[#AE7C18] focus:outline-none focus:ring-4 focus:ring-[#AE7C18]/15"
                            :class="errors.name ? 'border-red-400 focus:border-red-500 focus:ring-red-500/10' : ''"
                            :disabled="loading"
                        >

                        <template x-if="errors.name">
                            <p
                                class="mt-2 text-xs font-medium text-red-600"
                                x-text="errors.name"
                            ></p>
                        </template>

                        <p class="mt-2 text-xs text-slate-400">
                            Maksimal 20 karakter.
                        </p>
                    </div>

                    {{-- PREVIEW --}}
                    <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                            Preview
                        </p>

                        <div class="mt-3 flex items-center gap-3">

                            <div>
                                <p
                                    class="font-semibold text-slate-800"
                                    x-text="form.name || 'Nama ukuran'"
                                ></p>
                                <p class="text-xs text-slate-400">
                                    Ukuran produk
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

{{-- FOOTER / ACTIONS --}}
<div class="sticky bottom-0 flex flex-col gap-3 border-t border-slate-200 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6 sm:py-4">
    <div class="hidden sm:block">
        <p class="text-sm font-semibold text-slate-800">Publikasikan?</p>
        <p class="text-xs text-slate-400">Simpan atau perbarui data ukuran Anda.</p>
    </div>
    <div class="flex items-center gap-2.5 sm:gap-3">
        <button 
            type="button" 
            @click="closeDrawer()" 
            :disabled="loading" 
            class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 active:bg-slate-100 disabled:opacity-50 sm:flex-none sm:px-5 sm:py-3 sm:text-sm"
        >
            Batal
        </button>
        <button 
            type="button" 
            @click="submitForm()" 
            :disabled="loading" 
            class="relative inline-flex h-[44px] flex-1 min-w-[140px] items-center justify-center rounded-xl bg-[#AE7C18] px-4 text-xs font-bold text-white shadow-md shadow-[#AE7C18]/20 transition-all duration-200 hover:bg-[#96690F] active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:h-[48px] sm:flex-none sm:px-6 sm:text-sm"
        >
            <span x-show="!loading" x-text="mode === 'create' ? 'Simpan Ukuran' : 'Perbarui Ukuran'"></span>
            <span x-show="loading" class="inline-flex items-center gap-2" style="display: none;">
                <svg class="h-4 w-4 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Proses...</span>
            </span>
        </button>
    </div>
</div>
        </div>
    </div>