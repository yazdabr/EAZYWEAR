@extends('layouts.website')

@section('title', 'Cek Pesanan - Eazywear Indonesia')

@section('content')

<section class="min-h-[75vh] bg-slate-50/80 py-8 sm:py-16 flex items-center justify-center relative overflow-hidden">
    {{-- Aksen Dekorasi Latar Belakang (Desktop) --}}
    <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-[#AE7C18]/5 blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-[#AE7C18]/5 blur-3xl pointer-events-none"></div>

    <x-ui.container class="relative z-10 w-full">
        <div class="mx-auto max-w-md sm:max-w-lg">

            {{-- MAIN CARD --}}
            <div class="rounded-2xl sm:rounded-3xl border border-slate-200/80 bg-white p-5 sm:p-8 shadow-sm">

                {{-- HEADER SECTION --}}
                <div class="text-center">
                    <div class="mx-auto mb-3 sm:mb-4 flex h-12 w-12 sm:h-14 sm:w-14 items-center justify-center rounded-2xl bg-[#AE7C18]/10 text-[#AE7C18] shadow-inner">
                        <x-heroicon-o-magnifying-glass-circle class="h-6 w-6 sm:h-8 sm:w-8" />
                    </div>

                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">
                        Cek Pesanan Anda
                    </h1>

                    <p class="mt-1.5 text-xs sm:text-sm text-slate-500 max-w-xs mx-auto leading-relaxed">
                        Masukkan nomor invoice dan email yang Anda gunakan saat checkout.
                    </p>
                </div>

                {{-- ALERT ERROR --}}
                @if(session('error'))
                    <div class="mt-5 flex items-center gap-2.5 rounded-xl border border-red-200 bg-red-50/80 p-3.5 text-xs sm:text-sm text-red-700">
                        <x-heroicon-o-exclamation-triangle class="h-4 w-4 shrink-0 text-red-500" />
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                {{-- FORM --}}
                <form
                    method="POST"
                    action="{{ route('orders.tracking.search') }}"
                    class="mt-6 space-y-4 sm:space-y-5"
                >
                    @csrf

                    {{-- FIELD INVOICE --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                            Nomor Invoice
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <x-heroicon-o-document-text class="h-4 w-4 sm:h-5 sm:w-5" />
                            </div>
                            <input
                                type="text"
                                name="invoice_number"
                                value="{{ old('invoice_number') }}"
                                placeholder="Contoh: INV-20260925-XXXXXX"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50/50 pl-10 pr-4 py-2.5 sm:py-3 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 transition-all uppercase"
                                required
                            >
                        </div>
                    </div>

                    {{-- FIELD EMAIL --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-semibold text-slate-700 mb-1.5">
                            Alamat Email
                        </label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <x-heroicon-o-envelope class="h-4 w-4 sm:h-5 sm:w-5" />
                            </div>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="nama@gmail.com"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50/50 pl-10 pr-4 py-2.5 sm:py-3 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:border-[#AE7C18] focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#AE7C18]/20 transition-all"
                                required
                            >
                        </div>
                    </div>

                    {{-- SUBMIT BUTTON --}}
                    <button
                        type="submit"
                        class="mt-2 w-full inline-flex items-center justify-center gap-2 rounded-xl bg-[#AE7C18] py-3 sm:py-3.5 text-xs sm:text-sm font-bold text-white shadow-md shadow-[#AE7C18]/20 hover:bg-[#8F6514] active:scale-[0.99] transition-all duration-200"
                    >
                        <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                        <span>Lacak Pesanan</span>
                    </button>

                </form>

            </div>

            {{-- HELP FOOTER --}}
            <p class="mt-6 text-center text-xs text-slate-400">
                Mengalami kendala?
                <a
                href="https://api.whatsapp.com/send/?phone=6285754431105&type=phone_number&app_absent=0"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-semibold text-[#AE7C18] hover:underline inline-flex items-center gap-1"
                >
                    Hubungi Kami
                </a>
            </p>

        </div>
    </x-ui.container>
</section>

@endsection
